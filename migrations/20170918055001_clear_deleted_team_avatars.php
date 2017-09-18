<?php

use BZIon\Phinx\KernelReadyMigration;

class ClearDeletedTeamAvatars extends KernelReadyMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $players = Player::getQueryBuilder()
            ->where('status')->isOneOf(['deleted', 'disabled'])
            ->getModels($fast = true)
        ;

        /** @var Player $player */
        foreach ($players as $player) {
            $player->resetAvatar();
        }

        $teams = Team::getQueryBuilder()
            ->where('status')->isOneOf(['deleted', 'disabled'])
            ->getModels($fast = true)
        ;

        /** @var Team $team */
        foreach ($teams as $team) {
            $team->resetAvatar();
        }
    }
}
