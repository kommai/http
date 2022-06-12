<?php

declare(strict_types=1);

namespace Kommai\Http\Controller\Helper;

use BadMethodCallException;
use Kommai\Http\Controller\ControllerInterface;

trait HelperTrait
{
    /*
    private ?ControllerInterface $controller = null;

    public function help(ControllerInterface $controller): void
    {
        $this->controller = &$controller;
    }
    */

    private function getController(): ControllerInterface
    {
        $backtraces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        //var_dump($backtraces);
        if (!$backtraces[2]['object'] ?? null instanceof ControllerInterface) {
            throw new BadMethodCallException('The caller is not a controller');
        }
        return $backtraces[2]['object'];
    }

    public function __get(string $name)
    {
        /*
        echo "Getting '$name'\n";
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        */
        if ($name === 'controller') {
            //return $this->getController();
            // TODO: you SHOULD actually SEARCH for the first controller; DO NOY use a magic number!
            // TODO: you would want to cache the controller
            $backtraces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
            var_dump($backtraces);
            if (!($backtraces[2]['object'] ?? null instanceof ControllerInterface)) {
                throw new BadMethodCallException('The caller is not a controller');
            }
            return $backtraces[2]['object'];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }
}
