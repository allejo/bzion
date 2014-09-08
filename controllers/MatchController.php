<?php

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class MatchController extends CRUDController
{
    public function listAction(Request $request, Team $team=null, $type=null)
    {
        $query = $this->getQueryBuilder()
               ->sortBy('time')->reverse()
               ->with($team, $type)
               ->limit(50)->fromPage($request->query->get('page', 1));

        return array("matches" => $query->getModels(), "team" => $team);
    }

    public function createAction(Player $me)
    {
        return $this->create($me);
    }

    public function deleteAction(Player $me, Match $match)
    {
        if (!$match->getTeamA()->isLastMatch($match)
         || !$match->getTeamB()->isLastMatch($match)) {
            throw new BadRequestException("You can only delete the last match of a team");
        }

        return $this->delete($match, $me);
    }

    /**
     * {@inheritDoc}
     */
    protected function getMessages($type, $name='')
    {
        $messages = parent::getMessages($type, $name);

        // Don't show the match info on the successful create/edit message
        foreach($messages as &$action) {
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

        if (!$firstTeam || !$secondTeam)
            return;

        if ($firstTeam->getId() == $secondTeam->getId()) {
            $message = "You can't report a match where a team played against itself!";
            $form->addError(new FormError($message));
        }
    }
}
