<?php

use Phinx\Migration\AbstractMigration;

/**
 * Restructure the `bans` table to better support bans and mutes.
 *
 * - The `allow_server_join` column was never correctly used and therefore has been dropped; due to incorrect use, the
 *   data did not need to be migrated or saved.
 * - The `is_soft_ban` column is being added to differentiate between bans that should penalize players on the site
 *   itself and bans that will only affect players on league servers. A soft ban will not affect a player on BZiON.
 *
 * @link https://github.com/allejo/bzion/issues/157
 */
class BanMuteSupportIssue157 extends AbstractMigration
{
    public function up()
    {
        $bansTable = $this->table('bans');
        $bansTable
            ->removeColumn('allow_server_join')
            ->addColumn('is_soft_ban', 'boolean', [
                'after' => 'author',
                'null' => false,
                'default' => false,
                'comment' => 'A soft ban will not penalize a user on the site or servers',
            ])
            ->update()
        ;

        // Unban any currently banned players
        $this->query("UPDATE players SET status = 'active' WHERE status = 'banned';");
    }

    public function down()
    {
        $bansTable = $this->table('bans');
        $bansTable
            ->removeColumn('is_soft_ban')
            ->addColumn('allow_server_join', 'boolean', [
                'after' => 'reason',
                'null' => false,
                'default' => false,
                'comment' => 'Whether or not to allow players to join servers while banned'
            ])
            ->update()
        ;
    }
}
