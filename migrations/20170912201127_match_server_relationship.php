<?php

use BZIon\Phinx\KernelReadyMigration;

class MatchServerRelationship extends KernelReadyMigration
{
    public function up()
    {
        $matches = $this->table(Match::TABLE);
        $matches
            ->addColumn('server_id', 'integer', [
                'after'   => 'match_details',
                'limit'   => 10,
                'signed'  => false,
                'null'    => true,
                'comment' => 'The server where this match took place'
            ])
            ->addForeignKey('server_id', 'servers', 'id', ['delete' => 'CASCADE'])
            ->save()
        ;

        // Build a cache for server addresses
        $address = [];
        $servers = Server::getQueryBuilder()->getModels($fast = true);

        /** @var Server $server */
        foreach ($servers as $server) {
            $address[sprintf('%s:%s', $server->getDomain(), $server->getPort())] = $server->getId();
        }

        // Get all of the matches we can work with
        $matchQB = new MatchQueryBuilder('Match', [
            'columns' => [
                'server' => 'server',
            ]
        ]);
        $query = $matchQB
            ->where('server')->isNotNull()
            ->where('server')->notEquals('')
            ->limit(1000);

        $pageCount = $query->countPages();

        for ($i = 1; $i <= $pageCount; $i++) {
            $matches = $query
                ->fromPage($i)
                ->getModels($fast = true)
            ;

            /** @var Match $match */
            foreach ($matches as $match) {
                $match_address = $match->getServerAddress();

                if (isset($address[$match_address])) {
                    $match->setServer($address[$match_address]);
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
