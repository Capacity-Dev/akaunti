<?php

namespace App\Models;

class GetModel{


    public static function get($name){

        $class_name='\\App\\Models\\'.ucfirst($name).'Model';

        return new $class_name();

    }
}