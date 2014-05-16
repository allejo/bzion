<?php
// umask(0000);
require_once("bzion-load.php");

use Symfony\Component\HttpFoundation\Request;


$request = Request::createFromGlobals();

$kernel = new AppKernel(DEVELOPMENT ? "dev" : "prod", DEVELOPMENT);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
