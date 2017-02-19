<?php

use Predis\Client;

$conf['redis'] = function() {
    $conn = sprintf('tcp://%s:%s', getenv('REDIS_HOST'), getenv('REDIS_PORT'));
    $cli = new Client($conn);

    return $cli;
};