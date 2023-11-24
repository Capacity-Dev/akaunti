<?php
namespace App;

class Autoload{

    static function loader(){

        spl_autoload_register(array(__CLASS__,'load'));
        

    }
    static function load($class){
        $pathTable=explode('\\',$class);
        $folder='/'.lcfirst($pathTable[0]).'/';
        array_shift($pathTable);
        $path=ROOT_PATH.$folder.join('/',$pathTable);
        require($path.'.php');
    }
}