<?php

namespace Pugger\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Rabbit\GenConsumer;

class PageLikeRequestConsumer extends GenConsumer
{
    protected function processMessage(AMQPMessage $message)
    {
        return $message;
    }
}
