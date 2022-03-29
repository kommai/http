<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Route;

require_once __DIR__ . '/../vendor/autoload.php';

class DummyController implements ControllerInterface
{
    use ControllerTrait;

    public function whatever(Request $request): Response
    {
        return new Response(Response::STATUS_OK, [], 'It works!');
    }
}

$controller = new DummyController();

$routes = [
    new Route('GET', '/\A\/\z/', $controller, 'whatever'),
    new Route('GET', '/\A\/foo\z/', $controller, 'whatever'),
];

$method = 'GET';
$url = '/';
$url = '/foo/?bar=123///';

echo sprintf('Matching [%s %s]...', $method, $url), PHP_EOL;
foreach ($routes as $route) {
    /** @var Route $route */
    if ($route->matches($method, $url)) {
        echo 'Route matched!', PHP_EOL;
        //var_dump($route);
        echo $route->__toString(), PHP_EOL;
        exit;
    }
}
echo 'No route matched.', PHP_EOL;