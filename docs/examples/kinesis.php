<?php

require '../vendor/autoload.php';

use Aws\Kinesis\KinesisClient;

$kineses = new KinesisClient([
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

return $kineses;