<?php

/**
 * This file is part of the JamesGSilva Monolog Kinesis Handler package.
 *
 * @author James G Silva <jamesgsilva@pm.me>
 *
 * @license https://opensource.org/licenses/mit-license.php MIT
 */

declare(strict_types=1);

namespace JamesGSilva\MonologKinesisHandler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Aws\Kinesis\KinesisClient;

/**
 * Writes to any kineses stream.
 */
class KinesisHandler extends AbstractProcessingHandler
{

    /**
     * This client is used to interact with the Amazon Kinesis service
     *
     * @var KinesisClient
     */
    private $client;

    /**
     * The name of the stream to put the data record into
     *
     * @var string
     */
    private $stream;


    /**
     * KinesisHandler constructor
     *
     * @param KinesisClient $client This client is used to interact with the Amazon Kinesis service
     * @param string        $stream The name of the stream to put the data record into
     * @param int           $level  The minimum logging level at which this handler will be triggered
     * @param bool          $bubble Whether the messages that are handled can bubble up the stack or not
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

    }//end __construct()


    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param array<string, mixed> $record record for put in stream
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if (isset($record['formatted']) === false || is_string($record['formatted']) === false) {
            throw new \InvalidArgumentException('KinesisHandler accepts only formatted records as a string');
        }

        $request = [
            'StreamName'   => $this->stream,
            'PartitionKey' => $record['channel'],
            'Data'         => $record['formatted'],
        ];
        $this->client->putRecord($request);

    }//end write()


}//end class
