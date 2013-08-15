<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$header->draw("Messages");

$groups = Group::getGroups($_SESSION['bzid']);

?>

<script type="text/html" id="composeModal">
            <h3>Compose a new message</h3>
            <div class="nifty-inner">
                <form>
                    <p>This is a modal window. You can do the following things with it:</p>
                    <textarea id="composeArea" placeholder="Enter your message here..."></textarea>
                    <br />
                    <!--<button type="submit">Send Message</button>-->
                    <button id="composeButton" onclick="sendResponse()" type="button" class="ladda-button" data-style="zoom-out"><span class="ladda-label">Submit</span></button>
                    <button type="reset">Reset</button>
                    <button class="nifty-close">Cancel editing</button>
                </form>
            </div>
</script>

<div class="groups">

<div class="groups_toolbar">
    <div class="groups_toolbar_option"><a href="#">Active</a></div>
    <div class="groups_toolbar_option"><a href="#">Inactive</a></div>
    <div class="groups_toolbar_option"><a href="#">Other</a></div>
</div>

<table class="group_list">

<?php

foreach ($groups as $key => $id) {
    $group = new Group($id);

    date_default_timezone_set('America/New_York');
    echo "<tr><td><a href='" . $group->getURL() . "'>";
    echo "<div class='group_subject'>" . $group->getSubject() . "</div>";
    echo "<div class='group_last_activity'>" . $group->getLastActivity() . "</div>";
    echo "<div style='clear:both'>";
    echo "<div class='content_one'>Some Content</div>";
    echo "<div class='content_two'>Some More Content</div>";
    echo "</a></td></tr>";
}

?>

</table> <!-- end .group_list -->

</div> <!-- end .groups -->

<?php

if (isset($_GET['id'])) {
    $messages = Message::getMessages($_GET['id']);
    ?>
    <div id="group_messages">
        <div class="group_message">
            <div class="group_message_toolbar">
                <div class="group_message_option"><a href="#">Compose</a></div>
                <div class="group_message_option"><a href="#">Delete</a></div>
                <div class="group_message_option"><a onclick="showComposeModal('composeModal',<?php echo $_GET['id']; ?>)" href="#">Respond</a></div>
                <div class="group_message_option_disabled">Forward</div>
            </div>
            <div class="group_message_content">
                <?php
                echo "<pre>";
                foreach($messages as $id) {
                    $msg = new Message($id);
                    var_dump($msg);
                }
                echo "</pre>";
                ?>
            </div>
        </div> <!-- end .group_message -->
    </div> <!-- end #group_messages -->

<?php
}

$footer = new Footer();
$footer->draw();

?>
