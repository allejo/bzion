<?php

use Phinx\Migration\AbstractMigration;

class GroupRename extends AbstractMigration
{
    public function change()
    {
        $this->table('groups')->rename('conversations');

        $this->execute("alter table player_groups drop foreign key player_groups_ibfk_1");
        $this->execute("alter table player_groups drop foreign key player_groups_ibfk_2");
        $this->execute("alter table team_groups drop foreign key team_groups_ibfk_1");
        $this->execute("alter table team_groups drop foreign key team_groups_ibfk_2");
        $this->execute("alter table messages drop foreign key messages_ibfk_1");

        $this->table('player_groups')
            ->rename('player_conversations')
            ->renameColumn('group', 'conversation')
            ->addForeignKey('conversation', 'conversations', 'id', array('delete' => 'CASCADE'))
            ->addForeignKey('player', 'players', 'id', array('delete' => 'CASCADE'));

        $this->table('team_groups')
            ->rename('team_conversations')
            ->renameColumn('group', 'conversation')
            ->addForeignKey('conversation', 'conversations', 'id', array('delete' => 'CASCADE'))
            ->addForeignKey('team', 'teams', 'id', array('delete' => 'CASCADE'));

        $this->table('messages')
            ->renameColumn('group_to', 'conversation_to')
            ->addForeignKey('conversation_to', 'conversations', 'id', array('delete' => 'CASCADE'));
    }
}
