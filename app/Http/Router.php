<?php
namespace App\Http;

use \App\Controllers\GetControllerActions;

/**
 * this class is used to define route directive
 */
class Router{

  private $routes = Array();
  protected $path='/';
  protected $response;
  protected $request;
  protected $app;
  private $routeNotFound = null;
  private static $instance;

  public static function getInstance():Router
  {
    if(is_null(self::$instance))
    {
      self::$instance=new Router();
    }
    return self::$instance;
  }
  public function __construct()
  {
    
  }
  public function init($req,$res){
    $this->request=$req;
    $this->response=$res;
  }
  /**
    * Function used to add a new route
    * @param string $expression    Route string or expression
    * @param callable $action    Function to call when route with allowed method is found
    * @param string $name          name of the route
    * @param string|array $method  Either a string of allowed method or an array with string values
    *
    */
  public function add($expression, $action,$name,$method = 'get'){
      if($method=='all'){
          $method=['get','post','put','delete'];
      }
      else{
        $method=array($method);
      }
        $this->routes[$name]=Array(
          'expression' => $expression,
          'action' => $action,
          'method' => $method
        );
    
  }

  public function routeNotFound(){
    $this->response->redirect($this->getLink('error','error404'));
  }

  public function run($url){
    $route=$this->match(parse_url($url));
    if(!is_array($route)){
        $this->routeNotFound();
    }
    else{
        $path=$route['path'];
        $matches=$route['matches'];
        $action=$path['action'];
        $params=array(
          'params'=>$matches,
          'action'=>$action
        );
        $this->runController($params);
    }
  }
  public function match($parsed_url){

    if(isset($parsed_url['path'])){
        $this->path=$path = $parsed_url['path'];
    }else{
      $path =$this->path;
    }
    $method=$_SERVER['REQUEST_METHOD'];
    $route_match_found = false;
    $active_route=array();

    foreach($this->routes as $route){
      $route['expression'] = '^'.$route['expression'].'$';
      

      // Check path match
      if(preg_match('#'.$route['expression'].'#i',$path,$matches)){

        foreach ((array)$route['method'] as $allowedMethod) {
          // Check method match
          if(strtolower($method) == strtolower($allowedMethod)){
            
            array_shift($matches);
            
            
            $route_match_found = true;
            $active_route=$route;
            
            break;
          }
        }
        
        if($route_match_found) break;
      }
    }
    if(!$route_match_found){
        return false;
    }
    return array(
      'path'=>$active_route,
      'matches'=>$matches
    );

  }
  /**
   * function used to get the route by his name
   * @param String $name the name of a Route
   * @return String $path the link path
   */
  public function getLink($name,$var=null){

    $path=$this->routes[$name]['expression'];
    if($var!=null){
      $path=str_replace('(.*)',$var,$path);
    }
    return $path;

  }
  /**
   * get the name of app currently used
   * @return string name off app
   */
  public function getApp(){
    return $this->app;
  }
  /**
   * set the name of app currently used
   * @param string $app name off app
   */
  public function setApp($app){
    $this->app=$app;
  }
  public function getPath(){
    return $this->path;
  }
  /**
   * know if the url is active
   * @param string $url
   * @return $string 'active' if true and '' else
   */
  public function isActive($url){
    $string='';
    if($url==$this->getPath()){
      $string='active';
    }
    return $string;
  }
  public function runController($data){
      $app=$this->getApp();
      if(!is_string($data['action'])){
        $function = $data['action'];
        return call_user_func($function,$data['params']);
      }
      $action= GetControllerActions::get($data['action'],$app);
      $class=$action['class'];
      $method=$action['method'];
      $class->$method($this->request,$this->response,$data['params']);
  }
  
}