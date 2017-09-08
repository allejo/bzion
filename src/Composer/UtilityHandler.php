<?php

namespace BZIon\Composer;

use AppKernel;
use Composer\Script\Event;
use Player;
use Team;

class UtilityHandler
{
    public static function resetAvatars(Event $event)
    {
        $kernel = new AppKernel('prod', false);
        $kernel->boot();

        $io = $event->getIO();

        // ...first players
        $qb = Player::getQueryBuilder();
        $players = $qb->active()->getModels();

        $io->write("Resetting avatars for " . count($players) . " players.");

        /** @var Player $player */
        foreach ($players as $player) {
            $player->resetAvatar();
        }

        // ...now do teams
        $qb = Team::getQueryBuilder();
        $teams = $qb->active()->getModels();

        $io->write("Resetting avatars for " . count($teams) . " teams.");

        /** @var Team $team */
        foreach ($teams as $team) {
            $team->resetAvatar();
        }
    }
}
