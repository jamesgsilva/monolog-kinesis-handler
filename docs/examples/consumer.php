<?php

require '../vendor/autoload.php';

/** @var \Aws\Kinesis\KinesisClient $kinesis */
$kinesis = require 'kinesis.php';
$shardCount = 2;
$streamName = 'my_stream_name';
$kinesis->createStream([
    'ShardCount' => $shardCount,
    'StreamName' => $streamName,
]);
$numberOfRecordsPerBatch = 10;
$res = $kinesis->describeStream([ 'StreamName' => $streamName ]);
$shardIds = $res->search('StreamDescription.Shards[].ShardId');
$count = 0;
$startTime = microtime(true);
foreach ($shardIds as $shardId) {
    echo "ShardId: $shardId\n";
    $res = $kinesis->getShardIterator([
        'ShardId' => $shardId,
        'ShardIteratorType' => 'TRIM_HORIZON',
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