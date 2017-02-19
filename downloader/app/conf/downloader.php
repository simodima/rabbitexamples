<?php

use Pugger\Downloader;
use Pugger\Consumer\PageLikeRequestConsumer;
use Pugger\Producer\PageLikeResponseProducer;
use Pugger\Command\DownloaderCommand;
use Pugger\Logger\RabbitLogger;
use Pugger\Producer\DelayRequestProducer;

$conf['logger.exchange'] = 'logger';
$conf['remote_api.endpoint'] = getenv('REMOTE_API');

$conf['page_like.request.exchange']             = 'page_like';
$conf['page_like.request.queue']                = 'page_like_request';
$conf['page_like.request.dead_letter_exchange'] = 'delayed_request';
$conf['page_like.response.exchange']            = 'page_like';
$conf['page_like.response.queue']               = 'page_like_response';
$conf['messages.delayed.ttl.milliseconds']      = 60 * 1000;

$conf['service.request.consumer'] = function($container) {
    return new PageLikeRequestConsumer(
        $container['rabbit.connection'],
        $container['page_like.request.queue'],
        [
            'x-dead-letter-exchange'    => $container['page_like.request.dead_letter_exchange'],
            'routing_key'               => $container['page_like.request.queue'],
            'exchange'                  => $container['page_like.request.exchange'],
        ]
    );
};

$conf['service.delay_request.producer'] = function ($container) {
    return new DelayRequestProducer(
        $container['rabbit.connection'],
        $container['page_like.request.dead_letter_exchange'],
        [
            'x-dead-letter-exchange'    => $container['page_like.request.exchange'],
            'x-dead-letter-routing-key' => $container['page_like.request.queue'],
            'routing_key'               => $container['page_like.request.exchange'],
            'create_queue'              => true
        ],
        $container['redis']
    );
};

$conf['service.response.producer'] = function($container) {
    return new PageLikeResponseProducer(
        $container['rabbit.connection'],
        $container['page_like.response.exchange'],
        [
            'routing_key' => $container['page_like.response.queue']
        ]
    );
};

$conf['logger'] = function ($container) {
    return new RabbitLogger(
        $container['rabbit.connection'],
        $container['logger.exchange']
    );
};

$conf['service.downloader'] = function ($container) {
    $downloader = new Downloader(
        $container['service.request.consumer'],
        $container['service.response.producer'],
        $container['service.delay_request.producer'],
        $container['page_like.repository']
    );

    $downloader->setLogger($container['logger']);

    return $downloader;
};

$conf['command.downloader'] = function ($container) {
    return new DownloaderCommand(
        $container['service.downloader']
    );
};