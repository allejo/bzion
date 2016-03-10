<?php

use Symfony\Component\HttpFoundation\JsonResponse;

class PublicAPIController extends JSONController
{
    public function playerListAction()
    {
        /** @var Player[] $players */
        $players = $this->getQueryBuilder('Player')
            ->getModels($fast = true);

        $response = array(
            'players' => array(),
            'success' => true,
            'version' => 0
        );

        foreach ($players as $player) {
            $response['players'][] = array(
                'id' => $player->getId(),
                'alias' => $player->getAlias(),
                'username' => $player->getUsername(),
                'team' => $player->getTeam()->getId(),
                'url' => $player->getPermaLink('show', $absolute = true)
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
            'teams' => array(),
            'success' => true,
            'version' => 0
        );

        foreach ($teams as $team) {
            $response['teams'][] = array(
                'id' => $team->getId(),
                'alias' => $team->getAlias(),
                'name' => $team->getName(),
                'url' => $team->getPermaLink('show', $absolute = true),
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
                'id' => $server->getId(),
                'name' => $server->getName(),
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
