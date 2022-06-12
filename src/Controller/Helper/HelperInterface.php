<?php

declare(strict_types=1);

namespace Kommai\Http\Controller\Helper;

interface HelperInterface
{
    public function __get(string $name): mixed;
}
