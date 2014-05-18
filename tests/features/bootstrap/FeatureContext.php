<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit_Framework_Assert as Assert;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;
    private $client = null;
    private $response = null;
    private $crawler = null;
    private $player = null;
    private $genericPlayer;

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        $this->kernel->boot();
        $this->client = $this->kernel->getContainer()->get('test.client');
    }

    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->genericPlayer = $this->getNewUser("admin", Player::DEVELOPER);
    }

    protected function getNewUser($username="Sam", $role=Player::PLAYER)
    {
        // Try to find a valid bzid
        $bzid = 300;
        while (Player::getFromBZID($bzid)->isValid()) {
            $bzid++;

            if ($bzid > 15000)
                throw new Exception("bzid too big");
        }

        return Player::newPlayer($bzid, $username, null, "test", $role);
    }

    /**
     * @Given /^I have entered a news article named "([^"]*)"$/
     */
    public function iHaveEnteredANewsArticleNamed($title)
    {
        News::addNews($title, "bleep", $this->genericPlayer->getId());
    }

    /**
     * @Given /^I have a custom page named "([^"]*)"$/
     */
    public function iHaveACustomPageNamed($arg1)
    {
        Page::addPage($arg1, "blop", $this->genericPlayer->getId());
    }

    /**
     * @Given I have a user
     */
    public function iHaveAUser()
    {
        $this->player = $this->getNewUser();
    }

    /**
     * @When I log in
     */
    public function iLogIn()
    {
        $this->visit('/login/' . $this->player->getId());

    }
}
