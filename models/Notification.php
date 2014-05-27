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
    private $receiver;

    /**
     * The text of the notification
     * @var string
     */
    private $message;

    /**
     * The status of the notification (unread, read, deleted)
     * @var string
     */
    private $status;

    /**
     * When the notification was sent
     * @var DateTime
     */
    private $timestamp;

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
     * Construct a new Visit
     * @param int $id The visitor's id
     */
    public function __construct($id)
    {
        parent::__construct($id);
        if (!$this->valid) return;

        $notification = $this->result;

        $this->receiver  = $notification['receiver'];
        $this->message   = $notification['message'];
        $this->status    = $notification['status'];
        $this->timestamp = new DateTime($notification['timestamp']);

        $this->initializeAdapters();
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
        $notification = new Notification(self::create(array(
            "receiver"  => $receiver,
            "message"   => $content,
            "timestamp" => $timestamp,
            "status"    => $status
        ), 'isss'));

        $notification->push();

        return $notification;
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
     * Initialize the external push adapters
     * @return void
     */
    private static function initializeAdapters()
    {
        if (self::$adapters) {
            // The adapters have already been initialized, no need to do anything!
            return;
        }

        $adapters = array('PusherAdapter');

        foreach ($adapters as $adapter) {
            if ($adapter::isEnabled()) {
                self::$adapters[] = new $adapter;
            }
        }
    }
}
