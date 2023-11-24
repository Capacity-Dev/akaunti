<?php
namespace App\Middlewares;


class Runner {

    protected $res;
    protected $req;

    public function __construct($req,$res)
    {
        $this->res=$res;
        $this->req=$req;
    }
    public function useThis($midleware){
        $midleware='\\App\\Middlewares\\'.$midleware;
        $class=new $midleware();
        $class->run($this->req,$this->res);
        return $this;
    }
}