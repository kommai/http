<?php

declare(strict_types=1);

use Kommai\Http\Request;
use Kommai\TestKit\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();
echo sprintf('<pre>%s</pre>', var_export($request, true));
echo '<hr>';
echo sprintf('<pre>%s</pre>', var_export($_SERVER, true));
