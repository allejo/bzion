<?php

use BZIon\Phinx\KernelReadyMigration;

class MatchServerRelationship extends KernelReadyMigration
{
    public function up()
    {
        $matches = $this->table('matches');
        $matches
            ->addColumn('server_id', 'integer', [
                'after'   => 'match_details',
                'limit'   => 10,
                'signed'  => false,
                'null'    => true,
                'comment' => 'The server where this match took place'
            ])
            ->addForeignKey('server_id', 'servers', 'id', ['delete' => 'SET NULL'])
            ->save()
        ;

        // Build a cache for server addresses
        $address = [];
        $servers = $this->fetchAll('SELECT * FROM servers');

        foreach ($servers as $server) {
            $fqdn = sprintf('%s:%s', $server['domain'], $server['port']);
            $address[$fqdn] = $server['id'];
        }

        // Get all of the matches we can work with
        $countQuery = "SELECT COUNT(*) FROM matches WHERE server IS NOT NULL AND server != ''";
        $matchesQuery = "
            SELECT
                *
            FROM
                matches
            WHERE
                server IS NOT NULL AND server != '' AND 
                id > {id}
            LIMIT 1000
        ";

        $pageCount = ceil($this->fetchRow($countQuery)[0] / 1000);
        $lastID = 0;

        for ($i = 1; $i <= $pageCount; $i++) {
            $matches = $this->fetchAll(strtr($matchesQuery, [
                '{id}' => $lastID
            ]));

            foreach ($matches as $match) {
                $match_address = $match['server'];

                if (isset($address[$match_address])) {
                    $this->execute("UPDATE matches SET server_id = {$address[$match_address]} WHERE id = {$match['id']} LIMIT 1");
                }
            }
        }
    }

    public function down()
    {
        $matches = $this->table(Match::TABLE);
        $matches
            ->dropForeignKey('server_id')
            ->removeColumn('server_id')
        ;
    }
}
