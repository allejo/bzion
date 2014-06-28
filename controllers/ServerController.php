<?php

class ServerController extends HTMLController
{
    public function listAction()
    {
        $servers = Server::getServers();

        return array("servers" => $servers);
    }

    public function showAction(Server $server)
    {
        if ($server->staleInfo()) {
            $server->forceUpdate();
        }

        return array("server" => $server);
    }
}
