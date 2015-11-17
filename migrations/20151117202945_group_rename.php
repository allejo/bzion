<?php

use Phinx\Migration\AbstractMigration;

class GroupRename extends AbstractMigration
{
    public function change()
    {
        $this->table('groups')->rename('conversations');

        $this->table('player_groups')
            ->rename('player_conversations')
            ->renameColumn('group', 'conversation');

        $this->table('team_groups')
            ->rename('team_conversations')
            ->renameColumn('group', 'conversation');

        $messages = $this->table('messages');
        $messages->renameColumn('group_to', 'conversation_to');
    }
}
