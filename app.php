#!/usr/bin/env php
<?php
// application.php

require_once __DIR__.'/vendor/autoload.php';

use BOL\Command\BOLScrapeCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BOLScrapeCommand());
$application->run();