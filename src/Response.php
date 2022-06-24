<?php

declare(strict_types=1);

namespace Kommai\Http;

use Stringable;

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
    public array $dumps = []; // readonly

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

    public function dump(mixed $data, bool $detail = false): self
    {
        $trace = (debug_backtrace(2))[0];
        $this->dumps[sprintf('%s:%d', $trace['file'], $trace['line'])] = $detail ? var_export($data, true) : match (gettype($data)) {
            'boolean' => $data ? 'true' : 'false',
            'integer', 'double', 'string' => $data,
            'array' => sprintf('array (%d)', count($data)),
            'object' => get_class($data),
            'NULL' => 'null',
            default => gettype($data),
        };

        return $this;
    }
}
