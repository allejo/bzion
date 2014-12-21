<?php

use Behat\Behat\ApplicationFactory as BehatFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class BehatTest extends TestCase
{
    /**
     * @group behat
     */
    public function testThatBehatScenariosMeetAcceptanceCriteria()
    {
        try {
            $factory = new BehatFactory();

            $arguments = array('-c' => 'tests/behat.yml');
            $input = new ArrayInput($arguments);
            $application = $factory->createApplication();

            $this->assertEquals(0, $application->doRun($input, new ConsoleOutput()));
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
