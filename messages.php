<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$header->draw("Messages");

$messages = Message::getMessages($_SESSION['bzid']);

echo "<pre>";
print_r($messages);
echo "</pre>";

$footer = new Footer();
$footer->draw();

?>
