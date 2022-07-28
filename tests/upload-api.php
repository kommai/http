<?php

declare(strict_types=1);

use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Controller\ControllerTrait;
use Kommai\Http\Controller\ErrorControllerInterface;
use Kommai\Http\Exception\HttpException;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\Server;
use Kommai\Http\Upload;
use Kommai\Http\View\JsonView;
use Kommai\TestKit\Proxy;
use Kommai\Validation\UploadValidationTrait;
use Kommai\Validation\Validation;
use Kommai\Validation\ValidationInterface;

require_once __DIR__ . '/../vendor/autoload.php';

define('UPLOAD_DIR', __DIR__ . '/../storage/uploads');

$validation = new class() extends Validation implements ValidationInterface
{
    use UploadValidationTrait;

    public function __invoke(array $data): array
    {
        foreach (['file', 'files'] as $key) {
            $this->smallEnough($key, 'The uploaded file exceeds the upload_max_filesize directive in php.ini');
            $this->completed($key, 'The uploaded file was only partially uploaded');
            $this->filled($key, 'No file was uploaded');
            $this->written($key, 'Missing a temporary folder | Failed to write file to disk');
            $this->smaller($key, 1000000, 'Too large');
            $this->type($key, ['image/jpeg', 'image/png'], 'Unsupported type');
        }
        return parent::__invoke($data);
    }
};
//var_dump($validation);

$controller = new class(new JsonView(), $validation) implements ControllerInterface
{
    use ControllerTrait;

    public function __construct(
        private JsonView $view,
        private ValidationInterface $validation,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        //var_dump($this->validation);
        $errors = $this->validation->__invoke($request->uploads);
        if ($errors) {
            $this->view->data['errors'] = $errors;
            return $this->view->toResponse();
        }

        $this->view->data['uploads'] = $request->uploads;
        foreach ($request->uploads as $upload) {
            // NOTE: using raw $upload->name is strongly discouraged
            if (is_array($upload)) {
                /** @var Upload $item */
                foreach ($upload as $item) {
                    $item->save(UPLOAD_DIR . DIRECTORY_SEPARATOR . $item->name);
                }
            } else {
                /** @var Upload $upload */
                $upload->save(UPLOAD_DIR . DIRECTORY_SEPARATOR . $upload->name);
            }
        }

        $response = $this->view->toResponse();
        //$response->body = sprintf('<pre>%s</pre>', var_export($request->uploads, true));
        return $response;
    }
};

$errorController = new class implements ErrorControllerInterface
{
    public function error(Request $request, Throwable $thrown): Response
    {
        $response = new Response();
        $response->status = $thrown instanceof HttpException ? $thrown->getCode() : 500;
        $response->body = sprintf('<html><body><h1>%s</h1><pre>%s</pre></body></html>', get_class($thrown), $thrown->__toString());
        return $response;
    }
};

$server = new Server([], [], $errorController);
$serverProxy = new Proxy($server);
$request = Request::createFromGlobals();
$response = $controller->__invoke($request);
$serverProxy->sendResponse($response);
