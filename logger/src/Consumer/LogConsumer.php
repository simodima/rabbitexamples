<?php

namespace Pugger\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Rabbit\GenConsumer;

class LogConsumer extends GenConsumer
{
    protected function processMessage(AMQPMessage $message)
    {
        return $message;
    }
}
