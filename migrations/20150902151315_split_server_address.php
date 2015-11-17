<?php

use Phinx\Migration\AbstractMigration;

class SplitServerAddress extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $servers = $this->table('servers');
        $servers->renameColumn("address", "domain");
        $servers->addColumn("port", "integer", array("after" => "domain", "length" => 6))
                ->update();

        $rows = $this->fetchAll("SELECT * FROM servers");

        foreach ($rows as $row) {
            $id = $row["id"];
            list($domain, $port) = explode(":", $row["domain"]);

            $this->query("UPDATE servers SET domain='${domain}', port='${port}' WHERE id = ${id}");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $servers = $this->table('servers');

        $rows = $this->fetchAll("SELECT * FROM servers");

        foreach ($rows as $row) {
            $newValue = "${row['domain']}:${row['port']}";

            $this->query("UPDATE servers SET domain='${newValue}' WHERE id=${row['id']}");
        }

        $servers->renameColumn("domain", "address");
        $servers->removeColumn("port")
                ->update();
    }
}
