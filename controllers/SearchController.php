<?php

use BZIon\Form\Creator\PlayerAdminNotesFormCreator as FormCreator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends JSONController
{
    /**
     * A list of types that can be returned when searching
     *
     * @var array
     */
    private static $acceptableTypes = array('player', 'team');

    public function searchAction(Request $request, Player $me)
    {
        if (!$request->query->has('types')) {
            throw new \BadRequestException();
        }

        $types = explode(',', $request->query->get('types'));

        $queries = $results = array();

        foreach ($types as $type) {
            if (!in_array($type, self::$acceptableTypes)) {
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
            $query->except($this->getMe());
        }

        return $query->getArray(array('name', 'outdated'));
    }
}
