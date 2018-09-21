<?php

use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver\DriverFactory;
use Burrow\Handler\HandlerBuilder;
use Burrow\Test\Integration\SleepConsumer;
use Evaneos\Daemon\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPLazyConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

$connection = new AMQPLazyConnection('rabbitmq', '5672','guest', 'guest');
$driver = DriverFactory::getDriver($connection);
$handler = new HandlerBuilder($driver);
$handler->async();
$consumer = new SleepConsumer();
$daemon = new QueueHandlingDaemon($driver, $handler->build($consumer),'queue_test');
$worker = new Worker($daemon);

$logger = new Logger('test', [new StreamHandler(fopen('tests/integration/test.log','w'))]);
$consumer->setLogger($logger);
$daemon->setLogger($logger);
$worker->setLogger($logger);

$worker->run();
