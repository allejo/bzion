<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$header->draw("Messages");

$groups = Group::getGroups($_SESSION['bzid']);

?>


<div class="groups">

<!-- <div class="groups_toolbar">
    <div class="groups_toolbar_option"><a href="#">Active</a></div>
    <div class="groups_toolbar_option"><a href="#">Inactive</a></div>
    <div class="groups_toolbar_option"><a href="#">Other</a></div>
</div>
 -->
<table class="group_list">

    <tr><th class="groups_toolbar">
        <div class="groups_toolbar_option"><a href="#">Active</a></div>
        <div class="groups_toolbar_option"><a href="#">Inactive</a></div>
        <div class="groups_toolbar_option"><a href="#">Other</a></div>
    </th></tr>

<?php

foreach ($groups as $key => $id) {
    $group = new Group($id);

    date_default_timezone_set('America/New_York');
    echo "<tr><td><a class='group_link' data-id='" . $group->getId() . "' href='" . $group->getURL() . "'>";
    echo "<div class='group_subject'>" . $group->getSubject() . "</div>";
    echo "<div class='group_last_activity'>" . $group->getLastActivity() . "</div>";
    echo "<div style='clear:both'></div>";
    echo "<div class='content_one'>Some Content</div>";
    echo "<div class='content_two'>Some More Content</div>";
    echo "</a></td></tr>\n";
}

?>

</table> <!-- end .group_list -->

</div> <!-- end .groups -->

<div id="groupMessages" class="group_messages">

    <div class="compose_panel">
        <div class="group_message_toolbar"><span class="group_toolbar_text">Compose a new message</span></div>
        <form class="compose_form">
            <textarea id="composeArea" class="compose_area" placeholder="Enter your message here..."></textarea>
            <br />
            <button id="composeButton" onclick="sendResponse()" type="button" class="ladda-button" data-style="zoom-out">
                <span class="ladda-label">Submit</span>
            </button>
            <button type="reset">Reset</button>
            <button>Cancel editing</button>
        </form>
    </div>
<?php

if (isset($_GET['id'])) {
    $messages = Message::getMessages($_GET['id']);
    ?>

    <table class="group_message">

        <tr><th class="group_message_toolbar">
            <div class="group_message_option"><a href="#">Compose</a></div>
            <div class="group_message_option"><a href="#">Delete</a></div>
            <div class="group_message_option"><a onclick="showComposeModal('composeModal',<?php echo $_GET['id']; ?>)" href="#">Respond</a></div>
            <div class="group_message_option_disabled">Forward</div>
        </th></tr>


        <div style="clear: both"></div>

        <?php
        foreach($messages as $id) {
            echo "<tr><td class='group_message_content'><pre>";
            $msg = new Message($id);
            var_dump($msg);
            echo "</pre></td></tr>";
        }
        ?>

    </table> <!-- end .group_message -->


<?php
}
?>

</div> <!-- end .group_messages -->

<?php
$footer = new Footer();
$footer->draw();

?>
