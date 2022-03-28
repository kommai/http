<?php

declare(strict_types=1);

namespace Kommai\Http\Controller;

use Kommai\Http\Request;
use Kommai\Http\Response;
use Throwable;

interface ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response;
}
