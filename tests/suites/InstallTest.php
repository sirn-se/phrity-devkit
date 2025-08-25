<?php

declare(strict_types=1);

namespace Phrity\DevKit\Test;

use PHPUnit\Framework\TestCase;
use Phrity\DevKit\InstallerConsole;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class InstallTest extends TestCase
{
    public function tearDown(): void
    {
        $dir = escapeshellarg(__DIR__ . '/../test-install-area/');
        exec("rm -rf {$dir}");
    }

    public function testInstallCommand(): void
    {
        $console = new InstallerConsole();
        $command = $console->find('install');

        $tester = new CommandTester($command);
        $tester->execute([
            '--target' => __DIR__ . '/../test-install-area/',
        ]);
        $tester->assertCommandIsSuccessful();
    }
}
