<?php
/**
 * define the root path
 */
define('ROOT_PATH',dirname(__DIR__));
require_once(ROOT_PATH.'/vendor/autoload.php');
header('Access-Control-Allow-Origin: http://localhost:5173');
/**
 * initialisation 
 */
include ROOT_PATH.'/global/init.php';

$app->sendResponse();
