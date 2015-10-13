<?php

use BZIon\Form\Creator\PlayerAdminNotesFormCreator as FormCreator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends JSONController
{
    const ACCEPTABLE_TYPES = array('player', 'team');

    public function searchAction(Request $request, Player $me)
    {
        if (!$request->query->has('types')) {
            throw new \BadRequestException();
        }

        $types = explode(',', $request->query->get('types'));

        $queries = $results = array();

        foreach ($types as $type) {
            if (!in_array($type, self::ACCEPTABLE_TYPES)) {
                throw new \BadRequestException("An invalid type was provided");
            }

            $class = ucfirst($type);

            $queries[$type] = $class::getQueryBuilder();
        }

        foreach ($queries as $type => $query) {
            if ($startsWith = $request->query->get('startsWith')) {
                $query->where('name')->startsWith($startsWith);
            }

            $query->active()->sortBy('name');

            if ($type === 'player') {
                $result = $this->playerQuery($query, $request);
            } else {
                $result = $query->getArray(array('name'));
            }

            foreach ($result as $model) {
                $model['type'] = $type;
                $results[] = $model;
            }
        }

        return new JsonResponse(array(
            'results' => $results
        ));
    }

    private function playerQuery(\QueryBuilder $query, Request $request)
    {
        if ($team = $request->query->get('team')) {
            $query->where('team')->is($team);
        }

        if ($request->query->has('exceptMe')) {
            $query->except($me);
        }

        return $query->getArray(array('name', 'outdated'));
    }
}
