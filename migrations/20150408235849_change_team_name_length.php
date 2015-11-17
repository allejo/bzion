<?php

use BZIon\Migration\AbstractMigration;

class ChangeTeamNameLength extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('teams');

        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'name' || $column->getName() === 'alias') {
                $column->setLimit(42);

                if ($column->getName() === 'name') {
                    $column->setComment("The team's name");
                } else {
                    $column->setComment("The team's URL slug for viewing the team's profile");
                }

                $table->changeColumn($column->getName(), $column);
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
