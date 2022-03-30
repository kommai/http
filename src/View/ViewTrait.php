<?php

declare(strict_types=1);

namespace Kommai\Http\View;

use BadMethodCallException;

trait ViewTrait
{
    public array $data = [];
    //private string $rendered;

    /*
    public function __toString(): string
    {
        if (!isset($this->rendered)) {
            throw new BadMethodCallException('Not rendered yet');
        }
        return $this->rendered;
    }
    */
}
