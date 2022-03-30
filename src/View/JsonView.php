<?php

declare(strict_types=1);

namespace Kommai\Http\View;

use Kommai\Http\Response;

class JsonView implements ViewInterface
{
    use ViewTrait;

    public function toResponse(?Response $response = null): Response
    {
        if (is_null($response)) {
            $response = new Response();
        }
        $response->body = json_encode($this->data);
        $response->headers['Content-Type'] = 'application/json; charset=UTF-8';
        $response->headers['Content-Length'] = strlen($response->body);
        return $response;
    }
}
