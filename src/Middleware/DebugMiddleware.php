<?php

declare(strict_types=1);

namespace Kommai\Http\Middleware;

use Kommai\Http\Request;
use Kommai\Http\Response;

class DebugMiddleware implements MiddlewareInterface
{
    use MiddlewareTrait;

    public function processResponse(Response $response): Response
    {

        return $response;
    }

    private function generateDebugHtml(Response $response): string
    {
        return '<div id="kommai-debug"></div>';
    }

    private function injectDebugHtml(string $html): string
    {
        return $html;
    }
}
