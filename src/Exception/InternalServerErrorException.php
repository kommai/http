<?php

declare(strict_types=1);

namespace Kommai\Http\Exception;

use Kommai\Http\Response;

class InternalServerErrorException extends HttpException
{
    protected const CODE = Response::STATUS_INTERNAL_SERVER_ERROR;
}
