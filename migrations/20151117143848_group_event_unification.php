<?php

use Phinx\Migration\AbstractMigration;

class GroupEventUnification extends AbstractMigration
{
    public function up()
    {
        $this->table('messages')
            ->addColumn('event_type', 'string', array('limit' => 50, 'null' => true, 'comment' => 'The type of the event, NULL if it\'s a message'))
            ->update();

        $this->execute("ALTER TABLE messages MODIFY player_from int(10) UNSIGNED DEFAULT NULL");
        $this->execute("ALTER TABLE messages MODIFY status set('visible', 'hidden', 'deleted', 'reported') NOT NULL DEFAULT 'visible'");

        // MySQL doesn't properly set the default value for sets, so we have to
        // do it manually
        $this->execute("UPDATE messages SET status = 'visible' WHERE status = ''");

        $this->dropTable('group_events');
    }

    public function down()
    {
        $this->execute("
            # Dump of table group_events
            # ------------------------------------------------------------

            CREATE TABLE `group_events` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `group_to` int(10) unsigned NOT NULL COMMENT 'The ID of the group where the event occured',
              `event` text NOT NULL COMMENT 'The serialized data of the event',
              `type` varchar(50) NOT NULL COMMENT 'The type of the event',
              `timestamp` datetime NOT NULL COMMENT 'The timestamp of when the event took place',
              `status` set('visible','deleted') NOT NULL DEFAULT 'visible' COMMENT 'That status of the group event',
              PRIMARY KEY (`id`),
              KEY `group_to` (`group_to`),
              CONSTRAINT `group_event_ibfk_1` FOREIGN KEY (`group_to`) REFERENCES `groups` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->table('messages')->removeColumn('event_type');

        $this->execute("ALTER TABLE messages MODIFY status set('sent', 'hidden', 'deleted', 'reported') NOT NULL DEFAULT 'sent'");
        $this->execute("UPDATE messages SET status = 'sent' WHERE status = ''");
    }
}
