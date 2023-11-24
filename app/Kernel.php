<?php

namespace App;
use \App\Database\Database;
use \App\Http\Request;
use \App\Http\Response;
use \App\Http\Router;
use \App\Middlewares\Runner;
use \Exception;

class Kernel{

    protected $response;
    protected $request;
    protected $database;
    protected $router;
    protected $config;

    public function __construct($config)
    {
        $this->config=$config;
        $this->init($config);
    }

    public function init(Array $config)
    {
        $this->request=new Request($config);
        $this->router=Router::getInstance();
        $this->response=Response::getInstance();
        $this->database=Database::getInstance($config['db_info']);
    }
    public function middlewares($req,$res){
        $mw=new Runner($req,$res);
        if($this->isApiRequest())$mw->useThis("ApiAuth");
        else $mw->useThis("WebAuth");
        
    }
    public function isApiRequest(){
        $url=$this->request->getUrl();
        $paths=explode('/',$url);
        return $paths[1]=='api'?true:false;
    }
    public function run(){

        //including routes
        include($this->config['routes_path']);
        
        $this->router->init($this->request,$this->response);
        if($this->isApiRequest())$router=ApiRoutes($this->router);
        else $router=WebRoutes($this->router);
        
        try{

            $this->middlewares($this->request,$this->response);
        }catch(\Exception $e){
            if($e->getCode()=='5050'){
                return $this->response;//stoping the kernel
            }
            else{
                throw $e;
            }
        }
        $router->run($this->request->getUrl());
        return $this->response;

    }

}