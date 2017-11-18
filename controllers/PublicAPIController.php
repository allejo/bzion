<?php

use Symfony\Component\HttpFoundation\JsonResponse;

class PublicAPIController extends JSONController
{
    public function playerListAction()
    {
        $request = Service::getRequest();
        $bzidsParam = $request->get('bzids');
        $bzids = (!empty($bzidsParam)) ? explode(',', $bzidsParam) : [];

        $pqb = $this
            ->getQueryBuilder('Player')
            ->selectColumn('bzid')
        ;

        if (!empty($bzids)) {
            $pqb
                ->where('bzid')->isOneOf($bzids)
            ;
        }

        /** @var Player[] $players */
        $players = $pqb->getModels($fast = true);

        $response = array(
            'players' => array(),
            'success' => true,
            'version' => 0
        );

        foreach ($players as $player) {
            $response['players'][] = array(
                'id'       => $player->getId(),
                'bzid'     => $player->getBZID(),
                'alias'    => $player->getAlias(),
                'username' => $player->getUsername(),
                'team'     => $player->getTeam()->getId(),
                'url'      => $player->getPermaLink('show', $absolute = true),
                'elo'      => $player->getElo($request->get('season'), $request->get('year')),
            );
        }

        return new JsonResponse($response);
    }

    public function teamListAction()
    {
        /** @var Team[] $teams */
        $teams = $this->getQueryBuilder('Team')
            ->getModels($fast = true);

        $response = array(
            'teams'   => array(),
            'success' => true,
            'version' => 0
        );

        foreach ($teams as $team) {
            $response['teams'][] = array(
                'id'     => $team->getId(),
                'alias'  => $team->getAlias(),
                'name'   => $team->getName(),
                'url'    => $team->getPermaLink('show', $absolute = true),
                'status' => $team->getStatus()
            );
        }

        return new JsonResponse($response);
    }

    public function serverListAction()
    {
        /** @var Server[] $servers */
        $servers = $this->getQueryBuilder('Server')
            ->getModels($fast = true);

        $response = array(
            'servers' => array(),
            'success' => true,
            'version' => 0
        );

        foreach ($servers as $server) {
            $response['servers'][] = array(
                'id'      => $server->getId(),
                'name'    => $server->getName(),
                'address' => $server->getAddress(),
            );
        }

        return new JsonResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getLogChannel()
    {
        return 'api';
    }
}
