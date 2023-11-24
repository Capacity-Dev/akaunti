<?php
namespace App\Models;
use App\Database\Database;
use \App\Database\Tables\Table;
use \ArrayAccess;
use Exception;


class Model{
    /**
     * @var Database $db
     */
    protected $db;
    protected $table;
    protected $table_class_name;

    public function __construct(){
        $this->db=Database::getInstance();
        $class=explode('\\',get_class($this));
        $table=end($class);
        $table=str_replace('Model','',$table); //name of the table
        $table_class_name='\\App\\Database\\Tables\\'.$table.'Table';//name of class used by getTableInstances

        $this->table=lcfirst($table);
        $this->table_class_name=$table_class_name;
    }
    public function getAll($case=null,$table=null,$where=null){
        $table=$table?$table:$this->table;
        if($case==null){

            $data= $this->db->query('SELECT * FROM '.$table);
            return $data->fetchAll(\PDO::FETCH_ASSOC);
        }
        else{
            $cases=join(',',$case);
            $data= $this->db->query("SELECT $cases FROM $table");
            return $data->fetchAll();

        }

    }
    public function update($data,$table=null){
        is_null($table)?$table=$this->table:false;
        $where=isset($data['where'])?$data['where']:null;
        $case=$data['case'];
        if(is_array($case)){
            $string=$this->getKeyValues($case,'string');
            
            if(is_array($where)){
                $data=array_merge($case,$where);
                $whereString=$this->getKeyValues($where,'string');
            }
            $request=$this->prepare('UPDATE '.$table.' SET '.$string.(is_null($where)?'':' WHERE '.$whereString),$data);
        }
        return ($request);
        
    }
    public function delete($data,$table=null){
        is_null($table)?$table=$this->table:false;
        if(is_array($data))
        {
            $string=$this->getKeyValues($data,'string');
            
            $req=$this->prepare('DELETE FROM '.$table.' WHERE '.$string,$data);
            return $req;
        }
        else if(is_int($data)){
            $req=$this->prepare('DELETE FROM '.$table.' WHERE id=:id ',array('id'=>$data));
            return $req;
        }
    }
    public function insert($data,$table=null){
        is_null($table)?$table=$this->table:false;
        if(is_array($data))
        {
            $string=$this->getKeyValues($data);
            
            $req=$this->prepare('INSERT INTO '.$table.' ('.$string['params'].') VALUES('.$string['values'].')',$data);
            return $req;
        }
        else {
            throw new Exception('$data must be an array !!!!','0x101');
        }
    }
    public function getInsertedId(){
        return $this->query('SELECT LAST_INSERT_ID()');
    }
    /**
     * construct the string of key and one of values for the prepared sql request
     * @param string|null $type type of the returned value
     * @param string|null $separator used if close WHERE are used too
     * @return array|string array('params'=>string,'values'=>string):if $type==default -- string:if $type==='string'
     */
    public function getKeyValues(Array $data,$type='array',$separator=',')
    {
        
        if($type=='string'){
            $string='';
            foreach ($data as $key=>$value)
            {
                if(!$string)
                {
                    $string.=$key.'=:'.$key;
                }
                else{
                    $string.=' '.$separator.$key.'=:'.$key;
                }
            }
            return $string;
        }else{
            $values='';
            $params='';
            foreach ($data as $key=>$value)
            {
                if(!$params)
                {
                    $params=$params.$key;
                }
                else{
                    $params=$params.','.$key;
                }
                if(!$values){
                    $values=$values.':'.$key;
                }
                else{
                    $values=$values.',:'.$key;
                }
            }
            return array(
                'params'=>$params,
                'values'=>$values
            );
        }
        
    }
    /**
     * add data on the table instance
     * 
     */
    public function getTableInstances($data,$one=false){

        if($data){
            if($one){
                
                $instance=new $this->table_class_name();
                $instance->setData($data);
                return $instance;
            } 
            else{
                $instances=[];
                foreach ($data as $entry) {
                    $instance=new $this->table_class_name();
                    $instance->setData($entry);
                    array_push($instances,$instance);
                    
                }
                return $instances;
            }
        }
        else{
            if($one){
                
                $instance=new $this->table_class_name();
                return $instance;
            } 
            else{
                return array();
            }
        }

        
        


    }
    /**
     * make a SQL request to the database
     * @param string $statement
     * @return \PDOStatement|false
     */
    public function query(string $statement){

        return $this->db->query($statement);

    }
    /**
     * make a SQL request to the database
     * @param string $statement
     * @param array $data
     * @param bool $isAssociative
     * @return \PDOStatement|false
     */
    public function prepare(string $statement,array $data,$isAssociative=true){

        return $this->db->prepare($statement,$data,$isAssociative);

    }
    
    
    
}