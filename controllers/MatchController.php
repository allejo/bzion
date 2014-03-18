<?php

class MatchController extends HTMLController {

    public function cleanup() {
        $footer = new Footer();

        $footer->addScript("assets/js/matches.js");

        $footer->draw();
    }

    public function listByTeamAction(Team $team) {
        $this->drawHeader("Matches :: " . $team->getName());

        $matches = $team->getMatches();

        foreach ($matches as $match)
        {
            $team_a = $match->getTeamA();
            $team_b = $match->getTeamB();
            echo "<b>" . $team_a->getName() . " (" . $match->getTeamAPoints() . " points) vs " . $team_b->getName() . " (" . $match->getTeamBPoints() . " points) </b><br />";
            echo "+/- " . $match->getEloDiff() . "<br />";
            echo $team_a->getName() . "'s new ELO: " . $match->getTeamAEloNew() . "<br />";
            echo $team_b->getName() . "'s new ELO: " . $match->getTeamBEloNew() . "<br />";
            echo "Duration: " . $match->getDuration() . " min <br />";
            echo "Timestamp: " . $match->getTimestamp() . "<br />";
            echo "<br />";
        }
    }

    public function listAction() {
        $this->drawHeader("Matches");
        $matches = Match::getMatches();
    ?>
        <div class="matches">
            <?php
                foreach ($matches as $match)
                {
                    $team_a = $match->getTeamA();
                    $team_b = $match->getTeamB();

                    echo '<section>';
                    echo '    <div class="score">';
                    echo '        <div class="date">' . $match->getTimestamp() . '</div>';
                    echo '        <div class="teams">';
                    echo '            <div class="winner"><span class="winner_name"><a href="' . $team_a->getURL() . '">' . $team_a->getName() . '</a></span> <span class="winner_score">' . $match->getTeamAPoints() . '</span></div>';
                    echo '            <div class="loser"><span class="loser_score">' . $match->getTeamBPoints() . '</span> <span class="loser_name"><a href="' . $team_b->getURL() . '">' . $team_b->getName() . '</a></span></div>';
                    echo '        </div>';
                    echo '        <i class="more_details fa fa-plus-square-o" rel="' . $match->getId() . '"></i>';
                    echo '    </div>';

                    echo '    <div id="match-' . $match->getId() . '" class="match_details">';
                    echo '        <div class="participants">';
                    echo '            <h4>Participants</h4>';
                    echo '            <div class="team">';
                    echo '                <span>' . $team_a->getName() . '</span>';
                    echo '                <ul>';

                    if (!is_null($match->getTeamAPlayers()) && is_array($match->getTeamAPlayers()))
                    {
                        foreach ($match->getTeamAPlayers() as $player)
                        {
                            echo '            <li>' . $player->getLinkLiteral() . '</li>';
                        }
                    }
                    else
                    {
                        echo '<li><em>No Players Recorded</em></li>';
                    }

                    echo '                </ul>';
                    echo '            </div>';
                    echo '            <div class="team">';
                    echo '                <span>' . $team_b->getName() . '</span>';
                    echo '                <ul>';

                    if (!is_null($match->getTeamBPlayers()) && is_array($match->getTeamBPlayers()))
                    {
                        foreach ($match->getTeamBPlayers() as $player)
                        {
                            echo '            <li>' . $player->getLinkLiteral() . '</li>';
                        }
                    }
                    else
                    {
                        echo '                <li><em>No Players Recorded</em></li>';
                    }
                    echo '                </ul>';
                    echo '            </div>';
                    echo '        </div>';
                    echo '        <div class="information">';
                    echo '            <h4>Details</h4>';
                    echo '            <div class="match">';
                    echo '                <p><strong>Match Length:</strong> ' . $match->getDuration() . ' minutes</p>';
                    echo '                <p><strong>Elo:</strong> &plusmn; ' . $match->getEloDiff() . '</p>';
                    echo '            </div>';
                    echo '            <p><strong>Server:</strong> ' . (($match->getServerAddress() != null) ? $match->getServerAddress() : "<em>No server Recorded</em>");
                    echo '            <p><strong>Replay File:</strong> ';

                    if (!is_null($match->getTeamBPlayers()) && is_array($match->getTeamBPlayers()))
                    {
                        echo '<span title="' . $match->getReplayFileName() . '">' . $match->getReplayFileName(40) . '...</span>';
                    }
                    else
                    {
                        echo '<em>No replay filename recorded</em>';
                    }

                    echo '</p>';

                    echo '        </div>';
                    echo '    </div>';
                    echo '</section>';
                }
            ?>
        </div>
        <?php
    }
}

