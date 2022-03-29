<?php

declare(strict_types=1);

namespace Kommai\Http;

use InvalidArgumentException;
use Kommai\Http\Controller\ControllerInterface;

class Route
{
    public string $method;
    public string $pattern;
    public ControllerInterface $controller;
    public string $action;
    public array $params = [];

    private static function validatePattern(string $pattern): bool
    {
        set_error_handler(function () {
        }, E_WARNING);
        $match = preg_match($pattern, '');
        restore_error_handler();
        return $match !== false;
    }

    private static function normalizeUrl(string $url): string
    {
        if (preg_match('/\A\/+\z/', $url) === 1) {
            return '/';
        }
        return rtrim(preg_replace('/\?.*\z/', '', $url), '/');
    }

    public function __construct(string $method, string $pattern, ControllerInterface $controller, string $action)
    {
        $this->method = $method;
        if (!self::validatePattern($pattern)) {
            throw new InvalidArgumentException('Invalid regular expression pattern');
        }
        $this->pattern = $pattern;
        $this->controller = $controller;
        $this->action = $action;
    }

    public function __toString(): string
    {
        return sprintf('%s %s -> %s::%s', $this->method, $this->pattern, get_class($this->controller), $this->action);
    }

    public function matches(string $method, string $url): bool
    {
        if ($this->method !== '*' && strcasecmp($this->method, $method) !== 0) {
            return false;
        }
        //$match = preg_match($this->pattern, rtrim(preg_replace('/\?.*\z/', '', $url), '/'), $this->params);
        $match = preg_match($this->pattern, self::normalizeUrl($url), $this->params);
        array_shift($this->params);
        return $match === 1;
    }
}
