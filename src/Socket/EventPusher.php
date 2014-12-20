<?php
namespace BZIon\Socket;

use Player;
use BZIon\Event\EventSubscriber;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * The event loop
     * @var LoopInterface
     */
    protected $loop;

    /**
     * The console output
     * @var OutputInterface|null
     */
    protected $output;

    /**
     * Max pong time and interval between pings in seconds
     * @var integer
     */
    const KEEP_ALIVE = 300;

    /**
     * Create a new event pusher handler
     */
    public function __construct(LoopInterface $loop, OutputInterface $output = null)
    {
        $this->loop = $loop;
        $this->output = $output;

        $this->clients = new \SplObjectStorage;
        $this->subscriber = \Service::getContainer()->get('kernel.subscriber.bzion_subscriber');

        // Ping timer
        $loop->addPeriodicTimer(self::KEEP_ALIVE, array($this, 'ping'));
    }

    /**
     * Open the connection
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Find which player opened the connection
        $conn->Player = new Player($conn->Session->get('playerId'));

        $conn->pong = true;

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->log(
            sprintf(
                "<fg=cyan>Client #{$conn->resourceId} connected from {$conn->remoteAddress}\t ({$conn->Player->getUsername()})</>",
                $conn->resourceId,
                $conn->remoteAddress,
                $conn->Player->getUsername()
            ), OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Send a message as a client
     * @param ConnectionInterface $from
     * @param mixed               $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->log("Received message from #{$from->resourceId}");

        // Record the reception of the message to prevent a ping timeout
        $from->pong = true;
    }

    /**
     * Close a connection
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $this->log(
            sprintf(
                "<fg=yellow>Client #{$conn->resourceId} disconnected from {$conn->remoteAddress}\t ({$conn->Player->getUsername()})</>",
                $conn->resourceId,
                $conn->remoteAddress,
                $conn->Player->getUsername()
            ), OutputInterface::VERBOSITY_VERBOSE
        );
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
     * Pushes or emails a new private message to the user
     *
     * @param array $event The event data we received from the web server
     */
    private function onMessageServerEvent($event)
    {
        // A list of players who received a message so that we can e-mail the
        // ones who didn't
        $received = array();

        $group = new \Group($event->data->discussion);

        $groupMembers = $group->getMemberIds();

        foreach ($this->clients as $client) {
            $player = $client->Player;

            if (!in_array($player->getId(), $groupMembers)) {
                // Don't notify that player, he doesn't belong in the group
                continue;
            }

            $event->notification_count = $player->countUnreadNotifications();
            $event->message_count      = $player->countUnreadMessages();

            $this->send($client, $event);
            $received[] = $player->getId();
        }

        // Send e-mails
        foreach ($event->data->recipients as $recipient) {
            // Only send an email to users who aren't currently logged in
            if (!in_array($recipient, $received)) {
                $this->log("<fg=green>E-mailing player {$recipient->getId()} ({$recipient->getUsername()})</>");

                $this->subscriber->sendEmails(
                    'New message received',
                    array($recipient),
                    'message',
                    array('message' => new \Message($event->data->message))
                );
            }
        }
    }

    /**
     * Pushes or emails a new notification to the user
     *
     * @param array $event The event data we received from the web server
     */
    private function onNotificationServerEvent($event)
    {
        $notification = new \Notification($event->data->notification);

        // Whether we've notified that player in real time - if he isn't online
        // at the moment, we'll send an e-mail to him
        $active = false;

        foreach ($this->clients as $client) {
            if ($client->Player->getId() == $event->data->receiver) {
                $this->send($client, $event);
                $active = true;
            }
        }

        if (!$active) {
            $player = $notification->getReceiver();
            $this->log("<fg=green>E-mailing player {$player->getId()} ({$player->getUsername()})</>");

            $this->subscriber->emailNotification($notification);
        }
    }

    /**
     * Send some data to the client
     *
     * @param ConnectionInterface $client The client that will receive the data
     * @param array               $data   The data to send
     */
    protected function send(ConnectionInterface $client, $data)
    {
        $this->log("<fg=green>Notifying #{$client->resourceId} ({$client->Player->getUsername()})</>");

        $data->notification_count = $client->Player->countUnreadNotifications();
        $data->message_count      = $client->Player->countUnreadMessages();

        $client->send(json_encode(array('event' => $data)));
    }

    /**
     * Action to call when the server notifies us about something
     * @param string $event JSON'ified string we'll receive from the webserver
     */
    public function onServerEvent($event)
    {
        $event = $event->event;

        switch ($event->type) {
            case 'message':
                $this->log("New message received", OutputInterface::VERBOSITY_VERY_VERBOSE);
                $this->onMessageServerEvent($event);
                break;
            case 'notification':
                $this->log("New notification received", OutputInterface::VERBOSITY_VERY_VERBOSE);
                $this->onNotificationServerEvent($event);
                break;
            default:
                $this->log("Generic message received", OutputInterface::VERBOSITY_VERY_VERBOSE);
                foreach ($this->clients as $client) {
                    $this->send($client, $event);
                }
        }
    }

    /**
     * Log a debugging message to the console
     *
     * @param string $message The message to log
     * @param int    $level   The output verbosity level of the message
     */
    private function log($message, $level = OutputInterface::VERBOSITY_DEBUG)
    {
        if (!$this->output) {
            return;
        }

        if ($level <= $this->output->getVerbosity()) {
            $this->output->writeln($message);
        }
    }

    /**
     * Send a ping message to all clients and kick those who didn't respond
     */
    public function ping()
    {
        $this->log("Sending pings");

        foreach($this->clients as $client) {
            if (!$client->pong) {
                $this->log("Dropping #{$client->resourceId}");

                $client->close();
                continue;
            }

            $this->log("Pinging #{$client->resourceId}");
            $client->send('ping');
            $client->pong = false;
        }
    }
}
