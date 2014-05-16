<?php
require_once 'bzion-load.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$kernel = new AppKernel(AppKernel::guessEnvironment(), DEVELOPMENT > 0);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
