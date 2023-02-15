<?php

namespace Overtrue\LaravelQcloudCaptcha\Exceptions;

use Throwable;

class InvalidTicketException extends Exception
{
    public string $contents;

    public array $response;

    public function __construct(string $ticket, array $response, Throwable $previous = null)
    {
        $this->contents = $ticket;
        $this->response = $response;

        parent::__construct('Invalid captcha ticket.', 422, $previous);
    }
}
