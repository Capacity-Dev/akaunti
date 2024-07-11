<?php
namespace App\Http;
use \App\Http\Request;
use Firebase\JWT\JWT;
/**
 * The server Response
 */

class Response{

    protected $header=[];
    protected $content;
    /**
     * @var \App\Http\Request
     */
    protected $request;
    protected $csrfToken;
    public static $instance;

    public static function getInstance()
    {
       if(is_null(self::$instance)){
           self::$instance=new Response();
       }
        return self::$instance;
    }

    
    public function __construct()
    {
        $this->request=Request::getInstance();
        $this->setCsrfToken();
    }
    /**
     * here we put the header in the header variable
     * @param array|string $header the title of header
     * @return void
     */
    public function setHeader($header){
        if(is_string($header)) return $this->header=[$header];
        if(is_array($header)) return $this->header=$header;
        
    }
    /**
     * add the header
     * @param array|string $header
     */
    public function addHeader($header){
        if(is_string($header)) return array_push($this->header,$header);
        if(is_array($header)) return $this->header=array_merge($this->header,$header);
    }
    /**
     * method used to send the header to users
     * @return void
     */
    public function sendHeader(){

        if(!empty($this->header)){
            foreach ($this->header as $header) {
                header($header);
            }
        }
    }
    /**
     * set The response content
     * @param string $content
     */
    public function setContent($content){
        $this->content=$content;
    }
    /**
     * get The content of a page
     * @return String $this->content
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * Generating th JWT Token
     * @param array $payload
     * @return string The token
     */
    public function forgeToken($payload){
        $key = $this->request->serverParams("private_key");
        return JWT::encode($payload, $key, 'HS256');
    }
    /**
     * This is the Simple redirection
     * @param String $path the path or uri where to redirect
     * @return void
     */
    public function redirect($path,$intern=false){

        if($intern)header('location:'.$this->request->getDomain().$path);
        else header('location:'.$path);
        exit();

    }
    /**
     * Create the user Cookie
     */
    public function setCookie($name,$value='',$expire=0,$path=null,$domain=null,$secure=false,$httpOnly=true){
        setcookie($name,$value,$expire,$path,$domain,$secure,$httpOnly);
    }
    /**
     * generate the csrf token
     */
    public function generateCsrfToken(){
        $this->csrfToken=sha1(time().'lemon'.rand(17,80));
    }
    public function setCsrfToken(){
        if(is_null($this->csrfToken)) $this->generateCsrfToken();
        $this->setSession('token',$this->csrfToken);
    }
    public function getCsrfToken(){
        return $this->csrfToken;
    }
    /**
     * create or set a user session
     */
    public function setSession($key,$value){
        $_SESSION[$key]=$value;
    }
    /**
     * Delete or unset session
     * @param string $key The session key
     */
    public function unsetSession(string $key){
        unset($_SESSION[$key]);
    }
    /**
     * i return the complete response to the user
     */
    public function getResponse(){
        $this->sendHeader();
        echo $this->getContent();

    }
    /**
     * send file to user.
     * @param string $fileNmame 
     * @param string $type 
     */
    public function renderFile($fileName,$type,$download=true){
        $name = end(explode('/',$fileName));
        header("Content-disposition: attachment;filename=$name");
        readfile($fileName);
    }
    public function renderJSON(array $data){
        $this->addHeader('Content-Type:application/json');
        $this->setContent(json_encode($data));
    }
    public function render($view,$data=array()){
        $this->addHeader('Content-Type:text/html');
        extract($data, EXTR_PREFIX_SAME, "wddx");
        $file = dirname(dirname(__DIR__)) . "/views/$view"; 
        ob_start();
        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
        $this->setContent(ob_get_clean());
    }
}