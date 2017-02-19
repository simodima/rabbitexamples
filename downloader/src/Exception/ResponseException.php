<?php

namespace Pugger\Exception;

use Psr\Http\Message\ResponseInterface;

class ResponseException extends \Exception
{
    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            $response->getBody(),
            $response->getStatusCode(),
            null
        );
    }

}
