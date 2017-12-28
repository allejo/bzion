<?php

use Phinx\Migration\AbstractMigration;

class MatchesTableRestructure extends AbstractMigration
{
    //
    // This table will be used to store participation of players in matches. This is to take the place of a comma
    // separated column in the `matches` table for faster JOIN operations versus string operations.
    //

    public function up()
    {
        $playerParticipationTable = $this->table('match_participation', [
            'id' => false,
            'primary_key' => ['match_id', 'user_id']
        ]);
        $playerParticipationTable
            ->addColumn('match_id', 'integer', [
                'signed' => false,
                'limit' => 10,
                'null' => false,
                'comment' => 'The ID of the match this player participated in',
            ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'limit' => 10,
                'null' => false,
                'comment' => 'The ID of the player who participated in this match',
            ])
            ->addColumn('team_id', 'integer', [
                'signed' => false,
                'limit' => 10,
                'null' => true,
                'comment' => 'The ID of the team this player played for at the time of this match'
            ])
            ->addColumn('callsign', 'string', [
                'null' => true,
                'comment' => 'The callsign used by the player during this match.',
            ])
            ->addColumn('ip_address', 'string', [
                'limit' => 46,
                'null' => true,
                'comment' => 'The IP address used by the player in this match',
            ])
            // Integer operations in SQL are faster than strings; so for team loyalty, we can simplify it to 0 or 1
            ->addColumn('team_loyalty', 'integer', [
                'after' => 'callsign',
                'limit' => 1,
                'null' => false,
                'comment' => 'The team color this player played for: 0 will be for "TEAM A" and 1 will be for "TEAM B"'
            ])
            ->addForeignKey('match_id', 'matches', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('user_id', 'players', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'SET_NULL'])
            ->create()
        ;

        $statement = $this->query("SELECT * FROM matches WHERE (team_a_players IS NOT NULL AND team_a_players != '') OR (team_b_players IS NOT NULL AND team_b_players != '')");
        $matches = $statement->fetchAll();
        $insertData = [];

        foreach ($matches as $match) {
            $team_a_players = explode(',', $match['team_a_players']);
            $team_b_players = explode(',', $match['team_b_players']);

            $dataBuilder = function(array $playerIDs, $isTeamB) use (&$insertData, $match) {
                foreach ($playerIDs as $playerID) {
                    if (empty($playerID)) {
                        continue;
                    }

                    $workspace = [
                        'match_id' => $match['id'],
                        'user_id' => $playerID,
                        'team_loyalty' => (int)$isTeamB,
                    ];

                    if ($match['team_a'] !== null && !$isTeamB) {
                        $workspace['team_id'] = $match['team_a'];
                    } elseif ($match['team_b'] !== null && $isTeamB) {
                        $workspace['team_id'] = $match['team_b'];
                    }

                    $insertData[] = $workspace;
                }
            };

            $dataBuilder($team_a_players, false);
            $dataBuilder($team_b_players, true);
        }

        // Only attempt to insert data if we actually have data to insert, otherwise an exception will be thrown
        if (!empty($insertData)) {
            $playerParticipationTable->insert($insertData);
            $playerParticipationTable->saveData();
        }

        $matchesTable = $this->table('matches');
        $matchesTable
            ->removeColumn('team_a_players')
            ->removeColumn('team_b_players')
            ->update()
        ;
    }

    public function down()
    {
        $matchesTable = $this->table('matches');
        $matchesTable
            ->addColumn('team_a_players', 'string', [
                'after' => 'team_b_points',
                'limit' => 256,
                'null' => true,
                'comment' => 'A comma-separated list of BZIDs of players who where on Team 1'
            ])
            ->addColumn('team_b_players', 'string', [
                'after' => 'team_a_players',
                'limit' => 256,
                'null' => true,
                'comment' => 'A comma-separated list of BZIDs of players who where on Team 2'
            ])
            ->update()
        ;

        $statement = $this->query('
            SELECT
              match_id, 
              GROUP_CONCAT(IF(team_loyalty = 0, user_id, NULL)) AS team_a_players,
              GROUP_CONCAT(IF(team_loyalty = 1, user_id, NULL)) AS team_b_players
            FROM
              match_participation
            GROUP BY
              match_id;
        ');
        $results = $statement->fetchAll();

        foreach ($results as $result) {
            $this->execute(sprintf("
                UPDATE matches SET team_a_players = '%s', team_b_players = '%s' WHERE id = %d
            ", $result['team_a_players'], $result['team_b_players'], $result['match_id']));
        }

        $playerParticipationTable = $this->table('match_participation');
        $playerParticipationTable->drop();
    }
}
