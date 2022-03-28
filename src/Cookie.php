<?php

declare(strict_types=1);

namespace Kommai\Http;

class Cookie
{
    public string $name;
    public string $value;
    public array $options;

    public function __construct(string $name, string $value, array $options = [])
    {
        $this->name = $name;
        $this->value = $value;
        $this->options = $options;
    }
}
