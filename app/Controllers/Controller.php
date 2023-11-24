<?php
namespace App\Controllers;
use \App\Database\Database;
use \App\Models\GetModel;
use \App\Libs\Str;
class Controller{

    protected $db;
    protected $canLoadModel=true;
    
    protected $model;
    protected $str;
    protected $dataFolder;

    public function __construct(){
        if($this->canLoadModel){

            $this->model=$this->getModel();
        }
        $this->db=Database::getInstance();
        $this->str=new Str();
        $this->setDataFolder();
    }
    public function setDataFolder($path=null){
        if($path!==null){
            return $this->dataFolder=$path;
        }
        $class=explode('\\',get_class($this));
        $dataPath=end($class);
        $dataPath=strtolower(str_replace('Controller','',$dataPath));
        $dataPath="../app/Database/Tables/data/$dataPath/";
        $this->dataFolder=$dataPath;
    }
    protected function getModel(){

        $class=explode('\\',get_class($this));
        $class=end($class);
        $class=str_replace('Controller','',$class);
        $model=GetModel::get($class);
        return $model;

    }


}