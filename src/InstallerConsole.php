<?php

namespace Phrity\DevKit;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class InstallerConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Phrity DevKit', '1.0.0');

        $this->addCommands([
            new InstallCommand(),
        ]);
    }
}
