<?php
require_once 'bzion-load.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

switch (DEVELOPMENT) {
case 1: $env = "dev"; break;
case 2: $env = "profile"; break;
default: $env = "prod"; break;
}

$kernel = new AppKernel($env, DEVELOPMENT > 0);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
