<?php

namespace Burrow\Test\Integration;

use Assert\Assertion;
use Burrow\Driver\DriverFactory;
use Burrow\Publisher\AsyncPublisher;
use Burrow\QueuePublisher;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use Symfony\Component\Process\Process;

class StopConsumingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueuePublisher
     */
    private $publisher;

    public function setUp()
    {
        $connection = new AMQPLazyConnection('rabbitmq', '5672','guest', 'guest');
        $driver = DriverFactory::getDriver($connection);
        $this->publisher = new AsyncPublisher($driver, 'exchange_test');
        $connection->channel()->queue_purge('queue_test');
        file_put_contents('tests/integration/test.log', '');
    }

    /**
     * @test
     */
    public function it_stops_gracefully()
    {
        $stop = false;
        $stopedgracefully = false;

        $this->publisher->publish('5');

        $worker = new Process('exec php tests/integration/sleep.php', null, null, null, 20);
        $logWatcher = new Process('exec tail -f tests/integration/test.log', null, null,null, 20);
        $logWatcher->start();
        $worker->start();

        echo system('ps aux www f') . "\n";

        $logWatcher->wait(function ($type, $buffer) use (&$stop, $worker, &$stopedgracefully, $logWatcher) {
            if (strpos($buffer, SleepConsumer::START_CONSUME)) {
                $stop = true;
                $worker->signal(15);
            }

            if (true === $stop && strpos($buffer,SleepConsumer::END_CONSUME)) {
                $stopedgracefully = true;
                $logWatcher->stop();
            }
        });

        echo system('ps aux www f') . "\n";

        Assertion::true($stopedgracefully);
    }
}
