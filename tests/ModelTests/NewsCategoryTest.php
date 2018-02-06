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
        parent::setUp();

        $this->player_with_create_perms = $this->getNewPlayer();
        $this->player_without_create_perms = $this->getNewPlayer();

        $this->player_with_create_perms->addRole(Role::ADMINISTRATOR);

        $this->newsCategory = NewsCategory::addCategory('Sample Category');
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->wipe($this->newsCategory);
    }

    public function testCustomNewsCategorySetup()
    {
        $this->assertInstanceOf(NewsCategory::class, $this->newsCategory);
        $this->assertEquals('category', $this->newsCategory->getParamName());
        $this->assertEquals('news category', $this->newsCategory->getTypeForHumans());
    }

    public function testCustomNewsCategoryExists()
    {
        $this->assertArrayContainsModel($this->newsCategory, NewsCategory::getCategories());
    }

    public function testCustomNewsCategoryIsNotProtected()
    {
        $this->assertFalse($this->newsCategory->isProtected());
    }

    public function testCustomNewsCategoryIsNotDeletedByDefault()
    {
        $this->assertFalse($this->newsCategory->isDeleted());
    }

    public function testCustomNewsCategoryIsNotReadOnlyByDefault()
    {
        $this->assertFalse($this->newsCategory->isReadOnly());
    }

    public function testDeletingProtectedNewsCategory()
    {
        $this->expectException(DeletionDeniedException::class);

        $newsCategory = NewsCategory::get(1);
        $newsCategory->delete();
    }

    public function testDeletingCustomNewsCategoryWithoutPosts()
    {
        $this->newsCategory->delete();

        $this->assertTrue($this->newsCategory->isDeleted());
        $this->assertArrayDoesNotContainModel($this->newsCategory, NewsCategory::getCategories());
    }

    public function testDeletingCustomNewsCategoryWithPosts()
    {
        $this->expectException(DeletionDeniedException::class);

        $this->createdModels[] = News::addNews(StringMocks::SampleTitleOne, StringMocks::LargeContent, $this->player_with_create_perms->getId(), $this->newsCategory->getId());

        $this->assertArrayLengthEquals($this->newsCategory->getNews(), 1);

        $this->newsCategory->delete();
    }
}
