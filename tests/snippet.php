<?php

declare(strict_types=1);

$data = 'Heloo, world!';
$data = null;
$data = true;
//var_dump($data instanceof Stringable);
var_dump((string) $data);
var_dump(gettype($data));
