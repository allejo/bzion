<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;

class ServerController extends CRUDController
{
    public function listAction()
    {
        $servers = $this->getQueryBuilder()
            ->sortBy('name')
            ->getModels();

        return array("servers" => $servers);
    }

    public function showAction(Server $server, Player $me, Request $request)
    {
        if ($server->staleInfo()) {
            $server->forceUpdate();
        }

        if ($request->get('forced') && $me->canEdit($server)) {
            $server->forceUpdate();
        }

        return array("server" => $server);
    }

    public function statusAction(Server $server, Player $me, Request $request)
    {
        $tokenLiteral = $request->get('token');
        $csrfManager = Service::getContainer()->get('security.csrf.token_manager');
        $csrfToken = new CsrfToken('server_token', $tokenLiteral);

        if (!$csrfManager->isTokenValid($csrfToken)) {
            throw new ForbiddenException('Invalid CSRF token');
        }

        if ($server->staleInfo()) {
            $server->forceUpdate();
        }

        if ($request->get('forced') && $me->canEdit($server)) {
            $server->forceUpdate();
        }

        // Public data
        $data = [
            'status' => $server->isOnline() ? 'online' : 'offline'
        ];

        // Data that'll only be available to authenticated users
        if ($me->isValid()) {
            $data['player_count'] = $server->numPlayers();
            $data['last_update'] = $server->getLastUpdate()->diffForHumans();
        }

        return (new JsonResponse($data));
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function deleteAction(Player $me, Server $server)
    {
        return $this->delete($server, $me);
    }

    public function editAction(Player $me, Server $server)
    {
        return $this->edit($server, $me, "server");
    }

    protected function redirectTo($model)
    {
        // Redirect to the server list after creating/editing a server
        return $this->redirectToList($model);
    }
}
