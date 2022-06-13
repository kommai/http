<?php

declare(strict_types=1);

namespace Kommai\Http\Controller\Helper;

use BadMethodCallException;
use Kommai\Http\Controller\ControllerInterface;

trait HelperTrait
{
    private ?ControllerInterface $controllerReference = null;

    public function __get(string $name): mixed
    {
        if ($name === 'controller') {
            if ($this->controllerReference instanceof ControllerInterface) {
                return $this->controllerReference;
            }

            $traces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
            foreach ($traces as $trace) {
                if (($trace['object'] ?? null) instanceof ControllerInterface) {
                    $this->controllerReference = &$trace['object'];
                    return $this->controllerReference;
                }
            }
            throw new BadMethodCallException('The caller is not a controller');
        }

        // @see https://www.php.net/manual/ja/language.oop5.overloading.php#object.get
        $traces = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
                ' in ' . $traces[0]['file'] .
                ' on line ' . $traces[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }
}
