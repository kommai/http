<?php

declare(strict_types=1);

use Kommai\Http\Response;
use Kommai\Http\View\HtmlView;

require_once __DIR__ . '/../vendor/autoload.php';

define('TEMPLATE_FILE', sys_get_temp_dir() . DIRECTORY_SEPARATOR . sprintf('temp_%s.html', uniqid()));

$template = <<<EOT
<html>
<body>
<ul>
<li tal:repeat="cpu cpus"><span tal:replace="cpu/brand">Brand</span> <span tal:replace="cpu/name">Name</span> (<span tal:replace="cpu/arch">arch</span>)</li>
</ul>
</body>
</html>
EOT;
if (file_put_contents(TEMPLATE_FILE, $template) === false) {
    die('Failed to generate a temporary template file.');
}

$tal = new PHPTAL();
$tal->setForceReparse(true);
$view = new HtmlView($tal);

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

//var_dump($view->render(TEMPLATE_FILE)->toResponse());
var_dump($view->render(TEMPLATE_FILE)->toResponse($response));

unlink(TEMPLATE_FILE);
