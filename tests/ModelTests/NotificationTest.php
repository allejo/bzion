<?php

use BZIon\Event\Events;
use BZIon\Event\WelcomeEvent;

class NotificationTest extends TestCase
{
    private $player;
    private $notification;

    protected function setUp()
    {
        $this->connectToDatabase();

        $this->player = $this->getNewPlayer();
        $this->reset($this->player);
    }

    public function testNotification()
    {
        $event = new WelcomeEvent("Welcome!", $this->player);

        $this->notification = Notification::newNotification(
            $this->player->getId(),
            Events::WELCOME, $event
        );

        $this->assertEquals($event, $this->notification->getEvent());
        $this->assertEquals(Events::WELCOME, $this->notification->getCategory());

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
