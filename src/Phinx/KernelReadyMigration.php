<?php

namespace BZIon\Phinx;

use AppKernel;
use Phinx\Migration\AbstractMigration;

class KernelReadyMigration extends AbstractMigration
{
    protected function init()
    {
        parent::init();

        $kernel = new AppKernel('prod', false);
        $kernel->boot();
    }
}
