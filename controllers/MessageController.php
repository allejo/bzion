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
                            <span>New</span>
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
                <form class="compose">
                    <div class="icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <input type="text" class="search" name="search" data-id="new" placeholder="Search..." />
                    <div class="icon">
                        <i class="fa fa-cog"></i>
                    </div>
                    <div style="clear: both"></div>
                </form>

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
            <div class="chat_details">
                <div class="chat_options">
                    <div class="subject">Compose a new message</div>
                    <div style="clear: both"></div>
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
                    <button id="composeButton" type="submit" class="ladda-button button submit" data-style="zoom-out">Send</button>
                </div>
            </form>
        </div>
        <?php
    }

    public function showAction(Group $discussion) {
        $messages = Message::getMessages($discussion->getId());
        ?>

            <div id="groupMessages" class="chat_area" data-id="<?php echo $discussion->getId(); ?>">
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

                <div class="chat_details">
                    <div class="chat_options">
                        <div class="subject"><?php echo $discussion->getSubject(); ?></div>
                        <div class="icon close"><i class="fa fa-times"></i></div>
                        <div class="icon"><i class="fa fa-cog"></i></div>
                        <div style="clear: both"></div>
                    </div>
                    <div class="members">
                        <?php echo $groupMembers; ?>
                    </div>
                </div>

                <div class="scrollable_messages">
                    <ul class="messages">

                    <?php
                        $prev_author = null;

                        foreach($messages as $id)
                        {
                            $msg = new Message($id);
                            $sentByMe = ($msg->getAuthor()->getId() == $_SESSION['playerId']);

                            echo '<li>';
                            echo '    <div class="bubble' . (($sentByMe) ? " me" : "" ) . '">';

                            // Only display the author details for when the message wasn't sent from the previous user
                            if ($msg->getAuthor()->getID() != $prev_author)
                            {
                                echo '        <div class="details">';
                                echo "            <div class='author'>{$msg->getAuthor()->getUsername()}</div>";
                                echo "            <div class='date'>{$msg->getCreationDate()}</div>";
                                echo '        </div>';
                            }

                            echo "        <p>{$msg->getContent()}</p>";
                            echo '    </div>';
                            echo '</li>';

                            $prev_author = $msg->getAuthor()->getID();
                        }
                    ?>

                    </ul>
                </div>

                <form class="reply_form" autocomplete="off">
                    <div class="quick_reply">
                        <textarea id="composeArea" class="input_compose_area" placeholder="Enter your message here..." ></textarea>
                        <button id="composeButton" type="submit" class="ladda-button" data-style="zoom-out" data-size="xs">
                            <span class="ladda-label">Send</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php
    }
}
