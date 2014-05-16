<?php

class ServerController extends HTMLController
{
    public function listAction()
    {
        $servers = Server::getServers();

        foreach ($servers as $server) {
            if ($server->staleInfo())
                $server->forceUpdate();
        }

        return array("servers" => $servers);
    }
}
