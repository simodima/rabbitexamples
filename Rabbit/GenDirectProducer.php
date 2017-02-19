<?php

namespace Rabbit;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class GenDirectProducer
{
    private $channel;
    private $exchange;
    private $options;

    private static $defaultPublishOptions = [
        'routing_key' => '',
        'mandatory'   => false,
        'immediate'   => false,
        'create_queue' => false,
    ];

    public function __construct(AMQPStreamConnection $connection, string $exchange, array $options = [])
    {
        $this->channel  = $connection->channel();
        $this->exchange = $exchange;
        $this->options = array_merge(self::$defaultPublishOptions, $options);
        $this->configureChannel();
    }

    private function getArguments()
    {
        return array_filter([
            'x-dead-letter-exchange'    => $this->options['x-dead-letter-exchange'] ?? null,
            'x-dead-letter-routing-key' => $this->options['x-dead-letter-routing-key'] ?? null,
            'x-message-ttl'             => $this->options['x-message-ttl'] ?? null,
        ]);
    }

    private function configureChannel()
    {
        $this->channel->exchange_declare(
            $this->exchange,
            'direct',
            false, // passive: false
            true,  // durable: true -- the exchange will survive server restarts
            false  // auto_delete: false -- the exchange won't be deleted once the channel is closed.
        );

        if ($this->options['create_queue'] === true) {
            $this->channel->queue_declare(
                $this->exchange,
                false,
                true,  // the queue will survive server restarts
                false, // the queue can be accessed in other channels
                false,  // the queue won't be deleted once the channel is closed.
                false, // no wait
                new AMQPTable($this->getArguments())
            );
            $this->channel->queue_bind($this->exchange, $this->exchange, $this->options['routing_key']);
        }
    }

    public function publish(string $messageBody, array $properties = [])
    {
        $this->channel->basic_publish(
            new AMQPMessage($messageBody, $properties),
            $this->exchange,
            $this->options['routing_key'],
            $this->options['mandatory'],
            $this->options['immediate']
        );
    }
}