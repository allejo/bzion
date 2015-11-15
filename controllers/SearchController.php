<?php

use BZIon\Form\Creator\PlayerAdminNotesFormCreator as FormCreator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends JSONController
{
    /**
     * The maximum number of objects to allow being excluded
     *
     * @var int
     */
    const MAX_EXCLUDED_OBJECTS = 10;

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
        $excludeQuery = explode(',', $request->query->get('exclude'));

        $queries = $results = $excluded = array();

        foreach ($types as &$type) {
            if (!in_array($type, self::$acceptableTypes)) {
                throw new \BadRequestException("An invalid type was provided");
            }

            $type = ucfirst($type);

            $excluded[$type] = array();
            $queries[$type] = $type::getQueryBuilder();
        }

        if (count($excludeQuery) > self::MAX_EXCLUDED_OBJECTS) {
            throw new \BadRequestException("Too many excluded objects provided");
        }

        foreach ($excludeQuery as $excludedObject) {
            if ($excludedObject === '') {
                continue;
            }

            $excludedObject = explode(':', $excludedObject, 3);
            if (count($excludedObject) === 2) {
                $class = ucfirst($excludedObject[0]);
                $id = (int) $excludedObject[1];

                if (!in_array($class, $types)) {
                    throw new \BadRequestException("Invalid excluded type");
                }

                $excluded[$class][] = $id;
            } elseif (count($excludedObject) === 1) {
                // No type was provided
                if (count('types') > 1) {
                    throw new \BadRequestException(
                        "You need to provide the type of the excluded object"
                    );
                }

                $excluded[$types[0]][] = (int) $excludedObject[0];
            } else {
                throw new \BadRequestException("Malformed excluded object");
            }


        }

        foreach ($queries as $type => $query) {
            if ($startsWith = $request->query->get('startsWith')) {
                $query->where('name')->startsWith($startsWith);
            }

            $query->active()->sortBy('name');

            foreach ($excluded[$type] as $exclude) {
                $query->except($exclude);
            }

            if ($type === 'Player') {
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

        return $query->getArray(array('name', 'outdated'));
    }
}
