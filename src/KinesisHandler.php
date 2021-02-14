<?php

declare(strict_types=1);

namespace JamesGSilva\MonologKinesisHandler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Aws\Kinesis\KinesisClient;
use InvalidArgumentException;

/**
 * Class KinesisHandler
 */
class KinesisHandler extends AbstractProcessingHandler
{
    /**
     * This client is used to interact with the Amazon Kinesis service.
     *
     * @var KinesisClient
     */
    private $client;

    /**
     * The name of the stream to put the data record into.
     *
     * @var string
     */
    private $stream;

    /**
     * KinesisHandler constructor
     *
     * @param KinesisClient $client This client is used to interact with the Amazon Kinesis service.
     * @param string        $stream The name of the stream to put the data record into.
     * @param integer       $level  The minimum logging level at which this handler will be triggered.
     * @param boolean       $bubble Whether the messages that are handled can bubble up the stack or not.
     */
    public function __construct(
        KinesisClient $client,
        string $stream,
        int $level=Logger::INFO,
        bool $bubble=true
    ) {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->stream = $stream;
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param array<string, mixed> $record Record for put in stream.
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if (isset($record['formatted']) === false || is_string($record['formatted']) === false) {
            throw new InvalidArgumentException('KinesisHandler accepts only formatted records as a string');
        }

        $request = [
            'StreamName'   => $this->stream,
            'PartitionKey' => $record['channel'],
            'Data'         => $record['formatted'],
        ];
        $this->client->putRecord($request);
    }
}
