<?php

declare(strict_types=1);

namespace Kommai\Http\Support;

interface SupportInterface
{
    public function __get(string $name): mixed;
}
