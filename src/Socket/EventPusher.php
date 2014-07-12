<?php
namespace BZIon\Socket;
use Player;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class EventPusher implements MessageComponentInterface {
    /**
     * The connected clients
     * @var \SplObjectStorage
     */
    protected $clients;

    /**
     * Create a new event pusher handler
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * Open the connection
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn) {
        // Find which player opened the connection
        $conn->Player = new Player($conn->Session->get('playerId'));

        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    /**
     * Send a message as a client
     * @param ConnectionInterface $from
     * @param mixed $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // The client isn't supposed to send messages -
        // probably a user is messing with the console, just close the connection
        $from->close();
    }

    /**
     * Close a connection
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    /**
     * Action to call on an error
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Action to call when the server notifies us about something
     * @param string $event JSON'ified string we'll receive from ZeroMQ
     */
    public function onServerEvent($event) {
        $event = $event->event;

        switch($event->type) {
        case 'global_notification':
            $message = $event;
            break;
        default:
            return;
        }

        foreach ($this->clients as $client) {
            $player = $client->Player;

            $message->notification_count = $player->countUnreadNotifications();
            $message->message_count      = $player->countUnreadMessages();

            $client->send(json_encode(array('event' => $message)));
        }
    }
}
