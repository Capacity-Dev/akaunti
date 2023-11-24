<?php
namespace App;
use \App\Kernel;

/**
 * This app accept only json configuration file
 */
class App{

    protected $params;
    protected $response;
    private $kernel;
    
    public function __construct($configPath){
        $this->init($configPath);
    }
    protected function init($configPath){
        
        session_start();
        $this->params=json_decode(file_get_contents($configPath),true);
        $this->kernel=new Kernel($this->params);

    }
    public function run(){
         try{
            $this->response=$this->kernel->run();
        } catch (\Throwable $e) {
            if($this->params['env']=='dev') throw $e;
            else if($this->params['env']=='prod') $this->errorLog($e);
        }
        

    }
    public function errorLog($exception){
        $errorMessage='\n\t\r\n\t\r'.date(DATE_RSS);
        $errorMessage.=':'.$exception->getMessage().'\n\t\r';
        $errorMessage.='on '.$exception->getFile().'line : '.$exception->getLine();

        fputs($this->params['logPath'].'errorLog.txt',$errorMessage);
    }
    public function sendResponse(){
        
        $this->response->getResponse();
    }
}