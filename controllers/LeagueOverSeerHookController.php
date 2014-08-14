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
            return 'Returning team name...';
        case 'teamDump':
            return 'Dumping team members...';
        default:
            throw new BadRequestException();
        }
    }
}
