<?php

require '../vendor/autoload.php';

use Aws\Kinesis\KinesisClient;
use Monolog\Logger;
use JamesGSilva\MonologKinesisHandler\KinesisHandler;

$kinesis = new KinesisClient([
    'endpoint' => 'http://localhost:4567',
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

$numberOfRecordsPerBatch = 10;
$res = $kinesis->describeStream([ 'StreamName' => $streamName ]);
$shardIds = $res->search('StreamDescription.Shards[].ShardId');
$count = 0;
$startTime = microtime(true);
foreach ($shardIds as $shardId) {
    echo "ShardId: $shardId\n";
    $res = $kinesis->getShardIterator([
        'ShardId' => $shardId,
        'ShardIteratorType' => 'TRIM_HORIZON', // 'AT_SEQUENCE_NUMBER|AFTER_SEQUENCE_NUMBER|TRIM_HORIZON|LATEST'
        'StreamName' => $streamName,
    ]);
    $shardIterator = $res->get('ShardIterator');
    do {
        echo "Get Records\n";
        $res = $kinesis->getRecords([
            'Limit' => $numberOfRecordsPerBatch,
            'ShardIterator' => $shardIterator
        ]);
        $shardIterator = $res->get('NextShardIterator');
        $behind = $res->get('MillisBehindLatest');
        $localCount = 0;
        foreach ($res->search('Records[].[SequenceNumber, Data]') as $data) {
            list($sequenceNumber, $item) = $data;
            echo "- [$sequenceNumber] $item\n";
            $count++;
            $localCount++;
        }
        echo "Processed $localCount records in this batch\n";
    } while ($behind>0 && $shardIterator != '');
}

$duration = microtime(true) - $startTime;
$timePerMessage = $duration*1000 / $count;
echo "Total Messages: " . $count . "\n";
echo "Total Duration: " . round($duration) . " seconds\n";
echo "Time per message: " . round($timePerMessage, 2) . " ms/message\n";