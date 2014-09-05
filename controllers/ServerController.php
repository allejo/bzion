<?php

use Symfony\Component\HttpFoundation\Request;

class ServerController extends CRUDController
{
    public function listAction()
    {
        $servers = $this->getQueryBuilder()->getModels();

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

    protected function update($form, $server, $me)
    {
        $server->setName($form->get('name')->getData())
               ->setAddress($form->get('address')->getData())
               ->setOwner($form->get('owner')->getData()->getId())
               ->forceUpdate();

        return $server;
    }

    protected function enter($form, $me)
    {
        return Server::addServer(
            $form->get('name')->getData(),
            $form->get('address')->getData(),
            1,
            $form->get('owner')->getData()->getId()
        );
    }
}
