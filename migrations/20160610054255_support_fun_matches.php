<?php

use Phinx\Migration\AbstractMigration;

class SupportFunMatches extends AbstractMigration
{
    public function change()
    {
        $columnsToUpdate = array(
            'team_a' => 'Team 1 who played in this match',
            'team_b' => 'Team 2 who played in this match',
            'team_a_elo_new' => 'The new ELO for Team 1',
            'team_b_elo_new' => 'The new ELO for Team 2',
            'elo_diff' => 'The difference in ELO to Team 1'
        );

        $matches = $this->table('matches');
        $columns = $matches->getColumns();

        $matches->dropForeignKey(array('team_a', 'team_b'));

        foreach ($columns as $column)
        {
            if (in_array($column->getName(), array_keys($columnsToUpdate)))
            {
                $column->setOptions(array(
                    'null'    => true,
                    'signed'  => ($column->getName() == 'elo_diff'),
                    'comment' => $columnsToUpdate[$column->getName()]
                ));

                $matches->changeColumn($column->getName(), $column);
            }
        }

        $matches->addForeignKey('team_a', 'teams');
        $matches->addForeignKey('team_b', 'teams');

        $teamColorAssets = array('values'  => array('red', 'green', 'blue', 'purple'),
                                 'null'    => true,
                                 'comment' => 'The color of the team');

        $matches->addColumn('team_a_color', 'set', $teamColorAssets);
        $matches->addColumn('team_b_color', 'set', $teamColorAssets);
        $matches->addColumn('match_type', 'set', array('values' => array('official', 'fm', 'special'),
                                                       'null' => false,
                                                       'default' => 'official',
                                                       'comment' => 'The type of match that was played'));
        $matches->save();
    }
}
