<?php

use BZIon\Form\IpType;
use BZIon\Form\PlayerType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class BanController extends CRUDController
{
    public function showAction(Ban $ban)
    {
        return array("ban" => $ban);
    }

    public function listAction()
    {
        return array("bans" => Ban::getBans());
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Ban $ban)
    {
        return $this->edit($ban, $me, "ban");
    }

    public function deleteAction(Player $me, Ban $ban)
    {
        return $this->delete($ban, $me);
    }

    public function unbanAction(Player $me, Ban $ban)
    {
        if (!$this->canEdit($me, $ban)) {
            throw new ForbiddenException("You are not allowed to unban a player.");
        } elseif ($ban->hasExpired()) {
            throw new ForbiddenException("Sorry, this ban has already expired.");
        }

        $victim = $ban->getVictim()->getEscapedUsername();

        return $this->showConfirmationForm(function () use (&$ban) {
            $ban->expire();

            return new RedirectResponse($ban->getUrl());
        }, "Are you sure you want to unban <strong>$victim</strong>?",
            "$victim's ban has been deactivated successfully", "Unban");
    }

    protected function fill($form, $ban)
    {
        $form->get('player')->get('players')->setData($ban->getVictim());
        $form->get('reason')->setData($ban->getReason());
        $form->get('server_message')->setData($ban->getServerMessage());
        $form->get('server_join_allowed')->setData($ban->allowedServerJoin());
        $form->get('ip_addresses')->setData($ban->getIpAddresses());

        if ($ban->willExpire()) {
            $form->get('expiration')->setData($ban->getExpiration());
        } else {
            $form->get('automatic_expiration')->setData(false);
        }
    }

    protected function update($form, $ban, $me)
    {
        $ban->setIPs($form->get('ip_addresses')->getData())
            ->setExpiration($this->getExpiration($form))
            ->setReason($form->get('reason')->getData())
            ->setServerMessage($form->get('server_message')->getData())
            ->setAllowServerJoin($form->get('server_join_allowed')->getData());

        return $ban;
    }

    protected function enter($form, $me)
    {
        return Ban::addBan(
            $form->get('player')->getData()->getId(),
            $me->getId(),
            $this->getExpiration($form),
            $form->get('reason')->getData(),
            $form->get('server_message')->getData(),
            $form->get('ip_addresses')->getData(),
            $form->get('server_join_allowed')->getData()
        );
    }

    public function createForm($edit)
    {
        $builder = Service::getFormFactory()->createBuilder();

        return $builder
            ->add('player', new PlayerType(), array(
                'disabled' => $edit,
            ))
            ->add(
                $builder->create('automatic_expiration', 'checkbox', array(
                    'data' => true,
                    'required' => false,
                ))->setDataLocked(false)
            )
            ->add(
                $builder->create('expiration', 'datetime', array(
                    'data' => TimeDate::now(),
                ))->setDataLocked(false)
            )
            ->add('reason', 'text', array(
                'constraints' => new NotBlank(),
            ))
            ->add('server_join_allowed', 'checkbox', array(
                'data' => true,
                'required' => false,
            ))
            ->add('server_message', 'text', array(
                'required' => false,
                'constraints' => new Length(array(
                    'max' => 150,
                ))
            ))
            ->add('ip_addresses', new IpType(), array(
                'required' => false,
            ))
            ->add('enter', 'submit')
            ->setDataLocked(false)
            ->getForm();
    }

    /**
     * Get the expiration time of the ban based on the fields of the form
     *
     * @param  Form $form The form
     * @return TimeDate|null
     */
    private function getExpiration($form)
    {
        if ($form->get('automatic_expiration')->getData()) {
            return $form->get('expiration')->getData();
        } else {
            return null;
        }
    }
}
