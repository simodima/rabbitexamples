<?php

namespace Pugger\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Predis\Client;
use Pugger\Query\PageLikeQuery;
use Rabbit\GenDirectProducer;

class DelayRequestProducer extends GenDirectProducer
{
    private $redis;

    public function __construct(AMQPStreamConnection $connection, $exchange, array $options, Client $redis)
    {
        parent::__construct($connection, $exchange, $options);
        $this->redis = $redis;
    }

    public function delayQuery(PageLikeQuery $pageLikeQuery, $delayInMSecs)
    {
        $this->redis->set($pageLikeQuery->getToken(), 'locked', 'PX', $delayInMSecs);
        $this->publish(
            json_encode([
                'user_id' => $pageLikeQuery->getUser(),
                'token'   => $pageLikeQuery->getToken(),
            ]),
            [
                'expiration' => $delayInMSecs
            ]
        );
    }
}
