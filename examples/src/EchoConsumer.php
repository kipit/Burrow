<?php
namespace Burrow\Examples;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Burrow\QueueConsumer;

class EchoConsumer implements QueueConsumer
{
    /**
     * @param mixed $message
     * @return mixed|null|void
     */
    public function consume($message)
    {
        echo $message . "\n";
    }
}