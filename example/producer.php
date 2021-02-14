<?php

require '../vendor/autoload.php';

use Aws\Kinesis\KinesisClient;
use Monolog\Logger;
use JamesGSilva\MonologKinesisHandler\KinesisHandler;

$kinesis = new KinesisClient([
    'endpoint' => 'http://localhost:4567', //https://github.com/mhart/kinesalite
    'region' => 'us-west-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => 'YOUR_AWS_ACCESS_KEY_ID',
        'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
    ],
    'retries'     => 10,
    'delay'       => 1000,
    'synchronous' => true,
    'http'        => [
        'timeout' => 5,
        'connect_timeout' => 5,
        'verify' => false
    ]
]);
$shardCount = 2;
$streamName = 'my_stream_name';
try {
    $kinesis->createStream([
        'ShardCount' => $shardCount,
        'StreamName' => $streamName,
    ]);
} catch (\Throwable $e) {
    echo $e->getMessage(), PHP_EOL;
}
$kinesisHandler = new KinesisHandler($kinesis, $streamName);
$logger = new Logger('logs');
$logger->pushHandler($kinesisHandler);
$logger->info('Hello Kinesis');