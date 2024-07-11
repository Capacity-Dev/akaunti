<?php
namespace App\Middlewares;
use \App\Web\Controllers\UsersController;

/**
 * All about user authentification
 */
class WebAuth extends Middleware{

    protected $autorized=array(
        '/login',
    );

    public function __construct()
    {
        
    }
    /**
     * login function
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function run($req,$res){
        
        
        /* 
        $url=$req->getUrl();
        if(!in_array($url,$this->autorized) && !$req->session('username')){
            $res->redirect("/login",true);
        }else if($req->session('privillege') == "user" && (preg_match('#/dashboard#',$url) || preg_match('#\/dashboard\/(.+)#',$url))){
            $res->redirect("/login?error=not-admin",true);
        } */
        
        
    }
}
