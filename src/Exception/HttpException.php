<?php

declare(strict_types=1);

namespace Kommai\Http\Exception;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    protected const CODE = 0;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code ?: static::CODE, $previous);
    }
}
