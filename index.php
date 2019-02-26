<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'Classes/Request.php';
require_once 'Classes/Router.php';
require_once 'Classes/Controller.php';
session_start();
$router = new Router(new Request);
$controller = new Controller();
$baseRoute = $controller->getAppFolder();
$router->get($baseRoute, [$controller, 'index']);
$router->post($baseRoute . '/auth', [$controller, 'newUser']);
$router->post($baseRoute . '/login', [$controller, 'checkUser']);
$router->get($baseRoute . '/json', [$controller, 'json']);
$router->post($baseRoute . '/data', [$controller, 'posted']);
$router->get($baseRoute . '/logout', [$controller, 'logout']);
$router->post($baseRoute . '/delete', [$controller, 'delete']);