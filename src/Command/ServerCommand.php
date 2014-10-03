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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bzion:server')
            ->setDescription('Run notification server')
            ->addOption(
                'push',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The push port'
            )
            ->addOption(
                'pull',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The pull port'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = EventLoopFactory::create();
        $pusher = new EventPusher();

        $pushPort = ($input->getOption('push')) ?: $this->getContainer()
            ->getParameter('bzion.notifications.websocket.push_port');

        $pullPort = ($input->getOption('pull')) ?: $this->getContainer()
            ->getParameter('bzion.notifications.websocket.pull_port');

        $pullSocket = new Server($loop);
        $pullSocket->on('connection', function ($conn) use ($pusher) {
            $conn->on('data', function ($data) use ($pusher) {
                $pusher->onServerEvent(json_decode($data));
            });
        });

        // Bind to 127.0.0.1, so that only the server can send messages to the socket
        $pullSocket->listen($pullPort, '127.0.0.1');
        $output->writeln(" <fg=green>Running pull service on port $pullPort</>");

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
        $pushSocket->listen($pushPort, '0.0.0.0');
        $output->writeln(" <fg=green>Running push service on port $pushPort</>");

        $output->writeln("\n <bg=green;options=bold>Welcome to the BZiON live notification server!</>");
        $loop->run();
    }
}
