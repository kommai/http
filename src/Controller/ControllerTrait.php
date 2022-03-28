<?php

declare(strict_types=1);

namespace Kommai\Http\Controller;

use Kommai\Http\Request;
use Kommai\Http\Response;

trait ControllerTrait
{
    public function redirect(Request $request, string $url, int $status = Response::STATUS_FOUND): Response
    {
        // TODO: implement this
        $response = new Response();
        return $response;
    }
}
