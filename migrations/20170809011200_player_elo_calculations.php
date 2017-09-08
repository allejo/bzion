<?php

use Phinx\Migration\AbstractMigration;

class PlayerEloCalculations extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $kernel = new AppKernel('prod', false);
        $kernel->boot();
        $qb = new MatchQueryBuilder('Match', [
            'columns' => [
                'team_a_players' => 'team_a_players',
                'team_b_players' => 'team_b_players',
                'match_type' => 'match_type',
                'timestamp'  => 'timestamp',
                'status'     => 'status',
            ],
        ]);

        $query = $qb
            ->where('team_a_players')->isNotNull()
            ->where('team_a_players')->notEquals('')
            ->where('team_b_players')->isNotNull()
            ->where('team_b_players')->notEquals('')
            ->where('match_type')->equals('official')
            ->where('status')->equals('entered')
            ->sortBy('timestamp')
            ->limit(1000)
        ;

        $pageCount = $query->countPages();
        $lastSeason = [];

        for ($i = 1; $i <= $pageCount; $i++) {
            $matches = $query
                ->fromPage($i)
                ->getModels($fast = true)
            ;

            /** @var Match $match */
            foreach ($matches as $match) {
                $seasonInfo = Season::getSeason($match->getTimestamp());

                // Clear the model cache every season since Elos are cached internally
                if ($seasonInfo != $lastSeason) {
                    $lastSeason = $seasonInfo;
                    Service::getModelCache()->clear();
                }

                $teamA = $match->getTeamAPlayers();
                $teamB = $match->getTeamBPlayers();

                if (empty($teamA) || empty($teamB)) {
                    continue;
                }

                $getElo = function($n) use ($seasonInfo) {
                    /** @var Player $n */
                    return $n->getElo($seasonInfo['season'], $seasonInfo['year']);
                };

                $teamA_Avg = array_sum(array_map($getElo, $teamA)) / count($teamA);
                $teamB_Avg = array_sum(array_map($getElo, $teamB)) / count($teamB);

                $diff = Match::calculateEloDiff(
                    $teamA_Avg, $teamB_Avg, $match->getTeamAPoints(), $match->getTeamBPoints(), $match->getDuration()
                );

                // We need to disable transactions so our Player::adjustElo() fxn won't hold up execution
                $this->getAdapter()->commitTransaction();

                $this->query("UPDATE matches SET player_elo_diff = {$diff} WHERE id = {$match->getId()} LIMIT 1;");

                $walkFxn = function ($v, $k, $positive) use ($diff, $match, &$batch, $seasonInfo) {
                    /** @var Player $v */

                    $eloDiff = ($positive) ? $diff : -$diff;

                    if ($v->isValid()) {
                        $v->adjustElo($eloDiff, $match);
                    }
                };

                array_walk($teamA, $walkFxn, true);
                array_walk($teamB, $walkFxn, false);

                $this->getAdapter()->beginTransaction();
            }
        }
    }

    public function down()
    {
        $seasonElos = $this->table('player_elo');
        $seasonElos->truncate();
    }
}
