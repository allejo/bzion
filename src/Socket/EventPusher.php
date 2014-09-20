<?php
namespace BZIon\Socket;

use Player;
use BZIon\Event\EventSubscriber;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class EventPusher implements MessageComponentInterface
{
    /**
     * The connected clients
     * @var \SplObjectStorage
     */
    protected $clients;

    /**
     * The event subscriber
     * @var EventSubscriber
     */
    protected $subscriber;

    /**
     * Create a new event pusher handler
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->subscriber = \Service::getContainer()->get('kernel.subscriber.bzion_subscriber');
    }

    /**
     * Open the connection
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Find which player opened the connection
        $conn->Player = new Player($conn->Session->get('playerId'));

        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    /**
     * Send a message as a client
     * @param ConnectionInterface $from
     * @param mixed               $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        // The client isn't supposed to send messages -
        // probably a user is messing with the console, just close the connection
        $from->close();
    }

    /**
     * Close a connection
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    /**
     * Action to call on an error
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Action to call when the server notifies us about something
     * @param string $event JSON'ified string we'll receive from the webserver
     */
    public function onServerEvent($event)
    {
        $event = $event->event;

        // A list of players who received a message so that we can e-mail the
        // ones who didn't
        $received = array();

        if ($event->type == 'message') {
            $group = new \Group($event->data->discussion);

            // Don't notify the sender of the message, Javascript will
            // automatically refresh the page
            $groupMembers = $group->getMemberIds($event->data->author);
        }

        foreach ($this->clients as $client) {
            $player = $client->Player;

            if ($event->type == 'message') {
                if (!in_array($player->getId(), $groupMembers)) {
                    // Don't notify that player, he doesn't belong in the group
                    continue;
                }
            }

            $event->notification_count = $player->countUnreadNotifications();
            $event->message_count      = $player->countUnreadMessages();

            $client->send(json_encode(array('event' => $event)));
            $received[] = $player->getId();
        }

        // Send e-mails
        if ($event->type == 'message') {
            foreach ($event->data->recipients as $recipient) {
                $recipient = new Player($recipient);

                // Only send an email to users who aren't currently logged in
                if ($recipient->isVerified() && !in_array($recipient->getId(), $received)) {
                    $this->subscriber->sendEmails(
                        'New message received',
                        array($recipient->getId()),
                        'message',
                        array('message' => new \Message($event->data->message))
                    );
                }
            }
        }
    }
}
