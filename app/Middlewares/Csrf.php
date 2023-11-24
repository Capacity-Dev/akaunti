<?php
namespace App\Middlewares;

class Csrf{

    public function run($req,$res){

        if($req->getMethod()=='post'){
            $token=$req->post('csrfToken');
            if($token!==$req->session('token')){
                //$res->goTo404Error();
            }
        }

    }

}