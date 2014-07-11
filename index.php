<?php
require_once __DIR__ . '/bzion-load.php';

use BZIon\Session\DatabaseSessionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

$request = Request::createFromGlobals();

$kernel = new AppKernel(AppKernel::guessEnvironment(), DEVELOPMENT > 0);
$kernel->boot();

if (ENABLE_WEBSOCKET) {
    // Ratchet doesn't support PHP's native session storage, so use our own
    // if we need it
    $storage = new NativeSessionStorage(array(), new DatabaseSessionHandler());
    $session = new Session($storage);
    Service::getContainer()->set('session', $session);
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
