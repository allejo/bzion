<?php
require_once '../bzion-load.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$kernel = new AppKernel('prod', false);
$kernel->boot();

if ($kernel->getContainer()->getParameter('bzion.miscellaneous.development') === 'force') {
    // Create a new dev kernel
    $kernel = new AppKernel(AppKernel::guessDevEnvironment(), true);
    $kernel->boot();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
