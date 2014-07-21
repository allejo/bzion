<?php
/**
 * This symfony command runs the websocket server for real-time notifications
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Command;

use BZIon\Session\DatabaseSessionHandler;
use BZIon\Socket\EventPusher;
use Ratchet\Session\SessionProvider;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bzion:server')
            ->setDescription('Run notification server')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pusher = new EventPusher();
        $loop = EventLoopFactory::create();

        $pullSocket = new Server($loop);
        $pullSocket->on('connection', function ($conn) use($pusher) {
            $conn->on('data', function ($data) use($pusher) {
                $pusher->onServerEvent(json_decode($data));
            });
        });

        // Bind to 127.0.0.1, so that only the server can send messages to the socket
        $pullSocket->listen(WEBSOCKET_PULL_PORT, '127.0.0.1');

        $session = new SessionProvider(
                $pusher,
                new DatabaseSessionHandler()
            );

        $pushSocket = new Server($loop);

        $webServer = new IoServer(
            new WsServer(
                $session
            ),
            $pushSocket
        );

        // Binding to 0.0.0.0 means remotes can connect
        $pushSocket->listen(WEBSOCKET_PUSH_PORT, '0.0.0.0');

        $loop->run();
    }
}
