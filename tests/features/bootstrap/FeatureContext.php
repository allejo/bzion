<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Exception\PendingException;
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
    private $response;

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
//         $this->kernel = new AppKernel("test", true);
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
