<?php

class PlayerController extends HTMLController {

    public function showAction(Player $player) {
        if ($player->isValid()) {
            $this->drawHeader("Players :: " . $player->getUsername());

            $playerTeam = $player->getTeam();
            $teamlink = $playerTeam->getName();

            if ($playerTeam->isValid()) {
                $teamlink = '<a href="' . $playerTeam->getURL() . '">' . $teamlink . '</a>';
            }

            echo "<div class='label'>" . $player->getUsername() . "</div><br />";
            echo "<div class='label label_team'> Team:" .  $teamlink . "</div>";
            echo "<div class='label'> Joined: " . $player->getJoinedDate() . "</div>";
            echo "<img class='avatar_player' src='".$player->getAvatar() . "'</div>";
            echo "<div class='descript_player'>", $player->getDescription() ,"</div>";
        } else {
            $this->drawHeader("Players");

            echo "The specified player could not be found. <br />";
        }
    }

    public function listAction() {
        $this->drawHeader("Players");

        $players = Player::getPlayers();
        ?>

        <div class="table players">
            <ul>
                <li>Name</li>
                <li>Team</li>
                <li>Joined</li>
            </ul>

        <?php
            foreach ($players as $player)
            {
                echo '<ul>';
                echo '    <li><a href="' . $player->getURL() . '">' . $player->getUsername() . '</a></li>';
                $teamlink = $player->getTeam()->getName();
                if ($player->getTeam()->isValid()) {
                    $teamlink = '<a href="' . $player->getTeam()->getURL() . '">' . $teamlink . '</a>';
                }
                echo '    <li>' . $teamlink . '</li>';
                echo '    <li>' . $player->getJoinedDate() . '</li>';
                echo '</ul>';
            }
            echo '</div>';


    }
}
