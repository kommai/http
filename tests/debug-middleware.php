<?php

declare(strict_types=1);

use Kommai\Http\Middleware\DebugMiddleware;
use Kommai\Http\Response;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

$response = new Response();
$response->debug('Hello, world!');

$middleware = new DebugMiddleware();
$middlewareProxy = new Proxy($middleware);
var_dump($middlewareProxy->generateDebugHtml($response));
