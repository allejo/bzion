<?php

namespace BZIon\Command;

use Symfony\Component\Console\Output\NullOutput as BaseOutput;

class NullOutput extends BaseOutput
{
    public function isVerbose()
    {
        return false;
    }
}
