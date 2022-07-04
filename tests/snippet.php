<?php

declare(strict_types=1);

$class = new class () {
    public function __invoke()
    {
        return 'You just invoked the class!';
    }
};

var_dump(is_callable($class));
var_dump(call_user_func($class));
