<?php

require_once("bzion-load.php");

use Symfony\Component\HttpFoundation\Request;


$request = Request::createFromGlobals();

$kernel = new AppKernel("prod", DEVELOPMENT);

$kernel->handle($request);
