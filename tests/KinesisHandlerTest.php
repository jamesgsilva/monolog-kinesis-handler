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

use Aws\Kinesis\KinesisClient;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class KinesisHandlerTest extends TestCase
{

    /**
     * This client is used to interact with the Amazon Kinesis service
     *
     * @var KinesisClient&MockObject
     */
    private $client;


    /**
     * This method is called before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(KinesisClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__call'])
            ->getMock();

    }//end setUp()


    /**
     * Test class constructor
     *
     * @return void
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(KinesisHandler::class, new KinesisHandler($this->client, 'stream-name'));

    }//end testConstruct()


    /**
     * Test class implements Handler interface
     *
     * @return void
     */
    public function testInterface(): void
    {
        self::assertInstanceOf(HandlerInterface::class, new KinesisHandler($this->client, 'stream-name'));

    }//end testInterface()


    /**
     * Test Handle when record is not string, then throws exception
     *
     * @return void
     */
    public function testHandleAcceptsOnlyStringRecord(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $record    = $this->getRecord();
        $formatted = ['foo' => 1];
        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->method('format')
            ->with($record)
            ->willReturn($formatted);
        $handler = new KinesisHandler($this->client, 'stream-name');
        $handler->setFormatter($formatter);
        $handler->handle($record);

    }//end testHandleAcceptsOnlyStringRecord()


    /**
     * Test Handle when record is a string, then put record in kinesis stream
     *
     * @return void
     */
    public function testHandle(): void
    {
        $record    = $this->getRecord();
        $formatted = 'formatted string value';
        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->method('format')
            ->with($record)
            ->willReturn($formatted);
        $stream  = 'stream-name';
        $handler = new KinesisHandler($this->client, $stream);
        $handler->setFormatter($formatter);
        $expected = [
            'StreamName'   => $stream,
            'PartitionKey' => $record['channel'],
            'Data'         => $formatted,
        ];
        $this->client->expects(self::once())
            ->method('__call')
            ->with('putRecord', [$expected]);
        $handler->handle($record);

    }//end testHandle()


}//end class
