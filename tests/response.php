<?php

declare(strict_types=1);

use Kommai\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$response = new Response();
$response->debug('Hello, world!');
var_dump($response);
