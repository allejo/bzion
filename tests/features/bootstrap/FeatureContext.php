<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;

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
    private $me = null;

    /**
     * A random avatar URL
     * @var string
     */
    private $avatarUrl = "web/assets/imgs/avatars/players/1.png";

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        $this->kernel->boot();
        $this->client = $this->kernel->getContainer()->get('test.client');
    }

    /**
    * Prepare system for the test suite before it runs
    *
    * @BeforeSuite
    */
    public static function prepare($event)
    {
        self::clearDatabase();
    }

    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
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

    protected function getUserId($username="Sam", $role=Player::PLAYER)
    {
        return $this->getNewUser($username, $role)->getId();
    }

    protected function getAdminId()
    {
        return $this->getNewUser("Administrator", Player::DEVELOPER)->getId();
    }

    /**
     * @Given /^I have entered a news article named "([^"]*)"$/
     */
    public function iHaveEnteredANewsArticleNamed($title)
    {
        News::addNews($title, "bleep", $this->getAdminId());
    }

    /**
     * @Given /^I have a custom page named "([^"]*)"$/
     */
    public function iHaveACustomPageNamed($arg1)
    {
        Page::addPage($arg1, "blop", $this->getAdminId());
    }

    /**
     * @Given I have a user
     */
    public function iHaveAUser()
    {
        $this->me = $this->getNewUser();
    }

    /**
     * @Given I am logged in
     * @When I log in
     */
    public function iLogIn()
    {
        if (!$this->me)
            $this->me = $this->getNewUser();

        $this->visit('/login/' . $this->me->getId());
    }

    /**
     * @Given I am logged out
     * @When I log out
     */
    public function iLogOut()
    {
        $this->visit('/logout');
    }
    /**
     * @Given I have a team called :name
     * @Given there is a team called :name
     */
    public function iHaveATeamCalled($name)
    {
        return Team::createTeam($name, $this->getUserId(), $this->avatarUrl, "Description", "open");
    }

    /**
     * @Then I should see :arg1 in the title
     */
    public function iShouldSeeInTheTitle($arg1)
    {
        $this->assertElementContains("title", $arg1);
    }

    /**
     * @Then should not see :arg1 in the title
     */
    public function iShouldNotSeeInTheTitle($arg1)
    {
        $this->assertElementNotContains("title", $arg1);
    }

    /**
     * @Given :team1 plays a match against :team2 with score :score1 - :score2
     */
    public function playsAMatchAgainstWithScore($team1, $team2, $score1, $score2)
    {
        Match::enterMatch(Team::getFromName($team1)->getId(),
                          Team::getFromName($team2)->getId(),
                          $score1,
                          $score2,
                          30,
                          $this->getUserId());
    }

    /**
     * @Given I am an admin
     */
    public function iAmAnAdmin()
    {
        $this->me = $this->getNewUser("Sam", Player::S_ADMIN);
        $this->iLogIn();
    }

    /**
     * @Given a new user called :user joins :team
     */
    public function aNewUserCalledJoins($user, $team)
    {
        $player = $this->getNewUser($user);
        Team::getFromName($team)->addMember($player->getId());
    }

    /**
     * @Given I am a banned user
     */
    public function iAmABannedUser()
    {
        $this->me = $this->getNewUser("Sammed", Player::PLAYER_NO_PM);
        $this->iLogIn();
    }

    /**
     * @Given there is a player called :username
     */
    public function thereIsAPlayerCalled($username)
    {
        $this->getNewUser($username);
    }

    /**
     * @When :sender sends :recipient a message
     */
    public function sendsMeAMessage($sender, $recipient)
    {
        $this->sendsAMessageAbout($sender, $recipient, 'Message');
    }

    /**
     * @When :sender sends :recipient a message about :content
     */
    public function sendsAMessageAbout($sender, $recipient, $content)
    {
        $sender = Player::getFromUsername($sender);

        if ($recipient === 'me') {
            $recipient = $this->me;
        } else {
            $recipient = Player::getFromUsername($recipient);
        }

        $participants = array($sender->getId(), $recipient->getId());

        $group = Group::createGroup("Subject", $sender->getId(), $participants);
        $message = $group->sendMessage($sender, $content);
    }

    /**
     * @Given I am logged in as :username
     */
    public function iAmLoggedInAs($username)
    {
        $this->me = $this->getNewUser($username);
        $this->iLogIn();
    }

    /**
     * @Given I create a team called :name
     */
    public function iCreateATeamCalled($name)
    {
        Team::createTeam($name, $this->me->getId(), $this->avatarUrl, "Description");
    }

    /**
     * @Given I am a member of a team called :name
     */
    public function iAmAMemberOfATeamCalled($name)
    {
        $this->iHaveATeamCalled($name)->addMember($this->me->getId());
    }

    /**
     * @Given that the database is empty
     */
    public static function clearDatabase()
    {
        $db = Database::getInstance();

        // Get an array of the tables in the database, so that we can remove them
        $tables = array_map(function ($val) { return current($val); },
                            $db->query('SHOW TABLES'));

        if (count($tables) > 0) {
            $db->query('SET foreign_key_checks = 0');
            $db->query('DROP TABLES ' . implode($tables , ','));
            $db->query('SET foreign_key_checks = 1');
        }

        $host = Service::getParameter('bzion.mysql.host');
        $username = Service::getParameter('bzion.mysql.username');
        $password = Service::getParameter('bzion.mysql.password');
        $database = Service::getParameter('bzion.mysql.database');

        $dsn = 'mysql:dbname=' . $database . ';host=' . $host .';charset=UTF8';
        $pdo = new PDO($dsn, $username, $password);
        $pdo->exec(file_get_contents('DATABASE.sql'));

        if ($modelCache = Service::getModelCache())
            $modelCache->clear();
    }
}
