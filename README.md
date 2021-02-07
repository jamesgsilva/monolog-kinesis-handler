# monolog-kinesis-handler

A monolog handler for AWS Kinesis streams.

## Installation

Install the latest version with

```bash
$ composer require jamesgsilva/monolog-kinesis-handler
```

## Usage

```php
<?php

$kinesis = new \Aws\Kinesis\KinesisClient(['region' => 'us-west-2', 'version' => 'latest']);
$kinesisHandler = new \JamesGSilva\MonologKinesisHandler\KinesisHandler($kinesis, 'stream-name');
$logger = new \Monolog\Logger('channel');
$logger->pushHandler($kinesisHandler);
$logger->info('Hello Kinesis');
```