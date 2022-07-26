<?php

declare(strict_types=1);

namespace Kommai\Http;

use InvalidArgumentException;

class Request
{
    public string $method; // readonly
    public string $url; // readonly
    public string $protocol; // readonly
    public array $headers; // readonly
    public array $cookies; // readonly
    public array $inputs; // readonly
    public array $queries; // readonly
    public array $uploads; // readonly
    public array $env; // readonly

    private static function getMaxPostSize()
    {
    }

    // TODO: work on it
    // do I need to declare this so you can use it anyware?
    private static function parseIniValue(string $name): ?int
    {
        $iniValue = ini_get($name);
        if ($iniValue === false) {
            throw new InvalidArgumentException(sprintf('"%s" directive is unavailable in php.ini', $name));
        }

        return 0;
    }

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

    // NOTE: Do I need this when it has its constructor?
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

        $uploads = [];
        foreach ($_FILES as $key => $file) {
            if (is_array($file['error'])) {
                for ($i = 0; $i < count($file['error']); $i++) {
                    $uploads[$key][$i] = new Upload(
                        $file['name'][$i],
                        $file['type'][$i],
                        $file['tmp_name'][$i],
                        $file['error'][$i],
                        $file['size'][$i],
                    );
                }
                continue;
            }
            $uploads[$key] = Upload::createFromGlobal($file);
        }

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
