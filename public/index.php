<?php
require dirname (__FILE__) . '/../vendor/autoload.php';
require dirname (__FILE__) . '/../TombolaKernel.php';

$env = 'dev';
$debug = true;
if (isset($_SERVER['SYMFONY_ENV'])) {
    $env = $_SERVER['SYMFONY_ENV'];
    $debug = false;
}

$kernel = new TombolaKernel($env, $debug);
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
