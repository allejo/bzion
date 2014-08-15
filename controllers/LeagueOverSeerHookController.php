<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LeagueOverSeerHookController extends JSONController
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
     * @todo Add IP check
     * @todo Test/revoke support for API version 0
     */
    public function queryAction(Request $request)
    {
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

        $this->version = $this->params->get('apiVersion', 0);

        switch ($this->params->get('query')) {
        case 'reportMatch':
            return 'Reporting match...';
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
}
