<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\ForbiddenException;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Exception\ServiceUnavailableException;
use Kommai\Http\Middleware\MiddlewareInterface;
use Kommai\Http\Middleware\MiddlewareTrait;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;
use Kommai\Http\Server;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

class ExampleMiddleware1 implements MiddlewareInterface
{
    use MiddlewareTrait;

    private float $start;
    private float $end;

    public function processRequest(Request $request): Request
    {
        $this->start = microtime(true);
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        $this->end = microtime(true);
        $response->headers['X-Last-Middleware'] = __CLASS__;
        $response->headers['X-Php-Time'] = sprintf('%.2f ms', ($this->end - $this->start) * 1000);
        $response->headers['X-Php-Memory'] = sprintf('%d kb', memory_get_peak_usage() / 1024);
        $response->headers['X-Php-Includes'] = count(get_included_files());
        return $response;
    }
}

class ExampleMiddleware2 implements MiddlewareInterface
{
    use MiddlewareTrait;

    public function processRequest(Request $request): Request
    {
        if (!isset($request->headers['X-Country']) || strtoupper($request->headers['X-Country']) !== 'JAPAN') {
            throw new ForbiddenException('Unavailable in your country');
        }
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        $response->headers['X-Last-Middleware'] = __CLASS__;
        return $response;
    }
}

class ExampleMiddleware3 implements MiddlewareInterface
{
    use MiddlewareTrait;

    public function processRequest(Request $request): Request
    {
        if (true) {
            throw new ServiceUnavailableException('Test');
        }
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        $response->headers['X-Last-Middleware'] = __CLASS__;
        return $response;
    }
}

class ExampleController1 implements ControllerInterface
{
    use ControllerTrait;

    public function hello(Request $request): Response
    {
        return new Response(
            Response::STATUS_OK,
            [],
            'Hello, world!'
        );
    }
}

class ExampleErrorController implements ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response
    {
        return new Response(($thrown instanceof HttpException) ? $thrown->getCode() : Response::STATUS_INTERNAL_SERVER_ERROR, [], $thrown->__toString());
    }
}

$routes = [
    new Route('GET', '/\A\/\z/', new ExampleController1(), 'hello'),
];

$middlewares = [
    new ExampleMiddleware1(),
    new ExampleMiddleware2(),
    new ExampleMiddleware3(),
];

$server = new Server($routes, $middlewares, new ExampleErrorController());
$serverProxy = new Proxy($server);

$fakeRequest = new Request('GET', '/', 'FAKE', [
    'X-Country' => 'Japan',
    //'X-Country' => 'Canada',
]);

var_dump($serverProxy->handleRequest($fakeRequest));
//var_dump($serverProxy->route($fakeRequest));
//$server->run();
