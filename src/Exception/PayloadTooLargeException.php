<?php

declare(strict_types=1);

namespace Kommai\Http\Exception;

use Kommai\Http\Response;

class PayloadTooLargeException extends HttpException
{
    protected const CODE = Response::STATUS_PAYLOAD_TOO_LARGE;
}
