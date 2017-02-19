<?php

namespace Pugger\Logger;

use Psr\Log\LoggerInterface;
use Rabbit\GenTopicProducer;

class RabbitLogger extends GenTopicProducer implements LoggerInterface
{
    public function emergency($message, array $context = [])
    {
        $this->publish($message, 'logger.emergency', $context);
    }

    public function alert($message, array $context = [])
    {
        $this->publish($message, 'logger.alert', $context);
    }

    public function critical($message, array $context = [])
    {
        $this->publish($message, 'logger.critical', $context);
    }

    public function error($message, array $context = [])
    {
        $this->publish($message, 'logger.error', $context);
    }

    public function warning($message, array $context = [])
    {
        $this->publish($message, 'logger.warning', $context);
    }

    public function notice($message, array $context = [])
    {
        $this->publish($message, 'logger.notice', $context);
    }

    public function info($message, array $context = [])
    {
        $this->publish($message, 'logger.info', $context);
    }

    public function debug($message, array $context = [])
    {
        $this->publish($message, 'logger.debug', $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->publish($message, sprintf('logger.%s', $level), $context);
    }
}
