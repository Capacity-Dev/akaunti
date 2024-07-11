<?php

namespace App\Http;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * the client's request
 */
class Request
{

    protected $url;
    protected $requestMethod;
    protected $userInfo = array();
    protected $env;
    /**
     * @var  Request
     */
    protected static $instance = null;

    public static function getInstance($config = null)
    {
        if (is_null(self::$instance)) {
            if (is_null($config)) throw new \Exception("\$config can't be null", 1);
            self::$instance = new Request($config);
        }
        return self::$instance;
    }

    public function __construct($config = null)
    {
        $this->env = $config;
        $this->assign();
    }
    public function assign()
    {
        $this->requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->url = $_SERVER['REQUEST_URI'];
        
        $token = $this->getBearerToken();
        if (!is_null($token)) {
            try {
                $decoded =(array) JWT::decode($token, new Key($this->env["private_key"], 'HS256'));
            } catch (\Throwable $th) {
                return;
            }
            $this->addCInfo($decoded);
        }
    }
    public function post($index = null)
    {
        $jsonString = file_get_contents('php://input');
        $json = json_decode($jsonString, true);
        if (!is_null($json)) {
            $postData = array_merge($_POST, $json);
        } else $postData = $_POST;

        if ($index === null) {
            return $postData;
        }
        return isset($postData[$index]) ? $postData[$index] : null;
    }
    public function get($index = null)
    {

        if ($index === null) {
            return $_GET;
        }
        return isset($_GET[$index]) ? $_GET[$index] : null;
    }
    public function session($index = null)
    {
        if ($index === null) {
            return $_SESSION;
        }
        return isset($_SESSION[$index]) ? $_SESSION[$index] : null;
    }
    public function files($index = null)
    {

        if ($index === null) {
            return $_FILES;
        }
        return isset($_FILES[$index]) ? $_FILES[$index] : null;
    }
    public function coockie($index = null)
    {

        if ($index === null) {
            return $_COOKIE;
        }
        return isset($_COOKIE[$index]) ? $_COOKIE[$index] : null;
    }
    public function rootPath()
    {
        return !is_null($this->env) ? $this->env['rootPath'] : '../';
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
    public function getPath() {
        $parsed = parse_url($this->url);
        return $parsed['path'];
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function getMethod()
    {
        return $this->requestMethod;
    }
    /**
     * get the server environement property
     */
    public function serverParams($index = null)
    {
        if (is_null($index)) {
            return $this->env;
        }
        return $this->env[$index];
    }
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Pour Nginx ou fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Nous devons gérer le cas de différentes capitalisations du header
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // Extraction du token du header
        if (!is_null($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    /**
     * add other informations about client
     */
    public function addCInfo(array $info)
    {
        $this->userInfo = array_merge($this->userInfo, $info);
    }
    /**
     * get client's informations
     */
    public function getCInfo($index = null)
    {
        if ($index === null) {
            return $this->userInfo;
        }
        return isset($this->userInfo[$index]) ? $this->userInfo[$index] : null;
    }
}
