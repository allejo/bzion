<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../bzion-load.php";

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $kernel;
    private $response;

    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->kernel = new AppKernel(AppKernel::guessEnvironment(), DEVELOPMENT > 0);
    }

    /**
     * @Given /^I have entered a news article named "([^"]*)"$/
     */
    public function iHaveEnteredANewsArticleNamed($title)
    {
        News::addNews($title, "bleep", 1);
    }

    /**
     * @Given /^I have a custom page named "([^"]*)"$/
     */
    public function iHaveACustomPageNamed($arg1)
    {
        Page::addPage($arg1, "blop", 1);
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
}
