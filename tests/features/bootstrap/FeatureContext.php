<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;

/**
 * Behat context class.
 */
class FeatureContext implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;
    private $response = null;
    private $player = null;
    private $genericPlayer;

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->genericPlayer = $this->getNewUser();
    }

    protected function getNewUser($username="Sam") {
        // Try to find a valid bzid
        $bzid = 300;
        while (Player::getFromBZID($bzid)->isValid()) {
            $bzid++;

            if ($bzid > 15000)
                throw new Exception("bzid too big");
        }

        return Player::newPlayer($bzid, $username);
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
     * @When /^I go to the home page$/
     */
    public function iGoToTheHomePage()
    {
        $this->response = $this->kernel->handle(Request::create('/'));
    }

    /**
     * @Then /^I should see "([^"]*)"$/
     */
    public function iShouldSee($what)
    {
        if (strpos($this->response->getContent(), $what) === false)
            throw new Exception("Response does not contain $what");
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
        Service::getSession()->set('playerId', $this->player->getId());
    }

    /**
     * @Then I should not see :something
     */
    public function iShouldNotSee($something)
    {
        if (strpos($this->response->getContent(), $something) !== false)
            throw new Exception("Response contains $something");
    }
}
