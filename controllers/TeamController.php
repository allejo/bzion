<?php

use BZIon\Event as Event;
use BZIon\Event\Events;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TeamController extends CRUDController
{
    /**
     * The new leader if we're changing them
     * @var null|Player
     */
    private $newLeader = null;

    public function showAction(Team $team)
    {
        $creationDate = $team->getCreationDate()->setTimezone('UTC')->startOfMonth();

        $matches = Match::getQueryBuilder()
            ->with($team)
            ->getSummary($creationDate);

        $wins = Match::getQueryBuilder()
            ->with($team, "win")
            ->getSummary($creationDate);

        return [
            'matches' => $matches,
            'wins'    => $wins,
            'team'    => $team
        ];
    }

    public function listAction()
    {
        Team::$cachedMatches = Match::getQueryBuilder()
            ->where('time')->isAfter(TimeDate::from('45 days ago'))
            ->active()
            ->getModels($fast = true);

        $teams = $this->getQueryBuilder()
            ->sortBy('elo')->reverse()
            ->getModels($fast = true);

        return array(
            "teams" => $teams
        );
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function editAction(Player $me, Team $team)
    {
        // TODO: Generating this response is unnecessary
        $response = $this->edit($team, $me, "team");

        if ($this->newLeader) {
            // Redirect to a confirmation form if we are assigning a new leader
            $url = Service::getGenerator()->generate('team_assign_leader', array(
                'team'   => $team->getAlias(),
                'player' => $this->newLeader->getAlias()
            ));

            return new RedirectResponse($url);
        }

        return $response;
    }

    public function deleteAction(Player $me, Team $team)
    {
        $members = $team->getMembers();

        return $this->delete($team, $me, function () use ($team, $me, $members) {
            $event = new Event\TeamDeleteEvent($team, $me, $members);
            Service::getDispatcher()->dispatch(Events::TEAM_DELETE, $event);
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

        return $this->showConfirmationForm(function () use ($team, $me) {
            $team->addMember($me->getId());
            Service::getDispatcher()->dispatch(Events::TEAM_JOIN,  new Event\TeamJoinEvent($team, $me));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to join {$team->getEscapedName()}?",
            "You are now a member of {$team->getName()}");
    }

    public function kickAction(Team $team, Player $player, Player $me)
    {
        $this->assertCanEdit($me, $team, "You are not allowed to kick a player off that team!");

        if ($team->getLeader()->isSameAs($player)) {
            throw new ForbiddenException("You can't kick the leader off their team.");
        }

        if (!$team->isMember($player->getId())) {
            throw new ForbiddenException("The specified player is not a member of that team.");
        }

        return $this->showConfirmationForm(function () use ($me, $team, $player) {
            $team->removeMember($player->getId());
            $event = new Event\TeamKickEvent($team, $player, $me);
            Service::getDispatcher()->dispatch(Events::TEAM_KICK, $event);

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to kick {$player->getEscapedUsername()} from {$team->getEscapedName()}?",
            "Player {$player->getUsername()} has been kicked from {$team->getName()}", "Kick");
    }

    public function abandonAction(Team $team, Player $me)
    {
        if (!$team->isMember($me->getId())) {
            throw new ForbiddenException("You are not a member of that team!");
        }

        if ($team->getLeader()->isSameAs($me)) {
            throw new ForbiddenException("You can't abandon the team you are leading.");
        }

        return $this->showConfirmationForm(function () use ($team, $me) {
            $team->removeMember($me->getId());
            Service::getDispatcher()->dispatch(Events::TEAM_ABANDON, new Event\TeamAbandonEvent($team, $me));

            return new RedirectResponse($team->getUrl());
        },  "Are you sure you want to abandon {$team->getEscapedName()}?",
            "You have left {$team->getName()}", "Abandon");
    }

    public function assignLeaderAction(Team $team, Player $me, Player $player)
    {
        $this->assertCanEdit($me, $team, "You are not allowed to change the leader of this team.");

        if (!$team->isMember($player->getId())) {
            throw new ForbiddenException("The specified player is not a member of {$team->getName()}");
        } elseif ($team->getLeader()->isSameAs($player)) {
            throw new ForbiddenException("{$player->getUsername()} is already the leader of {$team->getName()}");
        }

        return $this->showConfirmationForm(function () use ($player, $team) {
            $event = new Event\TeamLeaderChangeEvent($team, $player, $team->getLeader());
            $team->setLeader($player->getId());
            Service::getDispatcher()->dispatch(Events::TEAM_LEADER_CHANGE, $event);

            return new RedirectResponse($team->getUrl());
        }, "Are you sure you want to transfer the leadership of the team to <strong>{$player->getEscapedUsername()}</strong>?",
        "{$player->getUsername()} is now leading {$team->getName()}",
        "Appoint leadership");
    }

    /**
     * Show a confirmation form to confirm that the user wants to assign a new
     * leader to a team
     *
     * @param Player $leader The new leader
     */
    public function newLeader($leader)
    {
        $this->newLeader = $leader;
    }

    protected function validateNew($form)
    {
        $name = $form->get('name');
        $team = Team::getFromName($name->getData());

        // The name for the team that the user gave us already exists
        // TODO: This takes deleted teams into account, do we want that?
        if ($team->isValid()) {
            $name->addError(new FormError("A team called {$team->getName()} already exists"));
        }
    }

    protected function canCreate($player)
    {
        if ($player->getTeam()->isValid()) {
            throw new ForbiddenException("You need to abandon your current team before you can create a new one");
        }

        return parent::canCreate($player);
    }

    /**
     * Make sure that a player can edit a team
     *
     * Throws an exception if a player is not an admin or the leader of a team
     * @param  Player        $player  The player to test
     * @param  Team          $team    The team
     * @param  string        $message The error message to show
     * @throws HTTPException
     * @return void
     */
    private function assertCanEdit(Player $player, Team $team, $message = "You are not allowed to edit that team")
    {
        if (!$player->canEdit($team)) {
            throw new ForbiddenException($message);
        }
    }
}
