<?php

declare(strict_types=1);

namespace Kommai\Http\Support;

use BadMethodCallException;
use Kommai\Http\Controller\ControllerInterface;
use Kommai\Http\Middleware\MiddlewareInterface;

trait SupportTrait
{
    private ?ControllerInterface $controllerReference = null;
    private ?MiddlewareInterface $middlewareReference = null;

    public function __get(string $name): mixed
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);

        if ($name === 'controller') {
            if ($this->controllerReference instanceof ControllerInterface) {
                return $this->controllerReference;
            }

            foreach ($traces as $trace) {
                if (($trace['object'] ?? null) instanceof ControllerInterface) {
                    $this->controllerReference = &$trace['object'];
                    return $this->controllerReference;
                }
            }
            throw new BadMethodCallException('The caller is not a controller');
        }

        if ($name === 'middleware') {
            if ($this->middlewareReference instanceof MiddlewareInterface) {
                return $this->middlewareReference;
            }

            foreach ($traces as $trace) {
                if (($trace['object'] ?? null) instanceof MiddlewareInterface) {
                    $this->middlewareReference = &$trace['object'];
                    return $this->middlewareReference;
                }
            }
            throw new BadMethodCallException('The caller is not a middleware');
        }

        // @see https://www.php.net/manual/ja/language.oop5.overloading.php#object.get
        trigger_error(
            'Undefined property via __get(): ' . $name .
                ' in ' . $traces[0]['file'] .
                ' on line ' . $traces[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }
}
