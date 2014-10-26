<?php

include '../bzion-load.php';

$error = false;
$message = "Your profile was successfully updated";

try {
    $request = Service::getRequest();
    $session = $request->getSession();
    $post    = $request->request;

    if (!$session->has("username")) {
        throw new Exception("You need to be logged in to do this.");
    }

    $me = new Player($session->get("playerId"));

    $me->setAvatar($post->get("avatar"));
    $me->setDescription($post->get("description"));
    $me->setTimezone($post->get("timezone"));

} catch (Exception $e) {
    $error = true;
    $message = $e->getMessage();
}

$response = array();
$response['success'] = !$error;
$response['message'] = $message;

echo json_encode($response);
