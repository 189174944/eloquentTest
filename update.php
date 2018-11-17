<?php
$mysql = require './config.php';
include './vendor/autoload.php';
include './helpers.php';

$client = new Predis\Client([
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'port' => 6379
]);
