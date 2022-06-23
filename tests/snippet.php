<?php

declare(strict_types=1);

$class = new class () {
    public function method()
    {
        return __METHOD__;
    }
};

var_dump($class->method());
