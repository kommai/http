<?php

declare(strict_types=1);

namespace Kommai\Http;

use BadMethodCallException;
use DomainException;
use InvalidArgumentException;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\BadRequestException;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Exception\NotFoundException;
use Kommai\Http\Exception\PayloadTooLargeException;
use Kommai\Http\Middleware\MiddlewareInterface;
use Kommai\Http\Middleware\MiddlewareTrait;
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

        // TODO: is this good to be here?
        $essentialMiddleware = new class implements MiddlewareInterface
        {
            use MiddlewareTrait;

            // NOTE: move to elsewhere if something else needs this
            private static function iniValueToBytes(string $name): int
            {
                $iniValue = ini_get($name);
                if ($iniValue === false) {
                    throw new InvalidArgumentException(sprintf('"%s" directive is unavailable in php.ini', $name));
                }

                // @see https://www.php.net/manual/ja/function.ini-get
                return match (strtoupper(substr(trim($iniValue), -1))) {
                    'K' => (int) $iniValue * 1024,
                    'M' => (int) $iniValue * 1024 * 1024,
                    'G' => (int) $iniValue * 1024 * 1024 * 1024,
                    default => (int) $iniValue,
                };
            }

            public function processRequest(Request $request): Request
            {
                //throw new HttpException('test');
                if ($request->isPost() && $request->headers['Content-Length'] > self::iniValueToBytes('post_max_size')) {
                    throw new PayloadTooLargeException('');
                }

                return $request;
            }
        };
        array_unshift($this->middlewares, $essentialMiddleware);

        try {
            try {
                for ($depth = 0; $depth < count($this->middlewares); $depth++) {
                    $request = $this->middlewares[$depth]->processRequest($request);
                }
                if (isset($route->action) && !method_exists($route->controller, $route->action)) {
                    throw new BadMethodCallException(sprintf('Call to an undefined action "%s" on %s', $route->action, get_class($route->controller)));
                }
                if (!isset($route->action) && !is_callable($route->controller)) {
                    throw new BadMethodCallException(sprintf('%s is not callable', get_class($route->controller)));
                }
                $response = call_user_func(isset($route->action) ? [$route->controller, $route->action] : $route->controller, $request, $route->params);
            } catch (HttpException $thrown) {
                $response = $this->errorController->error($request, $thrown);
            }
            if ($depth === count($this->middlewares)) {
                $depth--;
            }
            for (; $depth >= 0; $depth--) {
                $response = $this->middlewares[$depth]->processResponse($response);
            }
            // TODO: this could be in essential middleware's processResponse?
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
        foreach ($response->cookies as $cookie) {
            if (!$cookie instanceof Cookie) {
                throw new DomainException('Invalid cookie');
            }
            setcookie($cookie->name, $cookie->value, $cookie->options);
        }
        foreach ($response->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
        echo $response->body;
    }

    public function run(): void
    {
        $this->sendResponse($this->handleRequest(Request::createFromGlobals()));
    }
}
