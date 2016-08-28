<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

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

    public function searchAction(Request $request)
    {
        if (!$request->query->has('types')) {
            throw new \BadRequestException();
        }

        $types = explode(',', $request->query->get('types'));
        $excludeQuery = $request->query->get('exclude');

        $queries = $results = array();

        foreach ($types as &$type) {
            if (!in_array($type, self::$acceptableTypes)) {
                throw new \BadRequestException("An invalid type was provided");
            }

            $type = ucfirst($type);
            $queries[$type] = $type::getQueryBuilder();
        }

        $excluded = $this->decompose($excludeQuery, $types, false, self::MAX_EXCLUDED_OBJECTS);

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

    public function playerByBzidAction(Player $me, Request $request, FlashBag $flashBag, $bzid = null)
    {
        if (!$me->hasPermission(Permission::VIEW_VISITOR_LOG)) {
            throw new ForbiddenException();
        }

        if ($bzid === null) {
            if (!$request->query->has('bzid')) {
                throw new BadRequestException("Please provide the BZID to search for");
            }

            $bzid = $request->query->get('bzid');
        }

        $player = Player::getFromBZID($bzid);

        if (!$player->isValid()) {
            $flashBag->add('error', "Player with BZID $bzid not found");

            return $this->goBack();
        }

        return new RedirectResponse($player->getURL());
    }
}
