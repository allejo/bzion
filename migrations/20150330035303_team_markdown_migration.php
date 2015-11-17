<?php

use Phinx\Migration\AbstractMigration;

class TeamMarkdownMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $teamTable = $this->table("teams");
        $teamTable->renameColumn("description_md", "description");

        $this->execute("ALTER TABLE teams DROP COLUMN description_html");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
