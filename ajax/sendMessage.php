<?php

include("../bzion-load.php");

$error = false;
$message = "Your message was sent successfully";

try {

    $header = new Header();

    if (!isset($_SESSION['username'])) {
        throw new Exception("You need to be logged in to do this.");
    }
    $playerId = $_SESSION['playerId'];

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

        if (!$group_to->isMember($playerId)) {
            throw new Exception("You aren't a member of that message group.");
        }

        Message::sendMessage($group_to->getId(), $playerId, $content);
    } elseif (!isset($_POST['to']) || !isset($_POST['subject'])) {
        throw new Exception("Bad request");
    } else {
        // Create a group and send a message to it

        $subject = htmlspecialchars($_POST['subject']);
        $recipients = explode(',', $_POST['to']);

        if (trim($subject) == '')
            throw new Exception("You need to specify a subject for your message.");

        if (count($recipients) < 1 || trim($_POST['to']) == '') {
            if (DEVELOPMENT)
                $recipients = array();
            else
                throw new Exception("You need to specify at least one recipient.");
        }

        foreach ($recipients as $rid) {
            if (!DEVELOPMENT && $rid == $playerId && count($recipients) < 2)
                throw new Exception("You can't send a message to yourself!");

            $recipient = new Player($rid);

            if (!$recipient->isValid()) {
                throw new Exception("One of the recipients you specified does not exist.");
            }
        }

        // Add the currently logged-in user to the list of recipients, if he isn't there already
        $recipients[] = $playerId;
        $group_to = Group::createGroup($subject, array_unique($recipients));

        Message::sendMessage($group_to->getId(), $playerId, $content);
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