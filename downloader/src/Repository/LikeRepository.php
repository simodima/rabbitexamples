<?php

namespace Pugger\Repository;

use GuzzleHttp\Client;
use Predis\Client as Redis;
use Pugger\Exception\RateLimitException;
use Pugger\Exception\ResponseException;
use Pugger\Exception\TemporaryLockedUserException;
use Pugger\Query\PageLikeQuery;

class LikeRepository
{
    private $client;
    private $redis;

    public function __construct(string $endpoint, Redis $redis)
    {
        $this->client = new Client(['base_uri' => $endpoint, 'http_errors' => false]);
        $this->redis  = $redis;
    }

    public function get(PageLikeQuery $pageLikeQuery)
    {
        $token = $pageLikeQuery->getToken();
        $user  = $pageLikeQuery->getUser();

        $expiration = $this->redis->pttl($token);

        if ($expiration > 0) {
            throw new TemporaryLockedUserException(
                sprintf('The user is still in rate limit period, remaining %s mSecs ', $expiration),
                $token,
                $expiration
            );
        }

        $res = $this->client->request('GET', $user.'/likes?access_token='.$token);

        if ($res->getStatusCode() == 200) {
            return $res->getBody();
        }

        if ($res->getStatusCode() == 429) {
            $minExpiration      = 5000;
            $nextRetryInSecs    = (int) ceil($res->getHeader('X-RateLimit-PathReset')[0] / 1000);
            $nextRetry          = \DateTime::createFromFormat('U', $nextRetryInSecs);
            $now                = new \DateTime('now');
            $expiration         = $nextRetry->diff($now)->s * 1000;
            $expiration         = $expiration <= 0 ? $minExpiration : $expiration;

            throw new RateLimitException(
                sprintf('Rate limit reached, you should wait %s mSecs', $expiration),
                $token,
                $expiration
            );
        }

        throw new ResponseException($res);
    }
}
