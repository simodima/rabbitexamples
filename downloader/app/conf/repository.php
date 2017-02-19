<?php

use Pugger\Repository\LikeRepository;

$conf['page_like.repository'] = function ($container) {
    return new LikeRepository(
        $container['remote_api.endpoint'],
        $container['redis']
    );
};