<?php
/**
 * This file contains a class to quickly generate database queries for message
 *
 * @package    BZiON\Models\QueryBuilder
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * This class can be used to search for messages with specific characteristics
 * in the database.
 *
 * @package    BZiON\Models\QueryBuilder
 */
class MessageQueryBuilder extends QueryBuilder
{
    /**
     * A QueryBuilder for group events
     * @var QueryBuilder
     */
    private $eventQuery;

    /**
     * Whether the query was specified to end at a specific Message
     * @var boolean
     */
    private $end;

    /**
     * {@inheritDoc}
     */
    public function __construct($type, $options=array())
    {
        $this->eventQuery = GroupEvent::getQueryBuilder();

        parent::__construct($type, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function active()
    {
        $this->eventQuery->active();

        return parent::active();
    }

    /**
     * {@inheritDoc}
     */
    public function startAt($model, $inclusive=false, $reverse=false)
    {
        if ($reverse) {
            if (!$model) {
                $this->end = false;
            } elseif ($model instanceof Model && !$model->isValid()) {
                $this->end = false;
            } else {
                $this->end = true;
            }
        }

        return parent::startAt($model, $inclusive, $reverse);
    }

    /**
     * {@inheritDoc}
     */
    public function is($number)
    {
        if ($this->currentColumnRaw === $this->columns['group']) {
            $this->eventQuery->where('group')->is($number);
        }

        return parent::is($number);
    }

    /**
     * Only return messages that are sent from/to a specific player
     *
     * @param  Player $player The player related to the messages
     * @return self
     */
    public function forPlayer($player)
    {
        $this->extras .= '
            LEFT JOIN `groups` ON groups.id = messages.group_to
            LEFT JOIN `player_groups` ON player_groups.group=groups.id
        ';

        $this->column('player_groups.player')->is($player);
        $this->column('groups.status')->isOneOf(Group::getActiveStatuses());

        return $this;
    }

    /**
     * Locate messages that contain keywords in a search string
     *
     * @param  string $query The search query
     * @return self
     */
    public function search($query)
    {
        $keywords = preg_split('/\s+/', trim($query));

        $query = "";

        $first = true;
        foreach ($keywords as $keyword) {
            if (!$first) {
                $query .= ' AND ';
            } else {
                $first = false;
            }

            $query .= "(message LIKE CONCAT('%', ?, '%'))";
            $this->parameters[] = $keyword;
            $this->types .= 's';
        }

        $this->conditions[] = $this->rawConditions[] = $query;
        return $this;
    }

    /**
     * Get Messages and GroupEvents
     *
     * This method requires that you use the MessageQueryBuilder::startAt()
     * and MessageQueryBuilder::endAt() methods for pagination
     *
     * @return array
     */
    public function getAllEvents()
    {
        // Get one extra message that can be used later to find out when to stop
        // fetching group events and will be popped later so that the correct
        // result set is returned
        $this->resultsPerPage++;
        $messages = $this->getModels();

        $events = array();

        if (empty($messages)) {
            if (!$this->end) {
                // There are no messages in the discussion - just return all
                // the events
                $events = $this->eventQuery->getModels();
            }
        } else {
            $events = $this->eventQuery;
            $newest = $messages[0];

            // Pop the added element unless it's the last one in the discussion
            if (count($messages) == $this->resultsPerPage) {
                $oldest = array_pop($messages);
                $events->where('time')->isAfter($oldest->getTimestamp());
            }

            // Only show events that have occured after the message if that
            // message is the last one in the discussion
            if ($this->end) {
                $events->where('time')->isBefore($newest->getTimestamp(), true) ;
            }

            $events = $events->getModels();
        }

        // Merge and sort events and messages
        $results = array_merge($messages, $events);
        usort($results, function(GroupEventInterface $a, GroupEventInterface $b) {
            $timeA = $a->getTimestamp();
            $timeB = $b->getTimestamp();

            if ($timeA == $timeB) {
                return 0;
            }
            return ($timeA > $timeB) ? -1 : 1;
        });

        return array(
            'events' => $results,
            'messages' => $messages
        );
    }
}
