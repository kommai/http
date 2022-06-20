<?php

declare(strict_types=1);

namespace Kommai\Http;

use BadMethodCallException;
use DomainException;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Exception\NotFoundException;
use Kommai\Http\Middleware\MiddlewareInterface;
use LogicException;
use Throwable;

class Server
{
    /** @var Route[] */
    private array $routes;

    /** @var MiddlewareInterface[] */
    private array $middlewares;

    private ErrorControllerInterface $errorController;

    public function __construct(
        array $routes,
        array $middlewares,
        ErrorControllerInterface $errorController
    ) {
        $this->routes = $routes;
        $this->middlewares = $middlewares;
        $this->errorController = $errorController;
    }

    private function route(Request $request): ?Route
    {
        $routes = array_reverse($this->routes);
        foreach ($routes as $route) {
            if ($route->matches($request->method, $request->url)) {
                return $route;
            }
        }
        return null;
    }

    private function handleRequest(Request $request): Response
    {
        $route = $this->route($request);
        if (!$route) {
            return $this->errorController->error($request, new NotFoundException('No route matched'));
        }
        try {
            try {
                for ($depth = 0; $depth < count($this->middlewares); $depth++) {
                    $request = $this->middlewares[$depth]->processRequest($request);
                }
                if (!method_exists($route->controller, $route->action)) {
                    throw new BadMethodCallException(sprintf('Call to an undefined action "%s" on %s', $route->action, get_class($route->controller)));
                }
                $response = call_user_func([$route->controller, $route->action], $request, $route->params);
            } catch (HttpException $thrown) {
                $response = $this->errorController->error($request, $thrown);
            }
            if ($depth === count($this->middlewares)) {
                $depth--;
            }
            for (; $depth >= 0; $depth--) {
                $response = $this->middlewares[$depth]->processResponse($response);
            }
            $response->headers['Content-Length'] = strlen($response->body);
            return $response;
        } catch (HttpException $thrown) {
            return $this->errorController->error($request, new LogicException(sprintf('Stupid throw of an %s', HttpException::class), 0, $thrown));
        } catch (Throwable $thrown) {
            return $this->errorController->error($request, $thrown);
        }
    }

    private function sendResponse(Response $response): void
    {
        http_response_code($response->status);
        foreach ($response->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
        foreach ($response->cookies as $cookie) {
            if (!$cookie instanceof Cookie) {
                throw new DomainException('Invalid cookie');
            }
            setcookie($cookie->name, $cookie->value, $cookie->options);
        }
        echo $response->body;
    }

    public function run(): void
    {
        $this->sendResponse($this->handleRequest(Request::createFromGlobals()));
    }
}
