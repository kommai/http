<?php

declare(strict_types=1);

namespace Kommai\Http\Controller\Helper;

use Kommai\Http\Controller\ControllerInterface;

interface HelperInterface
{
    public function help(ControllerInterface $controller): void;
}
