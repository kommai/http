<?php

declare(strict_types=1);

namespace Kommai\Http;

class Response
{
    public const STATUS_OK = 200;
    public const STATUS_FOUND = 302;
    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_UNAUTHORIZED = 401;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_INTERNAL_SERVER_ERROR = 500;
    public const STATUS_SERVICE_UNAVAILABLE = 503;

    public int $status;
    public array $headers;
    /** @var Cookie[] $cookies */
    public array $cookies = [];
    public string $body;
    public array $debug = []; // readonly

    public function __construct(int $status = self::STATUS_OK, array $headers = [], string $body = '')
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public function debug(mixed $data): self
    {
        $trace = (debug_backtrace(2))[0];
        //var_dump($trace);
        $this->debug[] = [
            'file' => $trace['file'],
            'line' => $trace['line'],
            'dump' => print_r($data, true),
        ];

        return $this;
    }
}
