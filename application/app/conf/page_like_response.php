<?php

use Pugger\Consumer\PageLikesResponseConsumer;
use Pugger\Command\ConsumePageLikeResponses;

$conf['page_like.response.exchange'] = 'page_like';
$conf['page_like.response.queue']    = 'page_like_response';

$conf['consumer.page_like'] = function($container) {
    return new PageLikesResponseConsumer(
        $container['rabbit.connection'],
        $container['page_like.response.queue'],
        [
            'exchange'    => $container['page_like.response.exchange'],
            'routing_key' => $container['page_like.response.queue']
        ]
    );
};

$conf['command.consumer.page_like'] = function($container) {
    return new ConsumePageLikeResponses(
        $container['consumer.page_like']
    );
};