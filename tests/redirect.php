<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;
use Kommai\Http\Server;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

class ExampleController implements ControllerInterface
{
    use ControllerTrait;

    public function index(Request $request): Response
    {
        return new Response(Response::STATUS_OK, [], 'Index');
    }

    public function warp(Request $request): Response
    {
        return $this->redirect($request, '/destination');
    }

    public function destination(Request $request): Response
    {
        return new Response(Response::STATUS_OK, [], 'Redirected');
    }
}

class ExampleErrorController implements ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response
    {
        return new Response(($thrown instanceof HttpException) ? $thrown->getCode() : Response::STATUS_INTERNAL_SERVER_ERROR, [], $thrown->__toString());
    }
}

$controller = new ExampleController();
$server = new Server([
    new Route('GET', '/\A\/\z/', $controller, 'index'),
    new Route('GET', '/\A\/warp\z/', $controller, 'warp'),
    new Route('GET', '/\A\/destination\z/', $controller, 'destination'),
], [], new ExampleErrorController());
$serverProxy = new Proxy($server);
var_dump($serverProxy->handleRequest(new Request('GET', '/warp', 'FAKE')));

//$server->run();
