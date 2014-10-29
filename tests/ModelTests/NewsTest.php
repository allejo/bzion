<?php

include 'Mocks/StringMocks.php';

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
    }

    public function testCreateNewsWithoutPermissions()
    {
        $this->assertFalse($this->player_a->hasPermission(News::CREATE_PERMISSION));

        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_a->getId(), $this->newsCategory->getId());

        $this->assertFalse($news);
        $this->wipe($news);
    }

    public function testCreateNewsWithPermissions ()
    {
        $this->player_a->addRole(2);

        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_a->getId(), $this->newsCategory->getId());

        $this->assertNotFalse($news);
        $this->assertEquals(TimeDate::now()->diffForHumans(), $news->getCreated());

        $createdLiteral = '<span title="' . $news->getCreated(TimeDate::DATE_FULL) . '">' . $news->getCreated() . '</span>';

        $this->assertEquals($createdLiteral, $news->getCreatedLiteral());
        $this->assertEquals(StringMocks::SampleTitleOne, $news->getSubject());
        $this->assertEquals(StringMocks::LargeContent, $news->getContent());
        $this->assertEquals($this->newsCategory, $news->getCategory());
        $this->assertEquals($this->player_a, $news->getAuthor());
        $this->assertEquals($this->newsCategory->getId(), $news->getCategoryID());
        $this->assertEquals($this->player_a->getId(), $news->getAuthorID());

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

    public function testCategory ()
    {
        $this->player_a->addRole(2);

        $news = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_a->getId(), $this->newsCategory->getId());
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

    public function tearDown()
    {
        $this->wipe($this->newsCategory);
        parent::tearDown();
    }
}