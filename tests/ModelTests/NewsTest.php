<?php

class NewsTest extends TestCase
{
    /**
     * @var Player
     */
    protected $player_a;

    /**
     * @var NewsCategory
     */
    protected $newsCategory;

    protected function setUp ()
    {
        $this->connectToDatabase();

        $this->player_a = $this->getNewPlayer();
    }

    public function testCreateNewCategoryAndNews ()
    {
        $this->newsCategory = NewsCategory::addCategory("Sample Category");

        $this->assertArrayContainsModel($this->newsCategory, NewsCategory::getCategories());
        $this->assertFalse($this->newsCategory->isProtected());
        $this->assertEquals("enabled", $this->newsCategory->getStatus());

        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());

        $this->newsCategory->enableCategory();
        $this->assertEquals("enabled", $this->newsCategory->getStatus());

        $newsSubject = "Sample News Article";
        $newsContent = "Some really awesome and important message that requires an entry.";

        $this->assertFalse($this->player_a->hasPermission(News::CREATE_PERMISSION));

        $news = News::addNews($newsSubject, $newsContent, $this->player_a->getId(), $this->newsCategory->getId());

        $this->assertFalse($news);

        $this->player_a->addRole(2);

        $news = News::addNews($newsSubject, $newsContent, $this->player_a->getId(), $this->newsCategory->getId());

        $this->assertNotFalse($news);

        $this->assertEquals($newsSubject, $news->getSubject());
        $this->assertEquals($newsContent, $news->getContent());
        $this->assertEquals($this->newsCategory, $news->getCategory());
        $this->assertEquals($this->player_a, $news->getAuthor());
        $this->assertEquals($this->newsCategory->getId(), $news->getCategoryID());
        $this->assertEquals($this->player_a->getId(), $news->getAuthorID());

        $this->wipe($news);
    }

    public function tearDown()
    {
        $this->wipe($this->player_a, $this->newsCategory);
        parent::tearDown();
    }
}