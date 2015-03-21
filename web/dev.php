<?php
require_once '../bzion-load.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

try {
    $kernel = new AppKernel(AppKernel::guessDevEnvironment(), true);
    $kernel->boot();
} catch (ForbiddenDeveloperAccessException $e) {
    throw $e;
} catch (Exception $e) {
    // If something bad happened, show it unless we're sure we're in a production
    // environment
    if (!$kernel->getContainer()) {
        throw $e;
    } elseif ($kernel->getContainer()->getParameter('bzion.miscellaneous.development')) {
        throw $e;
    } else {
        die();
    }
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
