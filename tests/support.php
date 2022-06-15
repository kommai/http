<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Middleware\MiddlewareInterface;
use Kommai\Http\Middleware\MiddlewareTrait;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;
use Kommai\Http\Server;
use Kommai\Http\Support\SupportInterface;
use Kommai\Http\Support\SupportTrait;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

class ExampleDependency
{
    public mixed $data = null;
}

class ExampleErrorController implements ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response
    {
        return new Response(($thrown instanceof HttpException) ? $thrown->getCode() : Response::STATUS_INTERNAL_SERVER_ERROR, [], $thrown->__toString());
    }
}

class ExampleController implements ControllerInterface
{
    use ControllerTrait;

    public ExampleDependency $dependency;
    private ExampleSupport $support;

    public function __construct(ExampleDependency $dependency, ExampleSupport $support)
    {
        $this->dependency = $dependency;
        $this->support = $support;
    }

    public function hello(Request $request): Response
    {
        var_dump($this->support->getController());

        return new Response(
            Response::STATUS_OK,
            [],
            'Hello, world!',
        );
    }
}

class ExampleMiddleware implements MiddlewareInterface
{
    use MiddlewareTrait;

    public ExampleDependency $dependency;
    private ExampleSupport $support;

    public function __construct(ExampleDependency $dependency, ExampleSupport $support)
    {
        $this->dependency = $dependency;
        $this->support = $support;
    }

    public function processRequest(Request $request): Request
    {
        var_dump($this->support->getMiddleware());
        return $request;
    }
}

class ExampleSupport implements SupportInterface
{
    use SupportTrait;

    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    public function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }
}

$dependency = new ExampleDependency();
$support = new ExampleSupport();
$controller = new ExampleController($dependency, $support);
$middleware = new ExampleMiddleware($dependency, $support);
$server = new Server([
    new Route('GET', '/\A\/\z/', $controller, 'hello'),
], [$middleware], new ExampleErrorController());
$serverProxy = new Proxy($server);

var_dump($serverProxy->handleRequest(new Request('GET', '/', 'FAKE')));
