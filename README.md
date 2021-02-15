# monolog-kinesis-handler

A monolog handler for AWS Kinesis streams.

## Installation

Require this library through composer:

```bash
$ composer require jamesgsilva/monolog-kinesis-handler
```

## Usage

You can find usage examples [here](docs/examples) using [kinesalite](https://github.com/mhart/kinesalite) an implementation of Amazon's Kinesis built on LevelDB.

```php
<?php

$kinesis = new \Aws\Kinesis\KinesisClient(['region' => 'us-west-2', 'version' => 'latest']);
$kinesisHandler = new \JamesGSilva\MonologKinesisHandler\KinesisHandler($kinesis, 'stream-name');
$logger = new \Monolog\Logger('channel');
$logger->pushHandler($kinesisHandler);
$logger->info('Hello Kinesis');
```

## Contributing

Feel free to contribute by opening a pull request. Bug fixes or feature suggestions are always welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) for information.