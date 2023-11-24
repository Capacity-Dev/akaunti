<?php
/**
 * define the root path
 */
define('ROOT_PATH',dirname(__DIR__));
/**
 * first i include the autoload and run it
 */
require ROOT_PATH.'/app/Autoload.php';
App\Autoload::loader();
require_once(ROOT_PATH.'/vendor/autoload.php');
header('Access-Control-Allow-Origin: http://localhost:3000');
/**
 * initialisation 
 */
include ROOT_PATH.'/global/init.php';

$app->sendResponse();
