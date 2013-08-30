<?php

include("../bzion-load.php");

$error = false;
$message = "Your message was sent successfully";

try {

    $header = new Header();

    if (!isset($_SESSION['username'])) {
        throw new Exception("You need to be logged in to do this.");
    }
    $bzid = $_SESSION['bzid'];

    // Two different POST variable layouts are acceptable:
    //
    // 1. ?content=foo&group_to=123 (To send a response to an already existing group)
    // 2. ?content=foo&to=123,456,789&subject=bar (To create a new message group)
    if (!isset($_POST['content'])) {
        throw new Exception("Bad request");
    }
    $content = $_POST['content'];

    if (trim($content) == "") {
        throw new Exception("You can't send an empty message!");
    }

    if (isset($_POST['group_to'])) {
        // Send a message to a group
        $group_to = new Group($_POST['group_to']);

        if (!$group_to->isValid()) {
            throw new Exception("The message group you specified does not exist.");
        }

        if (!$group_to->isMember($bzid)) {
            throw new Exception("You aren't a member of that message group.");
        }

        Message::sendMessage($group_to->getId(), $_SESSION['bzid'], $content);
    } elseif (!isset($_POST['to']) || !isset($_POST['subject'])) {
        throw new Exception("Bad request");
    } else {
        // Create a group and send a message to it
        $recipients = explode(',', $_POST['to']);

        if (count($recipients) < 1 || trim($_POST['to']) == ''){
            throw new Exception("You need to specify at least one recipient!");
        }

        // Add currently logged-in user to the list of recipients, if they are
        // not yet there
        $recipients[] = $_SESSION['bzid'];
        $group_to = Group::createGroup(htmlspecialchars($_POST['subject']), array_unique($recipients));

        Message::sendMessage($group_to->getId(), $_SESSION['bzid'], $content);
    }

} catch (Exception $e) {
    $error = true;
    $message = $e->getMessage();
}

$response = array();
$response['success'] = !$error;
$response['message'] = $message;
if (isset($group_to))
    $response['id'] = $group_to->getId();

echo json_encode($response);