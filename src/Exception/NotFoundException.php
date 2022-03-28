<?php

declare(strict_types=1);

namespace Kommai\Http\Exception;

use Kommai\Http\Response;

class NotFoundException extends HttpException
{
    protected const CODE = Response::STATUS_NOT_FOUND;
}
