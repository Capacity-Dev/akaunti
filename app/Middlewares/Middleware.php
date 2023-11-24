<?php
namespace App\Middlewares;

class Middleware {
    
    /**
     * breaking the script with exception
     */
    public function breakScript(){
        throw new \Exception("Script stopped by The Middleware",'5050');//errorCode
    }
}