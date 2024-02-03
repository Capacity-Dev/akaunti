<?php

/**
 * here I can make routes directive for web pages
 * @param \App\Http\Router $router
 */
function WebRoutes($router){
   $router->setApp('Web');//tell to the router that i wanna use the web app

   
   $router->add('/(.+)','Default@front','front');
   /* $router->add('/','Default@cashDesk','front-home');
   $router->add('/logout','Default@logout','logout');
   $router->add('/login','Default@login','login');
   $router->add('/dashboard','Default@dashboard','dashboard-home');
   $router->add('/dashboard/(.+)','Default@dashboard','dashboard'); */
   

   return $router;
}

/**
 * here I can make routes directive for the Api
 * @param \App\Http\Router $router
 */
function ApiRoutes($router){
   $router->setApp('Api');//tell to the router that i wanna use the api app
   //products routes
   $router->add('/api/add-products','Products@addProducts','add-products','post');
   $router->add('/api/update-product','Products@updateProduct','update-product','post');
   $router->add('/api/get-products','Products@getProducts','get-products','get');
   $router->add('/api/get','Products@getProducts','get-product','get');
   $router->add('/api/delete-product','Products@deleteProducts','delete-product','get');
   $router->add('/api/search-products','Products@searchProducts','search-products','get');
   $router->add('/api/add-receipt','Products@addReceipt','add-receipt','post');
   $router->add('/api/verify-receipt','Products@verifyReceipt','verify-receipt','post');
   $router->add('/api/last-receipt','Products@lastReceipt','last-receipt','get');
   $router->add('/api/most-selled','Products@mostSelled','most-selled','get');
   $router->add('/api/get-sells','Products@getSells','get-sells','get');
   $router->add('/api/overview','Products@overview','overview','get');
   $router->add('/api/save-bill','Products@saveBill','save-bill','post');
   $router->add('/api/import','Products@import','import-csv','post');
   $router->add('/api/export','Products@export','export-csv','get');
   $router->add('/api/get-config','Products@getConfigs','get-config','get');
   $router->add('/api/save-config','Products@changeConfigs','save-config','post');

   //users routes
   $router->add('/api/login','Users@login','login','post');
   $router->add('/api/get-users','Users@getAll','get-users','get');
   $router->add('/api/get-user','Users@getOne','get-user','get');
   $router->add('/api/add-user','Users@create','add-user','post');
   $router->add('/api/update-user','Users@update','update-user','post');
   $router->add('/api/delete-user','Users@delete','delete-user','get');
   $router->add('/api/search-user','Users@search','search-user','get');

   
   $router->add('/api/(.*)',function($params){
      http_response_code(404);
      echo '{error:"endpoint : /'.$params[0].' doesn\'t exists"}';
   },'error');
   

   
   return $router;
}

 
