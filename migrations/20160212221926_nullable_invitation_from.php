<?php

use Phinx\Migration\AbstractMigration;

class NullableInvitationFrom extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("ALTER TABLE invitations MODIFY sent_by INT(10) UNSIGNED DEFAULT NULL COMMENT 'The player who sent the invitation'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
