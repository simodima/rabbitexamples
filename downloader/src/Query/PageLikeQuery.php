<?php

namespace Pugger\Query;

class PageLikeQuery
{
    private $token;
    private $user;

    private function __construct(string $token, string $user)
    {
        $this->token = $token;
        $this->user  = $user;
    }

    public static function fromJsonString(string $json)
    {
        $data = json_decode($json, true);

        return new PageLikeQuery($data['token'], $data['user_id']);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}
