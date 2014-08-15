<?php

use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LeagueOverseerHookController extends JSONController
{
    /**
     * The API version of the server performing the request
     * @var int
     */
    private $version;

    /**
     * The parameter bag representing the $_GET or $_POST array
     * @var Symfony\Component\HttpFoundation\ParameterBag
     */
    private $params;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $request = $this->getRequest();

        // To prevent abuse of the automated system, we need to make sure that
        // the IP making the request is one of the IPs we allowed in the config
        $allowedIPs = array_map('trim', explode(',', ALLOWED_IPS));
        $clientIP   = $request->getClientIp();

        if (DEVELOPMENT < 1 && // Don't care about IPs if we're in debug mode
           !in_array($clientIP, $allowedIPs)) {
            // If server making the request isn't an official server, then log the unauthorized attempt and kill the script

            Service::getContainer()->get('logger')->addNotice("Unauthorized access attempt from $clientIP");
            throw new ForbiddenException("Error: 403 - Forbidden");
        }

        // We will be looking at either $_POST or $_GET depending on the status, production or development
        $this->params = $request->request; // $_POST

        if (!$this->params->has('query')) {
            // There seems to be nothing in $_POST. If we are in debug mode
            // however, we might have a debug request with data in $_GET
            if (DEVELOPMENT && $request->query->has('query')) {
                $this->params = $request->query; // $_GET
            } else {
                throw new BadRequestException();
            }
        }

        // After the first major rewrite of the league overseer plugin, the
        // API was introduced in order to provide backwards compatibility for
        // servers that have not updated to the latest version of the plugin.
        $this->version = $this->params->get('apiVersion', 0);
    }

    /**
     * @todo Test/improve/revoke support for API version 0
     */
    public function queryAction()
    {
        switch ($this->params->get('query')) {
        case 'reportMatch':
            return $this->forward('reportMatch');
        case 'teamNameQuery':
            return $this->forward('teamName');
        case 'teamDump':
            return $this->forward('teamDump');
        default:
            throw new BadRequestException();
        }
    }

    public function teamNameAction()
    {
        if ($this->version < 1) {
            throw new BadRequestException();
        }

        $bzid = (int) $this->params->get('teamPlayers');
        $team = Player::getFromBZID($bzid)->getTeam();

        return new JsonResponse(array(
            "bzid" => $bzid,
            "team" => ($team->isValid()) ? preg_replace("/&[^\s]*;/", "", $team->getName()) : ''
        ));
    }

    public function teamDumpAction()
    {
        if ($this->version < 1) {
            throw new BadRequestException();
        }

        // Create an array to store all teams and the BZIDs
        $teamArray = array();

        foreach (Team::getTeams() as $team) {
            $memberList = "";

            foreach ($team->getMembers() as $member) {
                $memberList .= $member->getBZID() . ",";
            }

            $teamArray[] = array(
                "team" => preg_replace("/&[^\s]*;/", "", $team->getName()),
                "members" => rtrim($memberList, ",")
            );
        }

        return new JsonResponse(array("teamDump" => $teamArray));
    }

    public function reportMatchAction(Logger $log, Request $request)
    {
        $log->addNotice("Match data received from " . $request->getClientIp());

        $teamOneBZIDs = $this->params->get('teamOnePlayers');
        $teamTwoBZIDs = $this->params->get('teamTwoPlayers');

        $teamOnePlayers = $this->bzidsToIdArray($teamOneBZIDs);
        $teamTwoPlayers = $this->bzidsToIdArray($teamTwoBZIDs);

        $teamOne = $this->getTeam($teamOnePlayers);
        $teamTwo = $this->getTeam($teamTwoPlayers);

        // If we fail to get the the team ID for either the teams or both reported teams are the same team, we cannot
        // report the match due to it being illegal.

        // An invalid team could be found in either or both teams, so we need to check both teams and log the match
        // failure respectively.
        $error = true;
        if (!$teamOne->isValid()) {
            $log->addNotice("The BZIDs ($teamOneBZIDs) were not found on the same team. Match invalidated.");
        } elseif (!$teamTwo->isValid()) {
            $log->addNotice("The BZIDs ($teamTwoBZIDs) were not found on the same team. Match invalidated.");
        } else {
            $error = false;
        }

        if ($error) {
            throw new ForbiddenException("An invalid player was found during the match. Please message a referee to manually report the match");
        }

        if ($teamOne->getId() == $teamTwo->getId()) {
            $log->addNotice("The '" . $teamOne->getName() . "' team played against each other in an official match. Match invalidated.");
            throw new ForbiddenException("Holy sanity check, Batman! The same team can't play against each other in an official match.");
        }

        $match = Match::enterMatch(
            $teamOne->getId(),
            $teamTwo->getId(),
            $this->params->get('teamOnePoints'),
            $this->params->get('teamTwoPoints'),
            $this->params->get('duration'),
            null,
            $this->params->get('matchTime'),
            $teamOnePlayers,
            $teamTwoPlayers,
            $this->params->get('server'),
            $this->params->get('port'),
            $this->params->get('replayFile'),
            $this->params->get('mapPlayed')
        );

        $log->addNotice("Match reported automatically", array(
            'winner' => array(
                'name' => $match->getWinner()->getName(),
                'score' => $match->getScore($match->getWinner()),
            ),
            'loser' => array(
                'name' => $match->getLoser()->getName(),
                'score' => $match->getScore($match->getLoser())
            ),
            'eloDiff' => $match->getEloDiff()
        ));

        // Output the match stats that will be sent back to BZFS
        return sprintf("(+/- %d) %s [%d] vs [%d] %s",
            $match->getEloDiff(),
            $match->getWinner()->getName(),
            $match->getScore($match->getWinner()),
            $match->getScore($match->getLoser()),
            $match->getLoser()->getName()
        );
    }

    /**
     * Convert a comma-separated list of bzids to player IDs so we can pass
     * them to Match::enterMatch()
     *
     * @param  string $players A comma-separated list of BZIDs
     * @return int[]  A list of Player IDs
     */
    private function bzidsToIdArray($players)
    {
        $players = explode(',', $players);

        foreach ($players as &$player) {
            $player = Player::getFromBZID($player)->getId();
        }

        return $players;
    }

    /**
     * Queries the database to get the team which a group of players belong to
     *
     * @param  int[] $players The IDs of players
     * @return Team  The team
     */
    private function getTeam($players)
    {
        $team = null;

        foreach ($players as $id) {
            $player = new Player($id);

            if ($player->isTeamless()) {
                return Team::invalid();
            } elseif ($team == null) {
                $team = $player->getTeam();
            } elseif ($team->getId() != $player->getTeam()->getId()) {
                // This player is on a different team from the previous one!
                return Team::invalid();
            }
        }

        return $team;
    }
}
