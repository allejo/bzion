<?php
/**
 * This symfony command only shows a success message
 *
 * @package    BZiON
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SuccessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bzion:success')
            ->setDescription('Show a success message to make sure everything works')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(<<<SUCCESS

<bg=green;options=bold>

 [OK] BZiON has been successfully installed, enjoy!
</>
<comment>
 ! [NOTE] Before using BZiON, make sure that you have properly set
 ! up directory permissions as specified on the README.md file</>
SUCCESS
        );
    }
}
