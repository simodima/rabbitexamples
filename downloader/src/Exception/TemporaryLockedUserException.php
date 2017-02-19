<?php

namespace Pugger\Exception;

class TemporaryLockedUserException extends \Exception
{
    private $token;
    private $expiration;
    protected $message;

    public function __construct(string $message, string $token, int $expiration)
    {
        $this->token = $token;
        $this->expiration = $expiration;
        $this->message = $message;
        parent::__construct($message, 0, null);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiration()
    {
        return $this->expiration;
    }
}
