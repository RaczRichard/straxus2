<?php

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require 'vendor/autoload.php';
$rootDir = __DIR__;
$log = new Logger('global-log');
$log->pushHandler(new StreamHandler($rootDir.'/straxus.log', Logger::DEBUG));

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

require 'src/bootstrap.php';