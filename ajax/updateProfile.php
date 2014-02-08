<?php

include("../bzion-load.php");

$error = false;
$message = "Your profile was successfully updated";

try {

    $header = new Header();

    if (!isset($_SESSION['username'])) {
        throw new Exception("You need to be logged in to do this.");
    }

    $me = new Player($_SESSION['playerId']);

    $me->setAvatar($_POST['avatar']);
    $me->setDescription($_POST['description']);
    $me->setTimezone($_POST['timezone']);

} catch (Exception $e) {
    $error = true;
    $message = $e->getMessage();
}

$response = array();
$response['success'] = !$error;
$response['message'] = $message;

echo json_encode($response);