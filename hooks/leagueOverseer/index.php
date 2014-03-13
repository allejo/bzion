<?php

include("../../bzion-load.php");
$config = include("config.php");

// To prevent abuse of the automated system, we need to make sure that the IP making the request is one of the IPs we
// allowed in the $ALLOWED_IPS array.
if (!$config['disable_ip_check'] && !in_array($_SERVER['REMOTE_ADDR'], $config['allowed_ips']))
{
    // If server making the request isn't an official server, then log the unauthorized attempt and kill the script

    writeToDebug("Unauthorized access attempt from " . $_SERVER['REMOTE_ADDR']);
    die('Error: 403 - Forbidden');
}

// The array we will be looking at, either $_POST or $_GET depending on the status, production or development
$REPORT_METHOD = $config['report_method'];

// After the first major rewrite of the league overseer plugin, the API was introduced in order to provided backwards
// compatibility for servers that have not updated to the latest version of the plugin.
$API_VERSION = (isset($REPORT_METHOD['apiVersion'])) ? $REPORT_METHOD['apiVersion'] : 0;

// The server would like to report a match
if ($REPORT_METHOD['query'] == 'reportMatch')
{
    writeToDebug("Match data received from " . $_SERVER['REMOTE_ADDR']);
    writeToDebug("--------------------------------------");

    // Clean up user input and store it in variables [I'm using whatever bz-owl is using, sqlSafeString()]
    $teamOneWins    = $REPORT_METHOD['teamOneWins'];
    $teamTwoWins    = $REPORT_METHOD['teamTwoWins'];
    $timestamp      = $REPORT_METHOD['matchTime'];
    $duration       = $REPORT_METHOD['duration'];
    $teamOnePlayers = $REPORT_METHOD['teamOnePlayers'];
    $teamTwoPlayers = $REPORT_METHOD['teamTwoPlayers'];

    // These variables were introduced in API Version 1 so we need to set default values for servers still using the
    // old version of the league overseer plugin.
    $mapPlayed      = (isset($REPORT_METHOD['mapPlayed'])) ? $REPORT_METHOD['mapPlayed'] : null;
    $server         = ($API_VERSION >= 1) ? $REPORT_METHOD['server'] : null;
    $port           = ($API_VERSION >= 1) ? $REPORT_METHOD['port'] : null;
    $replayFile     = ($API_VERSION >= 1) ? $REPORT_METHOD['replayFile'] : null;

    // This new information was introduced when the API was introduced so at version 1 so we can only handle it if our
    // API version is greater than 1
    if ($API_VERSION >= 1)
    {
        writeToDebug("Server          : " . $server);
        writeToDebug("Port            : " . $port);
        writeToDebug("Replay File     : " . $replayFile);
    }

    // If we're using a rotational league, then we'll have different maps so display what map was used in the match. A
    // rotational league is defined as a league that allows for official matches to be played on different maps.
    if ($mapPlayed != null)
    {
        writeToDebug("Map Played      : " . $mapPlayed);
    }

    // Check which team won
    if ($teamOneWins > $teamTwoWins)
    {
        $winningTeamID      = getTeamID($teamOnePlayers);
        $winningTeamPoints  = $teamOneWins;
        $winningTeamPlayers = $teamOnePlayers;

        $losingTeamID       = getTeamID($teamTwoPlayers);
        $losingTeamPoints   = $teamTwoWins;
        $losingTeamPlayers  = $teamTwoPlayers;
    }
    else // Team two won or it was a draw
    {
        $winningTeamID      = getTeamID($teamTwoPlayers);
        $winningTeamPoints  = $teamTwoWins;
        $winningTeamPlayers = $teamTwoPlayers;

        $losingTeamID       = getTeamID($teamOnePlayers);
        $losingTeamPoints   = $teamOneWins;
        $losingTeamPlayers  = $teamOnePlayers;
    }

    // If we fail to get the the team ID for either the teams or both reported teams are the same team, we cannot
    // report the match due to it being illegal.
    if (($winningTeamID == -1 || $losingTeamID == -1) || $winningTeamID == $losingTeamID)
    {
        // An invalid team could be found in either or both teams, so we need to check both teams and log it the match
        // failure respectively.
        if ($winningTeamID == -1)
        {
            writeToDebug("The BZIDs (" . $winningTeamPlayers . ") were not found on the same team. Match invalidated.");
        }
        if ($losingTeamID == -1)
        {
            writeToDebug("The BZIDs (" . $losingTeamPlayers . ") were not found on the same team. Match invalidated.");
        }
        if ($winningTeamID == $losingTeamID)
        {
            writeToDebug("The '" . getTeamName($winningTeamID) . "' team played against each other in an official match. Match invalidated.");
        }

        writeToDebug("--------------------------------------");
        writeToDebug("End of Match Report");

        if ($winningTeamID == $losingTeamID && ($winningTeamID == -1 || $losingTeamID == -1))
        {
            echo "Holy sanity check, Batman! The same team can't play against each other in an official match.";
        }
        else
        {
            echo "An invalid player was found during the match. Please message a referee to manually report the match.";
        }
        die();
    }

    // These variables aren't score dependant since the parameter has already been
    $winningTeam = new Team($winningTeamID);
    $losingTeam  = new Team($losingTeamID);

    $winningTeamName = $winningTeam->getName();
    $winningTeamELO  = $winningTeam->getElo();
    $losingTeamName  = $losingTeam->getName();
    $losingTeamELO   = $losingTeam->getElo();

    writeToDebug("Match Time      : " . $timestamp);
    writeToDebug("Duration        : " . $duration);
    writeToDebug("");
    writeToDebug("Team '" . $winningTeamName . "'");
    writeToDebug("--------");
    writeToDebug("* Old ELO       : " . $winningTeamELO);
    writeToDebug("* Score         : " . $winningTeamPoints);
    writeToDebug("* Players");
    echoParticipants($winningTeamPlayers);
    writeToDebug("");
    writeToDebug("Team '" . $losingTeamName . "'");
    writeToDebug("--------");
    writeToDebug("* Old ELO       : " . $losingTeamELO);
    writeToDebug("* Score         : " . $losingTeamPoints);
    writeToDebug("* Players");
    echoParticipants($losingTeamPlayers);
    writeToDebug("");

    $match = Match::enterMatch($winningTeamID, $winningTeamPoints, $losingTeamID, $losingTeamPoints, $duration, $config['autoreport_uid'], $timestamp);

    writeToDebug("ELO Difference  : +/- " . $match->getEloDiff());
    writeToDebug("--------------------------------------");
    writeToDebug("End of Match Report");

    // Output the match stats that will be sent back to BZFS
    echo "(+/- " . $match->getEloDiff() . ") " . $winningTeamName . " [" . $winningTeamPoints . "] vs [" . $losingTeamPoints . "] " . $losingTeamName;
}
else if ($REPORT_METHOD['query'] == 'teamNameQuery') // We would like to get the team name for a user
{
    $player = $REPORT_METHOD['teamPlayers'];
    $teamName = (new Player($player))->getTeam()->getName();

    // We will only get -1 if a player did not belong to a team, so notify BZFS that they are teamless by sending it a
    // blank team name or a DELETE query respective to the API version.
    if ($teamName == "<em>none</em>")
    {
        if ($API_VERSION == 1)
        {
            echo json_encode(array("bzid" => "$player", "team" => ""));
        }

        die();
    }

    // If we have made it this far, then that means the player has a team so notify BZFS of the team name by either
    // sending JSON or a INSERT query
    if ($API_VERSION == 1)
    {
        echo json_encode(array("bzid" => "$player", "team" => preg_replace("/&[^\s]*;/", "", $teamName)));
    }
}
else if ($REPORT_METHOD['query'] == 'teamDump') // We are starting a server and need a database dump of all the team names
{
    if ($API_VERSION == 1)
    {
        // Create an array to store all teams and the BZIDs
        $teamArray = array();

        $teams = Team::getTeams();

        foreach ($teams as $team)
        {
            $members = $team->getMembers();
            $memberList = "";

            foreach ($members as $member)
            {
                $memberList .= $member->getBZID() . ",";
            }

            rtrim($memberList, ",");

            $teamArray[] = array("team" => preg_replace("/&[^\s]*;/", "", $team->getName()), "members" => $memberList);
        }

        // Return the JSON
        echo json_encode(array("teamDump" => $teamArray));
    }
}
else // Oh noes! Someone is trying to h4x0r us!
{
    echo "Error 404 - Page not found";
}


/**
 * Write all the match participants ot the log file
 *
 * @param $_players string The players who participated in the match
 */
function echoParticipants($_players)
{
    $matchPlayers = explode(",", $_players);

    foreach ($matchPlayers as $player)
    {
        $myPlayer = Player::getFromBZID($player);
        writeToDebug("    (" . $player . ") " . $myPlayer->getUsername());
    }
}

/**
 * Queries the database to get the team ID of which players belong to
 *
 * @param string $_players The BZIDs of players separated by commas
 * @return int The team ID
 */
function getTeamID($_players)
{
    $players = explode(",", $_players);
    $teamIDs = array();

    foreach ($players as $player)
    {
        $teamIDs[] = Player::getFromBZID($player)->getTeam()->getId();
    }

    if (count(array_unique($teamIDs)) != 1 || $teamIDs[0] == 0)
    {
        return -1;
    }

    return $teamIDs[0];
}

/**
 * Writes the specified string to the log file if logging is enabled
 *
 * @param The string that will written
 * @param string $string
 */
function writeToDebug($string)
{
    global $LOG_DETAILS, $LOG_FILE;

    if ($LOG_DETAILS === true)
    {
        $file_handler = fopen($LOG_FILE, 'a');
        fwrite($file_handler, date("Y-m-d H:i:s") . " :: " . $string . "\n");
        fclose($file_handler);
    }
}