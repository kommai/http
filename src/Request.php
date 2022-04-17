<?php

declare(strict_types=1);

namespace Kommai\Http;

class Request
{
    public string $method; // readonly
    public string $url; // readonly
    public string $protocol; // readonly
    public array $headers; // readonly
    public array $cookies; // readonly
    public array $inputs; // readonly
    public array $queries; // readonly
    public array $uploads; // readonly Array of Upload instances
    public array $env; // readonly

    public function __construct(
        string $method,
        string $url,
        string $protocol,
        array $headers = [],
        array $cookies = [],
        array $inputs = [],
        array $queries = [],
        array $uploads = [],
        array $env = [],
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->protocol = $protocol;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->inputs = $inputs;
        $this->queries = $queries;
        $this->uploads = $uploads;
        $this->env = $env;
    }

    public static function createFromScratch(
        string $method,
        string $url,
        string $protocol,
        // TODO $env here?
        array $headers = [],
        array $cookies = [],
        array $inputs = [],
        array $queries = [],
        array $uploads = [],
        array $env = [],
    ): self {
        return new self(
            $method,
            $url,
            $protocol,
            $headers,
            $cookies,
            $inputs,
            $queries,
            $uploads,
            $env,
        );
    }

    public static function createFromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $url = $_SERVER['REQUEST_URI'];
        $protocol = $_SERVER['SERVER_PROTOCOL'];
        $headers = [];
        foreach (getallheaders() as $key => $value) {
            $headers[ucwords($key, '-')] = $value;
        }
        $cookies = $_COOKIE;
        $inputs = $_POST;
        $queries = $_GET;
        $uploads = $_FILES;
        $env = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0) {
                $env[$key] = $value;
            }
        }
        return new self($method, $url, $protocol, $headers, $cookies, $inputs, $queries, $uploads, $env);
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjax(): bool
    {
        return isset($this->headers['X-Requested-With']) && strtoupper($this->headers['X-Requested-With']) === 'XMLHTTPREQUEST';
    }

    public function isHttps(): bool
    {
        return isset($this->env['HTTPS']) && strtoupper($this->env['HTTPS']) === 'ON';
    }
}
