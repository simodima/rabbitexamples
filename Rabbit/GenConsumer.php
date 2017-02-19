<?php

namespace Rabbit;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class GenConsumer
{
    private $channel;
    private $queue;
    private $options;

    private $currentMessage;

    private static $defaultQueueOptions = [
        'consumer_tag' => 'consumer',
        'no_local'     => false,
        'no_ack'       => false,
        'exclusive'    => false,
        'no_wait'      => false,
        'routing_key'  => '',
        'exchange'     => null,
        'exchange_type'=> 'direct'
    ];

    public function __construct(AMQPStreamConnection $connection, string $queue, array $options = [])
    {
        $this->channel = $connection->channel();
        $this->queue   = $queue;
        $this->options = array_merge(self::$defaultQueueOptions, $options);
        $this->configureChannel();
        $this->setUpDeadLetter();
    }

    private function getArguments()
    {
        $args = [
            'x-dead-letter-exchange' => $this->options['x-dead-letter-exchange'] ?? null,
            'x-message-ttl' => $this->options['x-message-ttl'] ?? null,
            'x-dead-letter-routing-key' => $this->options['x-dead-letter-routing-key'] ?? null,
        ];

        $args = array_filter($args);

        return count($args) ? $args : null;
    }

    private function configureChannel()
    {
        $this->channel->queue_declare(
            $this->queue,
            false,
            true,  // the queue will survive server restarts
            false, // the queue can be accessed in other channels
            false,  // the queue won't be deleted once the channel is closed.
            false, // no wait
            new AMQPTable($this->getArguments())
        );

        $exchange = $this->options['exchange'] ?? $this->queue;

        $this->channel->exchange_declare(
            $exchange,
            $this->options['exchange_type'],
            false, // passive: false
            true,  // durable: true -- the exchange will survive server restarts
            false  // auto_delete: false -- the exchange won't be deleted once the channel is closed.
        );

        $this->channel->basic_qos(0, 1, false);

        $this->channel->queue_bind($this->queue, $exchange, $this->options['routing_key']);
    }

    public function ack(AMQPMessage $message)
    {
        $message->delivery_info['channel']->basic_ack(
            $message->delivery_info['delivery_tag']
        );
    }

    public function nack(AMQPMessage $message)
    {
        $message->delivery_info['channel']->basic_nack(
            $message->delivery_info['delivery_tag']
        );
    }

    public function reject(AMQPMessage $message, bool $requeue)
    {
        $message->delivery_info['channel']->basic_reject(
            $message->delivery_info['delivery_tag'],
            $requeue
        );
    }

    private function setCurrentMessage(AMQPMessage $message)
    {
        $this->currentMessage = $message;
    }

    public function consume(int $maxMessages = 0): \Generator
    {
        $consumedMessages = 0;

        $this->channel->basic_consume(
            $this->queue,
            $this->options['consumer_tag'],    // consumer_tag: Consumer identifier
            $this->options['no_local'],        // no_local: Don't receive messages published by this consumer.
            $this->options['no_ack'],          // no_ack: Tells the server if the consumer will acknowledge the messages.
            $this->options['exclusive'],       // exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            $this->options['no_wait'],
            function(AMQPMessage $msg) { $this->setCurrentMessage($msg);}
        );

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
            yield $this->processMessage($this->currentMessage);

            $consumedMessages++;
            if ($maxMessages && $consumedMessages >= $maxMessages) {
                break;
            }
        }
    }

    abstract protected function processMessage(AMQPMessage $message);

    protected function setUpDeadLetter()
    {}
}