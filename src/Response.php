<?php

declare(strict_types=1);

namespace Kommai\Http;

class Response
{
    public const STATUS_OK = 200;
    public const STATUS_FOUND = 302;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_INTERNAL_SERVER_ERROR = 500;

    public int $status;
    public array $headers;
    /** @var Cookie[] $cookies */
    public array $cookies = [];
    public string $body;

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
}
