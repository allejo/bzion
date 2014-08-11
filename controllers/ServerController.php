<?php

use BZIon\Form\PlayerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ServerController extends CRUDController
{
    public function listAction()
    {
        $servers = Server::getServers();

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

    protected function fill($form, $server)
    {
        $form->get('name')->setData($server->getName());
        $form->get('address')->setData($server->getAddress());
        $form->get('owner')->get('players')->setData($server->getOwner());
    }

    protected function update($form, $server, $me)
    {
        $server->setName($form->get('name')->getData())
               ->setAddress($form->get('address')->getData())
               ->setOwner($form->get('owner')->getData()->getId());

        return $server;
    }

    protected function enter($form, $me)
    {
        return Server::addServer(
            $form->get('name')->getData(),
            $form->get('address')->getData(),
            $form->get('owner')->getData()->getId()
        );
    }

    public function createForm()
    {
        return Service::getFormFactory()->createBuilder()
            ->add('address', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 50,
                    )),
                ),
            ))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'max' => 100,
                    )),
                ),
            ))
            ->add('owner', new PlayerType())
            ->add('add', 'submit')
            ->getForm();
    }
}
