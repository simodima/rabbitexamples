<?php

namespace Pugger\Producer;

use Rabbit\GenDirectProducer;

class PageLikeRequestProducer extends GenDirectProducer
{
    public function requestPageLikes(string $userId, string $token, string $page)
    {
        $messageBody = json_encode([
            'user_id'   => $userId,
            'token'     => $token,
            'page'      => $page
        ]);

        $this->publish($messageBody);
    }
}
