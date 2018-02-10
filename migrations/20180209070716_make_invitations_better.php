<?php

use Phinx\Migration\AbstractMigration;

class MakeInvitationsBetter extends AbstractMigration
{
    public function change()
    {
        $invitationsTable = $this->table('invitations');
        $invitationsTable
            ->addColumn('sent', 'datetime', [
                'after' => 'team',
                'null' => true,
                'default' => null,
                'comment' => 'When the invitation was sent',
            ])
            ->addColumn('status', 'integer', [
                'after' => 'text',
                'null' => false,
                'default' => 0,
                'signed' => true,
                'length' => 1,
                'comment' => '0: pending; 1: accepted; 2: rejected',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'after' => 'status',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not the invitation has been soft deleted',
            ])
            ->update()
        ;

        $invitationsTable
            ->renameColumn('text', 'message')
            ->update()
        ;

        $invitationsTable
            ->changeColumn('message', 'text', [
                'null' => true,
                'default' => null,
                'comment' => 'The message sent when inviting a player to a team',
            ])
            ->update()
        ;
    }
}
