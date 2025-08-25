#!/usr/bin/env php
<?php

use Phrity\DevKit\InstallerConsole;

require __DIR__ . '/../vendor/autoload.php';

$installer = new InstallerConsole();
$installer->run();
