<?php

use Pugger\Consumer\LogConsumer;
use Pugger\Command\Logger;

$conf['logger.exchange'] = 'logger';
$conf['remote_api.endpoint'] = getenv('REMOTE_API');

$conf['logger'] = function($container) {
    return function(string $level) use ($container) {
        $queue = $container['logger.exchange'].'.'.$level;
        return new LogConsumer(
            $container['rabbit.connection'],
            $queue,
            [
                'routing_key'   => '*.'.$level,
                'exchange_type' => 'topic',
                'exchange'      => $container['logger.exchange']
            ]
        );
    };
};

$conf['logger.command'] = function($container) {
    return new Logger($container['logger']);
};