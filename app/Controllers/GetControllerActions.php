<?php
namespace App\Controllers;

class GetControllerActions{

    protected $req;
    protected $res;

    public function __construct($req,$res)
    {
        $this->req=$req;
        $this->res=$res;
    }

    public static function get($action,$app){
        $tab=explode('@',$action);
        $class=$tab[0];
        $method=$tab[1];
        $class='\\App\\Controllers\\'.$app.'\\'.$class.'Controller';

        $module=new $class();

        return array(
            'class'=>$module,
            'method'=>$method
        );
    }
}