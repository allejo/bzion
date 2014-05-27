<?php

class PusherAdapter extends NotificationAdapter
{
    /**
     * The Pusher instance
     * @var Pusher
     */
    private $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(PUSHER_KEY, PUSHER_SECRET, PUSHER_APP_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($channel, $message)
    {
        $this->pusher->trigger($channel, 'unnamed-event', array ( 'message' => $message));
    }

    /**
     * {@inheritDoc}
     */
    public static function isEnabled()
    {
        if (!parent::isEnabled())
            return false;

        return (bool) ENABLE_PUSHER;
    }
}
