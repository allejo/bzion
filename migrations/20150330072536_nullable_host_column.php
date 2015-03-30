<?php

use Phinx\Migration\AbstractMigration;

class NullableHostColumn extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("ALTER TABLE visits MODIFY host VARCHAR(100) COMMENT 'The host of the player. If this value is null, the host could not be resolved.'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}