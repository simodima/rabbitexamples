<?php

use Pugger\Producer\PageLikeRequestProducer;
use Pugger\Command\PublishRandomMessages;

$conf['page_like.request.exchange'] = 'page_like';
$conf['page_like.request.queue'] = 'page_like_request';

$conf['producer.page_like.download.request'] = function ($container) {
    return new PageLikeRequestProducer(
        $container['rabbit.connection'],
        $container['page_like.request.exchange'],
        [
            'routing_key' => $container['page_like.request.queue']
        ]
    );
};


$conf['command.publisher.random_request'] = function($container) {
    return new PublishRandomMessages(
        $container['producer.page_like.download.request']
    );
};