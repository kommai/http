<?php

declare(strict_types=1);

namespace Kommai\Http\Controller\Helper;

use Kommai\Http\Controller\ControllerInterface;

trait HelperTrait
{
    private ?ControllerInterface $controller = null;

    public function help(ControllerInterface $controller): void
    {
        $this->controller = &$controller;
    }
}
