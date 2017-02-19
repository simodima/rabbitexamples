<?php

namespace Pugger;

use GuzzleHttp\Client as GuzzleClient;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Pugger\Consumer\PageLikeRequestConsumer;
use Pugger\Exception\RateLimitException;
use Pugger\Exception\ResponseException;
use Pugger\Exception\TemporaryLockedUserException;
use Pugger\Producer\DelayRequestProducer;
use Pugger\Producer\PageLikeResponseProducer;
use Predis\Client;
use Pugger\Query\PageLikeQuery;
use Pugger\Repository\LikeRepository;

class Downloader
{
    use LoggerAwareTrait;
    use LoggerTrait;

    private $requestConsumer;
    private $responsePublisher;
    private $delayRequestProducer;
    private $likeRepo;
    private $redis;

    public function __construct(
        PageLikeRequestConsumer  $requestConsumer,
        PageLikeResponseProducer $responsePublisher,
        DelayRequestProducer     $delayRequestProducer,
        LikeRepository           $likeRepository
    )
    {
        $this->requestConsumer = $requestConsumer;
        $this->responsePublisher = $responsePublisher;
        $this->delayRequestProducer = $delayRequestProducer;
        $this->likeRepo = $likeRepository;
    }

    public function start()
    {
        /** @var AMQPMessage $message */
        foreach ($this->requestConsumer->consume() as $message) {
            try {
                $likes = $this->likeRepo->get(PageLikeQuery::fromJsonString($message->body));
                $this->responsePublisher->publish($likes);
            } catch (TemporaryLockedUserException $rateEx) {
                $this->delayRequestProducer->publish($message->body, ['expiration' => $rateEx->getExpiration()]);
            } catch(RateLimitException $tempEx) {
                $this->warning(sprintf('%s - %s', $tempEx->getMessage(), $message->body));
                $this->delayRequestProducer->delayQuery(PageLikeQuery::fromJsonString($message->body), $tempEx->getExpiration());
            } finally {
                $this->requestConsumer->ack($message);
            }
        }
    }

    public function log($level, $message, array $context = array())
    {
        if ($this->logger){
            $this->logger->log($level, $message, $context);
        }
    }
}
