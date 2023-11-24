<?php
namespace App\Libs;

class Str{

    public function isEmail($string):bool
    {
        return preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#",$string)?true:false;
    }
    public function isPhoneNumber($string):bool
    {
        return true;
    }
    /**
     * know if the string is empty
     * @param string $string the string to verify
     * 
     */
    public function isEmpty($string):bool
    {
        return empty($string)? true:false;
    }
    public function isValidUsername($string):bool
    {
        return !preg_match("/[\^<,\"@\/\{\}\'\(\)\*\$%\?=>:\|;#]+/i",$string)? true:false;
        
    }
    public function isValidName($string){
        return !preg_match("/[\^<,\"@\/\{\}\'\(\)\*\$%\?=>:\|;#]+/i",$string)?true:false;
    }
    public function isValidPassWord($string):bool
    {
        return preg_match("#^[a-zA-Z0-9._-]{4,}$#",$string)?true:false;

    }
    public function isNull($string):bool
    {
        return is_null($string)? true : false;
    }

}