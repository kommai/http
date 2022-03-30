<?php

declare(strict_types=1);

use Kommai\Http\Response;
use Kommai\Http\View\JsonView;

require_once __DIR__ . '/../vendor/autoload.php';

$view = new JsonView();

$view->data['cpus'][] = [
    'name' => 'Core',
    'brand' => 'Intel',
    'arch' => 'x64',
];
$view->data['cpus'][] = [
    'name' => 'Ryzen',
    'brand' => 'AMD',
    'arch' => 'x64',
];
$view->data['cpus'][] = [
    'name' => 'Cortex',
    'brand' => 'Broadcom',
    'arch' => 'ARM',
];

$response = new Response(Response::STATUS_OK, [
    'X-Some-Header' => 'some header value',
]);

//var_dump($view->toResponse());
var_dump($view->toResponse($response));