<?php

$header = new Header();

if (isset($_GET['alias'])) {
    $team = Team::getFromAlias($_GET['alias']);
} else if (isset($_GET['id'])) {
    $team = new Team($_GET['id']);
}

if (isset($team)) {
    $header->draw("Matches :: " . $team->getName());

    $matches = $team->getMatches();

    foreach ($matches as $match) {

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

} else {

    $header->draw("Matches");

    $matches = Match::getMatches();

?>

<div class="table matches">
    <ul>
        <li>Date</li>
        <li>Teams</li>
        <li>Score</li>
        <li>Duration</li>
        <li>Reporter</li>
    </ul>

    <?php
        foreach ($matches as $match)
        {
            $team_a = $match->getTeamA();
            $team_b = $match->getTeamB();
            echo '<ul>';
            echo '    <li>' . $match->getTimestamp() . '</li>';
            echo '    <li><a href="' . $team_a->getURL() . '">' . $team_a->getName() . '</a> vs <a href="' . $team_b->getURL() . '">' . $team_b->getName() . '</a></li>';
            echo '    <li>' . $match->getTeamAPoints() . ' - ' . $match->getTeamBPoints() . '</li>';
            echo '    <li>' . $match->getDuration() . '</li>';
            echo '    <li><a href="' . $match->getEnteredBy()->getURL() . '">' . $match->getEnteredBy()->getUsername() . '</a></li>';
            echo '</ul>';
        }
    ?>
</div>

<?php

}

$footer = new Footer();
$footer->draw();

?>
