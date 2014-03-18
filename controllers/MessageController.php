<?php

class MessageController extends HTMLController {

    /**
    * @todo Show an error
    */
    public function setup() {
        $header = new Header();

        if (!isset($_SESSION['username'])) {
            $header::go("home");
        }

        $this->drawHeader("Messages");
        $groups = Group::getGroups($_SESSION['playerId']);

        ?>
        <div class="messaging">
            <section class="toolbar">
                <ul>
                    <li class="separator-bottom">
                        <a href="<?= $this->generate("message_list") ?>">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fa fa-inbox"></i>
                            <span>Inbox</span>
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="fa fa-trash-o"></i>
                            <span>Trash</span>
                        </a>
                    </li>
                </ul>
            </section>
            <section class="conversations">
                <a href="<?= $this->generate("message_list") ?>" data-id="new" class="compose">New Message</a>
                <ul class="chats">
                <?php
                    foreach ($groups as $key => $id)
                    {
                        $group = new Group($id);

                        echo '<li>';
                        echo '    <a data-id="' . $group->getId() . '" href="' . $group->getURL() . '">';
                        echo '        <div class="subject">' . $group->getSubject() . '</div>';
                        echo '        <div class="last_activity">' . $group->getLastActivity() . '</div>';
                        echo '        <div style="clear:both"></div>';

                        $groupUsernames = array();

                        foreach ($group->getMembers(true) as $key => $value)
                        {
                            $player = new Player($value);
                            $groupUsernames[] = $player->getUsername();
                        }

                        if (count($groupUsernames) == 0)
                        {
                            $groupMembers = "No other recipients";
                        }
                        else
                        {
                            $groupMembers = implode(", ", $groupUsernames);
                        }

                        $lastMessage    = $group->getLastMessage();
                        $playerFrom     = $lastMessage->getAuthor()->getUsername();
                        $messageSummary = $lastMessage->getSummary(50);

                        echo '        <div class="members">' . $groupMembers . '</div>';
                        echo '        <div class="last_message"><strong>' . $playerFrom . ':</strong> ' . $messageSummary . '</div>';
                        echo '    </a>';
                        echo '</li>';
                    }
                ?>
                </ul>
            </section>
        <?php


    }

    public function cleanup() {
        echo '</div>';

        $footer = new Footer();

        $footer->addScript("includes/ladda/dist/spin.min.js");
        $footer->addScript("includes/ladda/dist/ladda.min.js");
        $footer->addScript("includes/chosen/chosen.jquery.min.js");
        $footer->addScript("assets/js/messages.js");

        $footer->draw();
    }

    public function composeAction() {
        ?>
        <div id="groupMessages" class="chat_area">
            <div class="group_message_toolbar">
                <div class="group_message_title_top">
                    <div class="group_message_compose">Compose a new message</div>
                </div>
            </div>
            <form class="compose_form">
                <div class="input_group">
                    <div class="input_group_row">
                        <label for="compose_recipients">Recipients:</label>
                        <div class="input_group_main" style="padding: 0">
                            <select id="compose_recipients" data-placeholder="Enter message recipients" multiple="" style="width:100%;" class="chosen-select">
                                <option value=""> </option>
                                <optgroup label="Players">
                                    <?php
                                        foreach (Player::getPlayers() as $player)
                                        {
                                            // Don't add the currently logged in player to the list of possible recipients
                                            if ($player->getId() == $_SESSION['playerId'])
                                            {
                                                continue;
                                            }

                                            $selected = "";

                                            echo "<option $selected value=\"{$player->getId()}\">", $player->getUsername(), "</option>";
                                        }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="input_group_row">
                        <label for="compose_subject">Subject:</label>
                        <div class="input_group_main">
                            <input id="compose_subject" name="subject" type="text" placeholder="Enter message subject">
                        </div>
                    </div>
                </div>
                <textarea id="composeArea" class="compose_area" placeholder="Enter your message here..."></textarea>
                <div class="buttons">
                    <button id="composeButton" onclick="sendMessage()" type="button" class="ladda-button button submit" data-style="zoom-out">Send</button>
                </div>
            </form>
        </div>
        <?php
    }

    public function showAction(Group $discussion) {
        $messages = Message::getMessages($discussion->getId());
        ?>

            <div id="groupMessages" class="chat_area">
                <?php
                    $groupUsernames = array();

                    foreach ($discussion->getMembers(true) as $key => $value)
                    {
                        $player = new Player($value);
                        $groupUsernames[] = $player->getUsername();
                    }

                    if (count($groupUsernames) == 0)
                    {
                        $groupMembers = "No other recipients";
                    }
                    else
                    {
                        $groupMembers = implode(", ", $groupUsernames);
                    }
            ?>

            <div class="group_message_toolbar">
                <div class="group_message_title_top">
                    <div class="group_message_title"><?php echo $discussion->getSubject(); ?></div>
                    <div class="group_message_title_icon close"><i class="fa fa-times"></i></div>
                    <div class="group_message_title_icon"><i class="fa fa-cog"></i></div>
                    <div style="clear: both"></div>
                </div>
                <div class="group_message_title_bottom">
                    <div class="group_message_title_members"><?php echo $groupMembers; ?></div>
                </div>
            </div>


            <div style="clear: both"></div>

            <div class="group_message_scroll">

                <table class="group_message">

                    <?php
                    $prev_author = null;
                    foreach($messages as $id) {
                        $msg = new Message($id);
                        $who = "other";
                        if ($msg->getAuthor()->getId() == $_SESSION['playerId'])
                            $who = "me";
                        echo "<tr><td>";
                        if ($msg->getAuthor()->getID() != $prev_author)
                            echo "<div class='group_message_author_$who'>{$msg->getAuthor()->getUsername()}</div>";
                        echo "<div class='group_message_content group_message_from_$who'>";
                        echo $msg->getContent();
                        echo "</div>";
                        echo "<div class='group_message_date_$who'>{$msg->getCreationDate()}</div>";
                        echo "</td></tr>";
                        $prev_author = $msg->getAuthor()->getID();
                    }
                    ?>

                </table> <!-- end .group_message -->

            </div> <!-- end .group_message_scroll -->

            <form class="alt_compose_form" autocomplete="off">
                <input type="text" id="composeArea" class="input_compose_area" placeholder="Enter your message here..." />
                <button id="composeButton" onclick="sendResponse()" type="submit" class="ladda-button" data-style="zoom-out" data-size="xs">
                    <span class="ladda-label">Send</span>
                </button>
            </form> <!-- end .alt_compose_form -->
        </div>

        <?php
    }
}
