<?php

namespace Rabbit;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class GenTopicProducer
{
    private $channel;
    private $exchange;
    private $publishOptions;

    private static $defaultPublishOptions = [
        'mandatory'   => false,
        'immediate'   => false,
        'ticket'      => null
    ];

    public function __construct(AMQPStreamConnection $connection, string $exchange, array $options = [])
    {
        $this->channel  = $connection->channel();
        $this->exchange = $exchange;
        $this->publishOptions = array_merge(self::$defaultPublishOptions, $options);
        $this->configureChannel();
    }

    private function configureChannel()
    {
        $this->channel->exchange_declare(
            $this->exchange,
            'topic',
            false, // passive: false
            true,  // durable: true -- the exchange will survive server restarts
            false  // auto_delete: false -- the exchange won't be deleted once the channel is closed.
        );
    }

    public function publish(string $messageBody, string $key, array $properties = [])
    {
        $this->channel->basic_publish(
            new AMQPMessage($messageBody, $properties),
            $this->exchange,
            $key,
            $this->publishOptions['mandatory'],
            $this->publishOptions['immediate'],
            $this->publishOptions['ticket']
        );
    }
}