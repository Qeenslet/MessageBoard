<?php

require_once 'Classes/Request.php';
require_once 'Classes/Router.php';
require_once 'Classes/Controller.php';
$router = new Router(new Request);
$controller = new Controller();
$router->get('/medcom', [$controller, 'index']);
$router->get('/medcom/auth', [$controller, 'test']);
$router->post('/data', function($request) {
    return json_encode($request->getBody());
});