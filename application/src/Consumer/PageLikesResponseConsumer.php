<?php

namespace Pugger\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Rabbit\GenConsumer;

class PageLikesResponseConsumer extends GenConsumer
{
    protected function processMessage(AMQPMessage $message)
    {
        return $message;
    }
}
