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
     * @var NewsCategory
     */
    protected $newsCategory;

    protected function setUp ()
    {
        $this->connectToDatabase();

        $this->player_with_create_perms = $this->getNewPlayer();
        $this->player_without_create_perms = $this->getNewPlayer();

        $this->player_with_create_perms->addRole(2);

        $this->newsCategory = NewsCategory::addCategory("Sample Category");
    }

    public function testCreateNewCategory()
    {
        $this->assertArrayContainsModel($this->newsCategory, NewsCategory::getCategories());
        $this->assertFalse($this->newsCategory->isProtected());
        $this->assertEquals("enabled", $this->newsCategory->getStatus());

        $this->newsCategory->disableCategory();
        $this->assertEquals("disabled", $this->newsCategory->getStatus());

        $this->newsCategory->enableCategory();
        $this->assertEquals("enabled", $this->newsCategory->getStatus());

        $this->assertEquals(array("enabled"), $this->newsCategory->getActiveStatuses());
        $this->assertEquals("category", $this->newsCategory->getParamName());
        $this->assertEquals("news category", $this->newsCategory->getTypeForHumans());
    }

    public function testCreateNewsWithoutPermissions()
    {
        $this->assertFalse($this->player_without_create_perms->hasPermission(News::CREATE_PERMISSION));

        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_without_create_perms->getId(), $this->newsCategory->getId());

        $this->assertFalse($news);
        $this->wipe($news);
    }

    public function testCreateNewsWithPermissions ()
    {
        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());

        $this->assertNotFalse($news);
        $this->assertEquals(TimeDate::now()->diffForHumans(), $news->getCreated());

        $createdLiteral = '<span title="' . $news->getCreated(TimeDate::DATE_FULL) . '">' . $news->getCreated() . '</span>';

        $this->assertEquals($createdLiteral, $news->getCreatedLiteral());
        $this->assertEquals(StringMocks::SampleTitleOne, $news->getSubject());
        $this->assertEquals(StringMocks::LargeContent, $news->getContent());
        $this->assertEquals($this->newsCategory, $news->getCategory());
        $this->assertEquals($this->player_with_create_perms, $news->getAuthor());
        $this->assertEquals($this->newsCategory->getId(), $news->getCategoryID());
        $this->assertEquals($this->player_with_create_perms->getId(), $news->getAuthorID());

        $this->assertEquals($news->getLastEdit(), $news->getCreated());
        $this->assertEquals($news->getLastEdit(TimeDate::DATE_FULL), $news->getCreated(TimeDate::DATE_FULL));

        $this->assertEquals($news->getAuthor(), $news->getLastEditor());
        $this->assertEquals($news->getAuthorID(), $news->getLastEditorID());

        $news->updateSubject(StringMocks::SampleTitleTwo);
        $news->updateContent(StringMocks::MediumContent);

        $this->assertEquals(StringMocks::SampleTitleTwo, $news->getSubject());
        $this->assertEquals(StringMocks::MediumContent, $news->getContent());

        $this->wipe($news);
    }

    public function testNewsStatuses ()
    {
        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());
        $unorgCategory = new NewsCategory(1);

        $news->updateCategory($unorgCategory->getId());
        $this->assertEquals($unorgCategory, $news->getCategory());

        $this->assertEquals("published", $news->getStatus());
        $news->updateStatus("draft");
        $this->assertEquals("draft", $news->getStatus());

        $player_b = $this->getNewPlayer();

        $news->updateLastEditor($player_b->getId());
        $this->assertEquals($player_b, $news->getLastEditor());

        $news->updateEditTimestamp();
        $this->assertEquals(TimeDate::now()->diffForHumans(), $news->getLastEdit());

        $this->wipe($news);
    }

    public function testFetchingNews ()
    {
        $publishedNewsArticle = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());
        $draftedNewsArticle = News::addNews(StringMocks::SampleTitleOne, StringMocks::ShortContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId(), "draft");

        $this->assertArrayContainsModel($publishedNewsArticle, $this->newsCategory->getNews());
        $this->assertArrayContainsModel($draftedNewsArticle, $this->newsCategory->getNews(0, 5, true));

        $this->wipe($publishedNewsArticle, $draftedNewsArticle);
    }

    public function testDeletingCategory ()
    {
        $article = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());

        $this->newsCategory->delete();
        $this->assertNotEquals("deleted", $this->newsCategory->getStatus());

        $article->wipe();
        $this->newsCategory->delete();
        $this->assertEquals("deleted", $this->newsCategory->getStatus());

        $this->wipe($article);
    }

    public function tearDown()
    {
        $this->wipe($this->newsCategory);
        parent::tearDown();
    }
}
