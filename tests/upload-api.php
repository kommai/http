<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Server;
use Kommai\Http\Upload;
use Kommai\Http\View\JsonView;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

$controller = new class(new JsonView()) implements ControllerInterface
{
    use ControllerTrait;

    public function __construct(
        private JsonView $view,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        //$this->view->data['request'] = get_object_vars($request);
        $this->view->data['uploads'] = $request->uploads;

        foreach ($request->uploads as $key => $upload) {
            if ($upload instanceof Upload) {
                $this->view->data[$key]['mime'] = mime_content_type($upload->temp);
            }
        }

        $response = $this->view->toResponse();
        return $response;
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

$server = new Server([], [], $errorController);
$serverProxy = new Proxy($server);
$request = Request::createFromGlobals();
$response = $controller->__invoke($request);
$serverProxy->sendResponse($response);
