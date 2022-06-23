<?php

declare(strict_types=1);

use Kommai\Http\Middleware\DebugMiddleware;
use Kommai\Http\Response;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

$response = new Response();
$response->dump('Hello, world!');
$response->dump(['A', 'B', 'C'], true);

$middleware = new DebugMiddleware();
//$middlewareProxy = new Proxy($middleware);
//var_dump($middlewareProxy->generateDebugHtml($response));

$html = file_get_contents(__DIR__ . '/../templates/index.html');
//$html = '<html></html>';
//var_dump($middlewareProxy->injectDebugHtml($html, '<debug></debug>'));

$time = '0.123 ms';
$memory = '123 kb';
$includes = 123;
$dumps = [
    '/path/to/file:123' => var_export(['one', 'two', 'three'], true),
    '/path/to/another/file:123' => var_export(['a', 'b', 'c'], true),
];

$middlewareStaticProxy = new Proxy(DebugMiddleware::class);

// json
$json = json_encode([
    'file' => __FILE__,
    'line' => __LINE__,
]);
//var_dump($middlewareStaticProxy::injectDebugJson($json, $time, $memory, $includes, $dumps));

// html
$html = file_get_contents(__DIR__ . '/../templates/index.html');
var_dump($middlewareStaticProxy::injectDebugHtml($html, $time, $memory, $includes, $dumps));
//var_dump($middlewareStaticProxy::compactHtml($html));