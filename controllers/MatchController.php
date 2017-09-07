<?php

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MatchController extends CRUDController
{
    /**
     * Whether the last edited match has had its ELO changed, requiring an ELO
     * recalculation
     *
     * This is useful so that a confirmation form is shown, asking the user if
     * they want to recalculate ELOs
     *
     * @var bool
     */
    public $recalculateNeeded = false;

    public function listAction(Request $request, Player $me, Team $team = null, Player $player = null, $type = null)
    {
        $qb = $this->getQueryBuilder();

        $currentPage = $request->query->get('page', 1);

        if ($player) {
            $team = $player;
        }

        $query = $qb->sortBy('time')->reverse()
               ->with($team, $type)
               ->limit(50)->fromPage($currentPage);

        $matchType = $request->query->get('type', 'all');

        if (in_array($matchType, array(Match::FUN, Match::OFFICIAL, Match::SPECIAL))) {
            $query->where('type')->is($matchType);
        }

        $matches = $query->getModels($fast = true);

        foreach ($matches as $match) {
            // Don't show wrong labels for matches
            $match->getOriginalTimestamp()->setTimezone($me->getTimezone());
        }

        return array(
            "matches"     => $matches,
            "team"        => $team,
            "currentPage" => $currentPage,
            "totalPages"  => $qb->countPages()
        );
    }

    public function showAction(Match $match)
    {
        return array("match" => $match);
    }

    public function createAction(Player $me)
    {
        return $this->create($me, function (Match $match) use ($me) {
            if ($me->canEdit($match)
                && $match->isOfficial()
                && (!$match->getTeamA()->isLastMatch($match)
                || !$match->getTeamB()->isLastMatch($match))
            ) {
                $url = Service::getGenerator()->generate('match_recalculate', array(
                    'match' => $match->getId(),
                ));

                return new RedirectResponse($url);
            }
        });
    }

    public function deleteAction(Player $me, Match $match)
    {
        return $this->delete($match, $me, function () use ($match, $me) {
            if ($match->getTeamA()->isLastMatch($match)
                && $match->getTeamB()->isLastMatch($match)) {
                $match->resetELOs();
            } elseif ($me->canEdit($match)) {
                $url = Service::getGenerator()->generate('match_recalculate', array(
                    'match' => $match->getId(),
                ));

                return new RedirectResponse($url);
            }
        });
    }

    public function editAction(Player $me, Match $match)
    {
        // TODO: Generating this response is unnecessary
        $response = $this->edit($match, $me, "match");

        if ($this->recalculateNeeded && $match->isOfficial()) {
            // Redirect to a confirmation form if we are assigning a new leader
            $url = Service::getGenerator()->generate('match_recalculate', array(
                'match' => $match->getId(),
            ));

            return new RedirectResponse($url);
        }

        return $response;
    }

    public function recalculateAction(Player $me, $match)
    {
        $match = Match::get($match); // get a match even if it's deleted

        if (!$me->canEdit($match)) {
            throw new ForbiddenException("You are not allowed to edit that match.");
        }

        if (!$match->isOfficial()) {
            throw new BadRequestException("You can't recalculate ELO history for a special match.");
        }

        return $this->showConfirmationForm(function () use ($match) {
            $response = new StreamedResponse();
            $response->headers->set('Content-Type', 'text/plain');
            $response->setCallback(function () use ($match) {
                $this->log(Match::recalculateMatchesSince($match));
            });
            $response->send();
        }, "Do you want to recalculate ELO history for all teams and matches after the specified match?",
            "ELO history recalculated",
            "Recalculate ELOs",
            function () use ($match) {
                if ($match->isDeleted()) {
                    return new RedirectResponse($match->getURL('list'));
                }

                return new RedirectResponse($match->getURL('show'));
            },
            "Match/recalculate.html.twig",
            $noButton = true
        );
    }

    /**
     * Echo a string and flush the buffers
     *
     * Useful for streamed AJAX responses
     *
     * @param string $string The string to echo
     */
    private function log($string)
    {
        echo $string;
        ob_flush();
        flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessages($type, $name = '')
    {
        $messages = parent::getMessages($type, $name);

        // Don't show the match info on the successful create/edit message
        foreach ($messages as &$action) {
            foreach ($action as &$status) {
                if (isset($status['named'])) {
                    $status['named'] = $status['unnamed'];
                }
            }
        }

        return $messages;
    }

    protected function validate($form)
    {
        // Make sure that two different teams participated in a match, i.e. a team
        // didn't match against itself
        $firstTeam  = $form->get('first_team')->get('team')->getData();
        $secondTeam = $form->get('second_team')->get('team')->getData();

        if (!$firstTeam || !$secondTeam) {
            return;
        }

        if ($firstTeam->isSameAs($secondTeam)) {
            $message = "You can't report a match where a team played against itself!";
            $form->addError(new FormError($message));
        }

        $matchType = $form->get('type')->getData();

        foreach (array('first_team', 'second_team') as $team) {
            $input = $form->get($team);
            $teamInput = $input->get('team');
            $teamParticipants = $input->get('participants');

            if ($matchType === Match::FUN) {
                if (!$teamInput->getData() instanceof ColorTeam) {
                    $message = "Please enter a team color for fun and special matches.";
                    $teamInput->addError(new FormError($message));
                }
            } elseif ($matchType === Match::OFFICIAL) {
                if ($teamInput->getData() instanceof ColorTeam) {
                    $participants = $teamParticipants->getData();

                    if (empty($participants)) {
                        $message = 'A player roster is necessary for a color team for a mixed official match.';
                        $teamInput->addError(new FormError($message));
                    }
                }
            }
        }
    }

    protected function validateEdit($form, $match)
    {
        if ($match->isOfficial() && $form->get('type')->getData() !== Match::OFFICIAL) {
            $message = "You cannot change this match's type.";
            $form->get('type')->addError(new FormError($message));
        } elseif (!$match->isOfficial() && $form->get('type')->getData() === Match::OFFICIAL) {
            $message = "You can't make this an official match.";
            $form->get('type')->addError(new FormError($message));
        }
    }
}
