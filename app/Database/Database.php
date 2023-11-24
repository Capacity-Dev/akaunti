<?php

namespace App\Database;
use \PDO;
/**
 * L'interface de connection a la bdd
 */
class Database 
{
    protected $db_name;
    protected $db_host;
    protected $db_user;
    protected $db_pass;
    protected $pdo;
    static $instance;

    public static function getInstance(Array $info=null)
    {
        if(empty(self::$instance)){
            self::$instance=new Database($info);
        }
        return self::$instance;
    }
    public function __construct($info)
    {
        $this->db_name=$info['database'];
        $this->db_user=$info['user'];
        $this->db_pass=$info['password'];
        $this->db_host=$info['host'];
    }
    /**
     * @return \PDO
     */
    public function getPDO()
    {
        if($this->pdo==null)
        {
            
            $pdo=new PDO('mysql:dbname=' . $this->db_name . ';charset=utf8mb4;host=' . $this->db_host, $this->db_user, $this->db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
            $this->pdo=$pdo;
        }
        return $this->pdo;
    }

    public function query($statement)
    {
        return $this->getPDO()->query($statement);
    }
    /**
     * make prepared request
     * @param string $statement the sql 
     */
    public function prepare($statement,$data,$associative = true)
    {
        $pdo=$this->getPDO();

        $request=$pdo->prepare($statement);
        if($associative){
            foreach($data as $key=>$value){
                $type=is_int($value)|is_float($value)?PDO::PARAM_INT:(is_string($value)?PDO::PARAM_STR:PDO::PARAM_BOOL);
                $request->bindValue(':'.$key,$value,$type);
            }
            $request->execute();
        }else{
            var_dump($data);
            $request->execute($data);
        }
        $resource=$request;
        return $resource;

    }
}
