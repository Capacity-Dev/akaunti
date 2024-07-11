<?php
 use App\App;
 /**
  * initializing and runing lemon
  */
  $app=new App(ROOT_PATH.'/config/config.php');
  $app->run();
