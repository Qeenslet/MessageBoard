<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'Classes/Request.php';
require_once 'Classes/Router.php';
require_once 'Classes/Controller.php';
$router = new Router(new Request);
$controller = new Controller();
$baseRoute = "/medcom";
$router->get($baseRoute, [$controller, 'index']);
$router->get($baseRoute . '/auth', [$controller, 'test']);
$router->get($baseRoute . '/json', [$controller, 'json']);
$router->post($baseRoute . '/data', function($request) {
    return json_encode($request->getBody());
});