<?php

class NewsTest extends TestCase
{
    /**
     * @var Player
     */
    protected $player_with_create_perms;

    /**
     * @var Player
     */
    protected $player_without_create_perms;

    /**
     * @var \NewsCategory
     */
    protected $newsCategory;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player_with_create_perms = $this->getNewPlayer();
        $this->player_without_create_perms = $this->getNewPlayer();

        $this->player_with_create_perms->addRole(Role::ADMINISTRATOR);

        $this->newsCategory = NewsCategory::addCategory("Sample Category");
    }

    public function testCustomNewsCategorySetup()
    {
        $this->assertInstanceOf('NewsCategory', $this->newsCategory);
        $this->assertEquals(array("enabled"), $this->newsCategory->getActiveStatuses());
        $this->assertEquals("category", $this->newsCategory->getParamName());
        $this->assertEquals("news category", $this->newsCategory->getTypeForHumans());
    }

    public function testCustomNewsCategoryExists()
    {
        $this->assertArrayContainsModel($this->newsCategory, NewsCategory::getCategories());
    }

    public function testCustomNewsCategoryIsNotProtected()
    {
        $this->assertFalse($this->newsCategory->isProtected());
    }

    public function testCustomNewsCategoryDefaultStatus()
    {
        $this->assertEquals("enabled", $this->newsCategory->getStatus());
    }

    public function testDisablingCustomNewsCategory()
    {
        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());
    }

    public function testDisablingDisabledCustomNewsCategory()
    {
        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());

        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());
    }

    public function testEnableDisabledCustomNewsCategory()
    {
        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());

        $this->newsCategory->enableCategory();
        $this->assertEquals("enabled", $this->newsCategory->getStatus());
    }

    public function testDeletingProtectedNewsCategory()
    {
        $newsCategory = new NewsCategory(1);

        $newsCategory->delete();
        $this->assertEquals('enabled', $newsCategory->getStatus());

        $this->assertArrayContainsModel($newsCategory, NewsCategory::getCategories());
    }

    public function testDeletingCustomNewsCategoryWithoutPosts()
    {
        $this->newsCategory->delete();
        $this->assertEquals('deleted', $this->newsCategory->getStatus());

        $this->assertArrayDoesNotContainModel($this->newsCategory, NewsCategory::getCategories());
    }

    public function testDeletingCustomNewsCategoryWithPosts()
    {
        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());

        $this->assertArrayLengthEquals($this->newsCategory->getNews(), 1);

        $this->newsCategory->delete();
        $this->assertEquals('enabled', $this->newsCategory->getStatus());

        $this->assertArrayContainsModel($this->newsCategory, NewsCategory::getCategories());

        $this->wipe($news);
    }

    public function tearDown()
    {
        $this->wipe($this->newsCategory);
        parent::tearDown();
    }
}
