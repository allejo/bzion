<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Behat\Behat\ApplicationFactory as BehatFactory;
use Behat\Testwork\Cli\Command as BehatCommand;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BehatTest extends TestCase
{
    /**
     * @group behat
     */
    public function testThatBehatScenariosMeetAcceptanceCriteria()
    {
        try {
            $factory = new BehatFactory();

            $arguments = array ('-c' => 'tests/behat.yml');
            $input = new ArrayInput($arguments);
            $application = $factory->createApplication();

            $application->doRun($input, new ConsoleOutput());
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
