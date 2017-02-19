<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

$conf['rabbit'] = [
    'host' => getenv('RABBIT_HOST'),
    'port' => '5672',
    'vhost'=> '/',
    'user' => 'guest',
    'password' => 'guest'
];

$conf['rabbit.connection'] = function ($container) {
    return new AMQPStreamConnection(
        $container['rabbit']['host'],
        $container['rabbit']['port'],
        $container['rabbit']['user'],
        $container['rabbit']['password'],
        $container['rabbit']['vhost']
    );
};