<?php
namespace App\Libs;
use \App\Libs\Str;

class FormVerify{

    protected $inputs;
    protected $str;

    public function __construct()
    {
        $this->str=new Str();
    }
    public function init(Array $inputs)
    {
        

    }
    public function verify(Array $directives){

        $verified=false;
        foreach($directives as $key){
            if(isset($this->inputs[$key])){
                $function=$directives[$key];
                if(method_exists($this->str,$function)){
                    $verified=$this->str->$function();
                }
            }
        }
        return $verified;

    }




}