<?php

use BZIon\Phinx\KernelReadyMigration;

class ClearDeletedTeamAvatars extends KernelReadyMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $playersTable = $this->table('players');
        $playersTable
            ->changeColumn('avatar', 'string', [
                'limit' => 200,
                'null' => true,
                'comment' => "The path to the player's avatar relative to the bzion project root",
            ])
            ->update()
        ;

        $this->execute("
            UPDATE
                players
            SET
                avatar = NULL 
            WHERE
                status IN ('deleted', 'disabled')
        ");

        $teamsTable = $this->table('teams');
        $teamsTable
            ->changeColumn('avatar', 'string', [
                'limit' => 200,
                'null' => true,
                'comment' => "The path to the player's avatar relative to the bzion project root",
            ])
            ->update()
        ;

        $this->execute("
            UPDATE
                teams
            SET
                avatar = NULL
            WHERE
                status IN ('deleted', 'disabled')
        ");
    }
}
