<?php
require dirname (__FILE__) . '/../vendor/autoload.php';
require dirname (__FILE__) . '/../TombolaKernel.php';


$kernel = new TombolaKernel('dev', true); // @todo change
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
