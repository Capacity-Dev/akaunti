<?php
namespace App\Controllers\Web;
use \App\Controllers\Controller;
class ErrorController extends Controller{

    protected $canLoadModel=false;
    /**
     * function used to call methods for all errors
     * @param String $error The Error name
     * @return function
     */
    public function getError($req,$res,$error){
        $error=$error[0];
        if(method_exists($this,$error)){
            return $this->$error($req,$res);
        }
        else{
            return $res->redirect('/error/error404');
        }
    }

    public function error404($req,$res){
        $res->error404('web');

    }

}