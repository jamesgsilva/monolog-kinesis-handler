<?php

require '../vendor/autoload.php';

use Aws\Kinesis\KinesisClient;
use Monolog\Logger;
use JamesGSilva\MonologKinesisHandler\KinesisHandler;

/** @var KinesisClient $kinesis */
$kinesis = require 'kinesis.php';
$shardCount = 2;
$streamName = 'my_stream_name';
$kinesis->createStream([
    'ShardCount' => $shardCount,
    'StreamName' => $streamName,
]);
$kinesisHandler = new KinesisHandler($kinesis, $streamName);
$logger = new Logger('logs');
$logger->pushHandler($kinesisHandler);
$logger->info('Hello Kinesis');