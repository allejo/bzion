<?php

include("../bzion-load.php");

$error = false;
$message = "Your message was sent successfully";

try {

    $header = new Header();

    if (!isset($_SESSION['username'])) {
        throw new Exception("You need to be logged in to do this.");
    }

    if (!isset($_POST['to']) || !isset($_POST['content'])) {
        throw new Exception("Bad request");
    }

    $group_to = new Group($_POST['to']);
    $content  = $_POST['content'];
    $bzid     = $_SESSION['bzid'];

    if (!$group_to->isValid()) {
        throw new Exception("The group you specified does not exist.");
    }

    if (!$group_to->isMember($bzid)) {
        throw new Exception("You aren't a member of that group.");
    }

    Message::sendMessage($group_to->getId(), $_SESSION['bzid'], $content);

} catch (Exception $e) {
    $error = true;
    $message = $e->getMessage();
}

$response = array();
$response['success'] = !$error;
$response['message'] = $message;

echo json_encode($response);