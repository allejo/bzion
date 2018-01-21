<?php
/**
 * This symfony command will recalculate Elo matches
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Command;

use Match;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculateEloCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bzion:recalc-elo')
            ->setDescription('Recalculate Elos for a given match and all of the matches proceeding it.')
            ->addArgument('matchID', InputArgument::REQUIRED, 'The ID of the first match that needs to be recalculated.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('matchID');
        $match = Match::get($id);

        if (!$match->isValid()) {
            $output->writeln(sprintf('No match found with id: %d', $id));
            exit(1);
        }

        $output->writeln(sprintf('Starting Elo recalculation with match #%d...', $id));
        $output->writeln(sprintf('  (this may take a while)'));

        try {
            Match::recalculateMatchesSince($match, function ($event) use ($output) {
                switch ($event['type']) {
                    case 'recalculation.count':
                        $output->writeln(sprintf("\nDetected %d matches needing recalculations\n", $event['value']));
                        $this->initProgress($output, $event['value']);
                        break;

                    case 'recalculation.progress':
                        $this->advance();
                        break;

                    case 'recalculation.complete':
                        $this->finishProgress();
                        break;

                    default:
                        $output->writeln(sprintf('Received unknown recalculation event: %s', $event['type']));
                }
            });
        }
        catch (\Exception $e) {
            $errMsg = $e->getMessage();

            $output->writeln(<<<EXCEPTION
            
<bg=red;options=bold>
 [ERROR] An exception occurred with the following message:
   ${$errMsg}
</>
EXCEPTION
);
            exit(1);
        }

        $output->writeln(<<<SUCCESS

<bg=green;options=bold>

 [OK] Match Elos have been successfully recalculated.
</>
SUCCESS
        );
    }
}
