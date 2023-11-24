<?php
namespace App\Http;
/**
 * the client's request
 */
class Request{

    protected $url;
    protected $requestMethod;
    protected $userInfo=array();
    protected $env;
    protected static $instance;

    public static function getInstance($config=null)
    {
        if(is_null(self::$instance)){
            self::$instance=new Request($config=null);
        }
        return self::$instance;
    }

    public function __construct($config=null){
        $this->assign();
        $this->env=$config;
    }
    public function assign(){
        $this->requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->url=$_SERVER['REQUEST_URI'];

    }
    public function post($index=null){
        $jsonString = file_get_contents('php://input');
        $json = json_decode($jsonString,true);
        if(!is_null($json)){
            $postData = array_merge($_POST,$json);
        } else $postData = $_POST;

        if($index===null)
        {
            return $postData;
        }
        return isset($postData[$index])?$postData[$index]:null;
    }
    public function get($index=null){

        if($index===null)
        {
            return $_GET;
        }
        return isset($_GET[$index])?$_GET[$index]:null;
    }
    public function session($index=null){
        if($index===null)
        {
            return $_SESSION;
        }
        return isset($_SESSION[$index])?$_SESSION[$index]:null;
    }
    public function files($index=null){

        if($index===null)
        {
            return $_FILES;
        }
        return isset($_FILES[$index])?$_FILES[$index]:null;
    }
    public function coockie($index=null){

        if($index===null)
        {
            return $_COOKIE;
        }
        return isset($_COOKIE[$index])?$_COOKIE[$index]:null;
    }
    public function rootPath(){
        return !is_null($this->env)?$this->env['rootPath']:'../';
    }
    public function getProtocol()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        return $protocol;
    }
    public function getDomain()
    {
        $protocol = $this->getProtocol();
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host;
    }
    public function getUrl(){
        return $this->url;
    }
    public function getMethod(){
        return $this->requestMethod;
    }
    /**
     * get the server environement property
     */
    public function serverParams($index){
        if(is_null($index)){
            return $this->env;
        }
        return $this->env[$index];
    }
    /**
     * add other informations about client
     */
    public function addCInfo(array $info){
        $this->userInfo=array_merge($this->userInfo,$info);
    }
    /**
     * get client's informations
     */
    public function getCInfo($index=null){
        if($index===null)
        {
            return $this->userInfo;
        }
        return isset($this->userInfo[$index])?$this->userInfo[$index]:null;
    }

}