<?php

class TeamTest extends TestCase
{
    private $player;
    private $playerid;
    private $notification;

    protected function setUp()
    {
        global $db;
        $db = new Database(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB_NAME);

        $this->player = $this->getNewPlayer();
    }

    public function testNotification()
    {
        $this->notification = Notification::newNotification($this->player->getId(), "Something happened, please take a look");

        $this->assertEquals("Something happened, please take a look", $this->notification->getMessage());
        $this->assertEquals($this->player->getId(), $this->notification->getReceiver()->getId());
        $this->assertFalse($this->notification->isRead());

        $this->notification->markAsRead();
        $this->assertTrue($this->notification->isRead());

        // Refresh the notification from the database
        $this->notification = new Notification($this->notification->getId());
        $this->assertTrue($this->notification->isRead());
    }


    public function tearDown()
    {
        $this->wipe($this->notification);
        parent::tearDown();
    }
}
