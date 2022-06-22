<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Middleware\DebugMiddleware;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;
use Kommai\Http\Server;
use Kommai\Http\View\HtmlView;
use Kommai\Http\View\JsonView;

require_once __DIR__ . '/../vendor/autoload.php';

$controller = new class(new HtmlView(new PHPTAL()), new JsonView()) implements ControllerInterface
{
    use ControllerTrait;

    private HtmlView $html;
    private JsonView $json;

    public function __construct(HtmlView $html, JsonView $json)
    {
        $this->html = $html;
        $this->json = $json;
    }

    public function index(Request $request): Response
    {
        //throw new RuntimeException('Test');
        $response = new Response();
        $response->debug('This is the debug!');
        $response->debug($request->env, true);
        //$api = true;
        if (isset($api) && $api) {
            $this->json->data = get_object_vars($request);
            return $this->json->toResponse($response);
        } else {
            $this->html->data = get_object_vars($request);
            return $this->html->render(__DIR__ . '/../templates/index.html')->toResponse($response);
        }
    }
};

$errorController = new class implements ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response
    {
        $response = new Response();
        $response->status = $thrown instanceof HttpException ? $thrown->getCode() : 500;
        $response->body = sprintf('<html><body><h1>%s</h1><pre>%s</pre></body></html>', get_class($thrown), $thrown->__toString());
        return $response;
    }
};

$server = new Server(
    [
        new Route('GET', '/\A\/\z/', $controller, 'index'),
    ],
    [
        new DebugMiddleware(),
    ],
    $errorController,
);
$server->run();
