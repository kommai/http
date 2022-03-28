<?php

declare(strict_types=1);

namespace Kommai\Http\Middleware;

use Kommai\Http\Request;
use Kommai\Http\Response;

trait MiddlewareTrait
{
    public function processRequest(Request $request): Request
    {
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        return $response;
    }
}
