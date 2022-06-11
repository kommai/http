<?php

declare(strict_types=1);

use ExampleController as GlobalExampleController;
use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Controller\Helper\HelperInterface;
use Kommai\Http\Controller\Helper\HelperTrait;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;
use Kommai\Http\Server;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

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

    public string $message = 'Hello, world!';
    private ExampleHelper $helper;

    public function __construct(ExampleHelper $helper)
    {
        $this->helper = $helper;
        $this->helper->help($this);
    }

    public function hello(Request $request): Response
    {
        $this->helper->goBig();

        return new Response(
            Response::STATUS_OK,
            [],
            $this->message,
        );
    }
}

class ExampleHelper implements HelperInterface
{
    use HelperTrait;

    public function goBig(): void
    {
        var_dump($this->controller);
        $this->controller->message = strtoupper($this->controller->message);
    }
}

$helper = new ExampleHelper();
$controller = new ExampleController($helper);
var_dump($controller);
$server = new Server([
    new Route('GET', '/\A\/\z/', $controller, 'hello'),
], [], new ExampleErrorController());
$serverProxy = new Proxy($server);

var_dump($serverProxy->handleRequest(new Request('GET', '/', 'FAKE')));
