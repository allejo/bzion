<?php
/**
 * This file contains functionality to keep track of notifications for players and communicate with external push services
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

use BZIon\Event\Event;
use BZIon\Event\Events;

/**
 * A notification to a player
 * @package    BZiON\Models
 */
class Notification extends Model
{
    /**
     * The id of the notified player
     * @var int
     */
    protected $receiver;

    /**
     * The type of the notification
     *
     * Can be one of the constants in BZIon\Event\Events
     *
     * @var int
     */
    protected $type;

    /**
     * The event of the notification
     * @var Event
     */
    protected $event;

    /**
     * The status of the notification (unread, read, deleted)
     * @var string
     */
    protected $status;

    /**
     * When the notification was sent
     * @var DateTime
     */
    protected $timestamp;

    /**
     * Services that will be notified when a new notification is created
     * @var NotificationAdapter[]
     */
    private static $adapters = array();

    /**
     * The name of the database table used for queries
     */
    const TABLE = "notifications";

    /**
     * {@inheritdoc}
     */
    protected function assignResult($notification)
    {
        $this->receiver  = $notification['receiver'];
        $this->type      = $notification['type'];
        $this->event     = unserialize($notification['event']);
        $this->status    = $notification['status'];
        $this->timestamp = TimeDate::fromMysql($notification['timestamp']);
    }

    /**
     * Enter a new notification into the database
     * @param  int          $receiver  The receiver's ID
     * @param  string       $type      The type of the notification
     * @param  Event        $event     The event of the notification
     * @param  string       $timestamp The timestamp of the notification
     * @param  string       $status    The status of the notification (unread, read, deleted)
     * @return Notification An object representing the notification that was just entered
     */
    public static function newNotification($receiver, $type, $event, $timestamp = "now", $status = "unread")
    {
        $notification = self::create(array(
            "receiver"  => $receiver,
            "type"      => $type,
            "event"     => serialize($event),
            "timestamp" => TimeDate::from($timestamp)->toMysql(),
            "status"    => $status
        ), 'issss');

        return $notification;
    }

    /**
     * Show a list of notifications for a specific user
     * @param  int            $receiver   The ID of the recipient of the notifications
     * @param  bool           $onlyUnread False to show both unread & read notifications
     * @return Notification[]
     */
    public static function getNotifications($receiver, $onlyUnread = false)
    {
        $statuses = array('unread');
        if (!$onlyUnread) {
            $statuses[] = 'read';
        }

        return self::getQueryBuilder()
            ->where('status')->isOneOf($statuses)
            ->where('receiver')->is($receiver)
            ->getModels();
    }

    /**
     * Show the number of notifications the user hasn't read yet
     * @param  int $receiver
     * @return int
     */
    public static function countUnreadNotifications($receiver)
    {
        return self::fetchCount("WHERE receiver = ? AND status = 'unread'",
            'i', $receiver
        );
    }

    /**
     * Get the receiving player of the notification
     * @return Player
     */
    public function getReceiver()
    {
        return Player::get($this->receiver);
    }
    /**
     * Get the type of the notification
     *
     * Do not use Notification::getType(), as it returns the name of the class
     * (i.e. notification)
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->type;
    }

    /**
     * Get the event of the notification
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get the time when the notification was sent
     * @return TimeDate
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Finds if the notification has been read by the user
     *
     * This returns true even if the notification is deleted
     * @return bool
     */
    public function isRead()
    {
        return $this->status != "unread";
    }

    /**
     * Mark the notification as read by the user
     * @return void
     */
    public function markAsRead()
    {
        if ($this->status == "deleted") {
            return;
        }

        $this->update('status', $this->status = "read", 's');
    }

    /**
     * Make sure that the user is shown the notification immediately if they are
     * browsing
     */
    public function push()
    {
        self::pushEvent('notification', $this);
    }

    /**
     * Get the available actions for the notification
     *
     * @return array
     */
    public function getActions($email = false)
    {
        switch ($this->type) {
            case Events::TEAM_INVITE:
                return array(
                    ($email) ? 'Accept invitation' : 'Accept' => $this->event->getInvitation()->getUrl('accept', $email)
                );
            default:
                return array();
        }
    }

    /**
     * Push an event to the event adapters
     * @param  string $type The type of the event
     * @param  mixed  $data The data for the event
     * @return void
     */
    public static function pushEvent($type, $data = null)
    {
        switch ($type) {
            case 'message':
                $message = array(
                    'conversation' => $data['message']->getConversation()->getId(),
                    'message'      => $data['message']->getId(),
                    'author'       => $data['message']->getAuthor()->getId(),
                    'recipients'   => $data['recipients']
                );
                break;
            case 'notification':
                $message = array(
                    'type'         => $data->getType(),
                    'receiver'     => $data->getReceiver()->getId(),
                    'notification' => $data->getId()
                );
                break;
            case 'blank':
                $message = null;
                break;
            default:
                $message = $data;
        }

        foreach (self::$adapters as $adapter) {
            $adapter->trigger($type, $message);
        }
    }

    /**
     * Initialize the external push adapters
     * @return void
     */
    public static function initializeAdapters()
    {
        if (self::$adapters) {
            // The adapters have already been initialized, no need to do anything!
            return;
        }

        $adapters = array(
            'BZIon\NotificationAdapter\WebSocketAdapter'
        );

        foreach ($adapters as $adapter) {
            if ($adapter::isEnabled()) {
                self::$adapters[] = new $adapter();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getActiveStatuses()
    {
        return array('read', 'unread');
    }

    /**
     * Get a query builder for notifications
     * @return NotificationQueryBuilder
     */
    public static function getQueryBuilder()
    {
        return new NotificationQueryBuilder('Notification', array(
            'columns' => array(
                'receiver'  => 'receiver',
                'timestamp' => 'timestamp',
                'status'    => 'status'
            )
        ));
    }
}
