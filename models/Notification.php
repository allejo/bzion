<?php
/**
 * This file contains functionality to keep track of notifications for players and communicate with external push services
 *
 * @package    BZiON\Models
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

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
     * The text of the notification
     * @var string
     */
    protected $message;

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
     * {@inheritDoc}
     */
    protected function assignResult($notification)
    {
        $this->receiver  = $notification['receiver'];
        $this->message   = $notification['message'];
        $this->status    = $notification['status'];
        $this->timestamp = new DateTime($notification['timestamp']);
    }

    /**
     * Enter a new notification into the database
     * @param  int          $receiver  The receiver's ID
     * @param  string       $content   The content of the notification
     * @param  string       $timestamp The timestamp of the notification
     * @param  string       $status    The status of the notification (unread, read, deleted)
     * @return Notification An object representing the notification that was just entered
     */
    public static function newNotification($receiver, $content, $timestamp = "now", $status = "unread")
    {
        $notification = self::create(array(
            "receiver"  => $receiver,
            "message"   => $content,
            "timestamp" => $timestamp,
            "status"    => $status
        ), 'isss');

        $notification->push();

        return $notification;
    }

    /**
     * Show a list of notifications for a specific user
     * @param  int            $receiver   The ID of the recipient of the notifications
     * @param  bool           $onlyUnread False to show both unread & read notifications
     * @return Notification[]
     */
    public static function getNotifications($receiver, $onlyUnread=false)
    {
        $statuses = array('unread');
        if (!$onlyUnread)
            $statuses[] = 'read';

        return self::arrayIdToModel(self::fetchIdsFrom('status', $statuses, 's'));
    }

    /**
     * Show the number of notifications the user hasn't read yet
     * @param  int $id The ID of the user
     * @return int
     */
    public static function countUnreadNotifications($receiver)
    {
        $db = Database::getInstance();
        $table = static::TABLE;

        $result = $db->query(
            "SELECT COUNT(*) FROM $table WHERE receiver = ? AND status = 'unread'",
            'i', $receiver);

        return $result[0]['COUNT(*)'];
    }

    /**
     * Get the receiving player of the notification
     * @return Player
     */
    public function getReceiver()
    {
        return new Player($this->receiver);
    }

    /**
     * Get the content of the notification
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
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
        return ($this->status != "unread");
    }

    /**
     * Mark the notification as read by the user
     * @return void
     */
    public function markAsRead()
    {
        if ($this->status == "deleted")
            return;

        $this->update('status', $this->status = "read", 's');
    }

    /**
     * Make sure that the user is shown the notification immediately if they are
     * browsing
     */
    private function push()
    {
        foreach (self::$adapters as $adapter) {
            $adapter->trigger('main', $this->message);
        }
    }

    /**
     * Push an event to the event adapters
     * @param  string $type The type of the event
     * @param  mixed  $data The data for the event
     * @return void
     */
    public static function pushEvent($type, $data)
    {
        switch ($type) {
        case 'message':
            $message = array(
                'discussion' => $data->getGroup()->getId(),
                'author'     => $data->getAuthor()->getId(),
            );
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

        $adapters = array('PusherAdapter', 'WebSocketAdapter');

        foreach ($adapters as $adapter) {
            if ($adapter::isEnabled()) {
                self::$adapters[] = new $adapter;
            }
        }
    }
}
