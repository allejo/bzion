<?php

use BZIon\Form\ModelType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TeamController extends CRUDController
{
    /**
     * The new leader if we're changing them
     * @var null|Player
     */
    private $newLeader = null;

    public function showAction(Team $team)
    {
        return array("team" => $team);
    }

    public function listAction()
    {
        return array("teams" => Team::getTeams());
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Team $team)
    {
        $response = $this->edit($team, $me, "team");

        if ($this->newLeader) {
            // Redirect to a confirmation form if we are assigning a new leader
            $url = Service::getGenerator()->generate('team_assign_leader', array(
                'team' => $team->getAlias(),
                'player' => $this->newLeader->getAlias()
            ));

            return new RedirectResponse($url);
        }

        return $response;
    }

    public function deleteAction(Player $me, Team $team)
    {
        $members = $team->getMembers();
        $name    = $team->getName();

        return $this->delete($team, $me, function () use ($members, $me, $name) {
            foreach ($members as $member) {
                // Do not notify the user who initiated the deletion
                if ($me->getId() != $member->getId()) {
                    $member->notify(Notification::TEAM_DELETED, array(
                        'by'   => $me->getId(),
                        'team' => $name
                    ));
                }
            }
        });
    }

    public function joinAction(Team $team, Player $me)
    {
        $this->requireLogin();
        if (!$me->isTeamless()) {
            throw new ForbiddenException("You are already a member of a team");
        } elseif ($team->getStatus() != 'open') {
            throw new ForbiddenException("This team is not accepting new members without an invitation");
        }

        return $this->showConfirmationForm(function () use (&$team, &$me) {
            $team->addMember($me->getId());
            $team->getLeader()->notify(Notification::TEAM_JOIN, array(
                'player' => $me->getId(),
                'team'   => $team->getId()
            ));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }

    public function inviteAction(Team $team, Player $player, Player $me)
    {
        $this->assertCanEdit($me, $team, "You are not allowed to invite a player to that team!");

        if ($team->isMember($player->getId()))
            throw new ForbiddenException("The specified player is already a member of that team.");

        if (Invitation::hasOpenInvitation($player->getId(), $team->getId())) {
            throw new ForbiddenException("This player has already been invited to join the team.");
        }

        return $this->showConfirmationForm(function () use (&$team, &$player, &$me) {
            $invite = Invitation::sendInvite($player->getId(), $me->getId(), $team->getId());
            $player->notify(Notification::TEAM_INVITE, array('id' => $invite->getId()));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to invite {$player->getEscapedUsername()} to {$team->getEscapedName()}?",
            "Player {$player->getUsername()} has been invited to {$team->getName()}");
    }

    public function kickAction(Team $team, Player $player, Player $me)
    {
        $this->assertCanEdit($me, $team, "You are not allowed to kick a player off that team!");

        if ($team->getLeader()->getId() == $player->getId())
            throw new ForbiddenException("You can't kick the leader off their team.");

        if (!$team->isMember($player->getId()))
            throw new ForbiddenException("The specified player is not a member of that team.");

        return $this->showConfirmationForm(function () use (&$me, &$team, &$player) {
            $team->removeMember($player->getId());
            $player->notify(Notification::TEAM_KICKED, array(
                'by' => $me->getId(),
                'team' => $team->getId()
            ));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to kick {$player->getEscapedUsername()} from {$team->getEscapedName()}?",
            "Player {$player->getUsername()} has been kicked from {$team->getName()}", "Kick");
    }

    public function abandonAction(Team $team, Player $me)
    {
        if (!$team->isMember($me->getId()))
            throw new ForbiddenException("You are not a member of that team!");

        if ($team->getLeader()->getId() == $me->getId())
            throw new ForbiddenException("You can't abandon the team you are leading.");

        return $this->showConfirmationForm(function () use (&$team, &$me) {
            $team->removeMember($me->getId());
            $team->getLeader()->notify(Notification::TEAM_ABANDON, array(
                'player' => $me->getId(),
                'team'   => $team->getId()
            ));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to abandon {$team->getEscapedName()}?",
            "You have left {$team->getName()}", "Abandon");
    }

    public function assignLeaderAction(Team $team, Player $me, Player $player)
    {
        $this->assertCanEdit($me, $team, "You are not allowed to change the leader of this team.");

        if (!$team->isMember($player->getId()))
            throw new ForbiddenException("The specified player is not a member of {$team->getEscapedName()}");

        return $this->showConfirmationForm(function() use ($player, $team) {
            $team->setLeader($player->getId());
            return new RedirectResponse($team->getUrl());
        }, "Are you sure you want to transfer the leadership of the team to <strong>{$player->getEscapedUsername()}</strong>?",
        "{$player->getUsername()} is now leading {$team->getName()}",
        "Appoint leadership");
    }

    public function createForm($edit, Team $team=null)
    {
        $builder = Service::getFormFactory()->createBuilder()
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(), new Length(array(
                        'min' => 2,
                        'max' => 32,
                    ))
                )
            ))
            ->add('description', 'textarea', array(
                'required' => false
            ));

        if ($edit) {
            // We are editing the team, not creating it
            // Let the user appoint a different leader
            $builder->add('leader', new ModelType('Player', false, function ($query) use ($team) {
                // Only list players belonging in that team
                return $query->where('team')->is($team);
            }));
        }

        return $builder->add('status', 'choice', array(
                'choices' => array(
                    'open'   => 'Open',
                    'closed' => 'Closed',
                ),
            ))
            ->add('submit', 'submit')
            ->getForm();
    }

    protected function enter($form, $creator)
    {
        return Team::createTeam(
            $form->get('name')->getData(),
            $creator->getId(),
            '',
            $form->get('description')->getData(),
            $form->get('status')->getData()
        );
    }

    protected function fill($form, $team)
    {
        $form->get('name')->setData($team->getName());
        $form->get('description')->setData($team->getDescription(true));
        $form->get('status')->setData($team->getStatus());
        $form->get('leader')->setData($team->getLeader());
    }

    protected function update($form, $team, $me)
    {
        $team->setName($form->get('name')->getData());
        $team->setDescription($form->get('description')->getData());
        $team->setStatus($form->get('status')->getData());

        // Is the player updating the team's leader?
        // Don't let them do it right away - issue a confirmation notice first
        $leader = $form->get('leader')->getData();

        if ($leader->getId() != $team->getLeader()->getId()) {
            $this->newLeader = $leader;
        }

        return $team;
    }

    protected function validateNew($form)
    {
        $name = $form->get('name');
        $team = Team::getFromName($name->getData());

        // The name for the team that the user gave us already exists
        // TODO: This takes deleted teams into account, do we want that?
        if ($team->isValid())
            $name->addError(new FormError("A team called {$team->getEscapedName()} already exists"));
    }

    protected function canCreate($player)
    {
        if ($player->getTeam()->isValid())
            throw new ForbiddenException("You need to abandon your current team before you can create a new one");

        return parent::canCreate($player);
    }

    /**
     * Make sure that a player can edit a team
     *
     * Throws an exception if a player is not an admin or the leader of a team
     * @throws HTTPException
     * @param  Player        $player  The player to test
     * @param  Team          $team    The team
     * @param  string        $message The error message to show
     * @return void
     */
    private function assertCanEdit(Player $player, Team $team, $message="You are not allowed to edit that team")
    {
        if (!$player->hasPermission(Permission::EDIT_TEAM))
            if ($team->getLeader()->getId() != $player->getId())
                throw new ForbiddenException($message);
    }
}
