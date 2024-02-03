<?php

namespace App\Database\Tables;


class Table{

    protected $data=array();
    protected $otherData=true;
    protected $dataPath;

    public function __construct()
    {
        $class=explode('\\',get_class($this));
        $dataPath=end($class);
        $dataPath=strtolower(str_replace('Table','',$dataPath));
        $dataPath=__DIR__."/data/$dataPath/";
        $this->dataPath=$dataPath;
    }
    public function __get($name)
    {
        return $this->getValue($name);
    }
    public function setData(array $data){
        if(empty($this->data)){
            $this->data=$data;
        }
        else{
            $this->data=array_merge($this->data,$data);
        }
        if($this->otherData){
            if(method_exists($this,'otherData')){
            $otherData='otherData';
            $this->$otherData();
            }
        }
        
    }
    public function getData(){
        return $this->data;
    }
    public function getValue($key){
        return isset($this->data[$key])?$this->data[$key]:'';
    }
    public function otherData(){
        $itemId=$this->data['id'];
        $filePath=$this->dataPath.$itemId.'.json';
        if(!is_file($filePath)){
            $file=fopen($filePath,'a+');
           fputs($file,json_encode(array('likes'=>[])));
        }
        $other=json_decode(file_get_contents($filePath),true);
        $this->data=array_merge($this->data,$other);
        $this->data['likesNumber']=count($this->data['likes']);
    } 
    public function __toString()
    {
        return json_encode($this->data);
    }
}