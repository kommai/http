<?php

declare(strict_types=1);

namespace Kommai\Http\Middleware;

use Kommai\Http\Request;
use Kommai\Http\Response;

interface MiddlewareInterface
{
    public function processRequest(Request $request): Request;
    public function processResponse(Response $response): Response;
}
