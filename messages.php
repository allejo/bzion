<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$header->draw("Messages");

$groups = Group::getGroups($_SESSION['bzid']);

if (isset($_GET['id'])) {
    $messages = Message::getMessages($_GET['id']);
    $currentGroup = new Group($_GET['id']);
} else {
    $messages = false;
    $currentGroup = false;
}
?>


<div class="groups">

<table class="group_list">

    <tr><th class="groups_toolbar">
        <div class="groups_toolbar_option"><a href="#">Active</a></div>
        <div class="groups_toolbar_option"><a href="#">Inactive</a></div>
        <div class="groups_toolbar_option"><a href="#">Other</a></div>
    </th></tr>

<?php

foreach ($groups as $key => $id) {
    $group = new Group($id);

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
<?php
if (!$messages) {
?>
    <div class="compose_panel">
        <div class="group_message_toolbar"><span class="group_toolbar_text">Compose a new message</span></div>
        <form class="compose_form">
            <div class="input_group">
                <label for="compose_subject">Subject:</label>
                <input id="compose_subject" class="input_group_main" name="subject" type="text" placeholder="Enter message subject"
                <?php
                    if ($messages) {
                        echo 'disabled value="', $currentGroup->getSubject(), '"';
                    }
                ?>
                >
            </div>
            <div class="input_group">
                <label for="recipient-list">Recipients:</label>
                <div class="input_group_main" style="padding: 0">
                    <select id="recipient-list" data-placeholder="Enter message recipients" multiple="" style="width:100%;" class="chosen-select">
                        <option value=""></option>
                        <optgroup label="Players">
                          <?php

                          foreach (Player::getPlayers() as $key => $bzid) {
                              $player = new Player($bzid);

                              $selected = "";
                              if ($currentGroup && $currentGroup->isMember($bzid)) {
                                  $selected = 'selected=""';
                              }

                              echo "<option $selected value=\"$bzid\">", $player->getUsername(), "</option>";
                          }

                          ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <textarea id="composeArea" class="compose_area" placeholder="Enter your message here..."></textarea>
            <br />
            <button id="composeButton" onclick="sendResponse()" type="button" class="ladda-button" data-style="zoom-out">
                <span class="ladda-label">Submit</span>
            </button>
            <button type="reset">Reset</button>
            <button>Cancel editing</button>
        </form> <!-- end .compose_form -->
    </div> <!-- end .compose_panel -->
<?php
} else {
?>
    <table class="group_message">

        <!-- <tr><th class="group_message_toolbar">
            <div class="group_message_option"><a href="#">Compose</a></div>
            <div class="group_message_option"><a href="#">Delete</a></div>
            <div class="group_message_option"><a href="#">Respond</a></div>
            <div class="group_message_option_disabled">Forward</div>
        </th></tr> -->


        <div style="clear: both"></div>


        <?php
        foreach($messages as $id) {
            echo "<tr><td class='group_message_content'>";
            $msg = new Message($id);
            echo "<div>";
            echo $msg->getContent();
            echo "</div><span class='group_message_info'>Sent by {$msg->getAuthor()->getUsername()} {$msg->getCreationDate()}</span>";
            echo "</td></tr>";
        }
        ?>

    </table> <!-- end .group_message -->

    <form class="alt_compose_form">
        <input type="text" id="composeArea" class="input_compose_area" placeholder="Enter your message here..." />
        <button id="composeButton" onclick="sendResponse()" type="button" class="ladda-button" data-style="zoom-out" data-size="xs">
            <span class="ladda-label">Submit</span>
        </button>
    </form> <!-- end .alt_compose_form -->

<?php
}
?>

</div> <!-- end .group_messages -->

<?php
$footer = new Footer();

$footer->addScript("includes/ladda/js/spin.js");
$footer->addScript("includes/ladda/js/ladda.js");
$footer->addScript("includes/chosen/chosen.jquery.min.js");
$footer->addScript("js/messages.js");

$footer->draw();

?>
