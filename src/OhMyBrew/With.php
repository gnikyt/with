<?php

namespace OhMyBrew;

use Exception;
use OhMyBrew\Withable;

/**
 * The Python-like "with" function.
 *
 * @param object $object The class object to use for "with".
 * @param callable $callable The callable action method performing the work.
 * @return object
 */
function with($object, callable $callable)
{
    if (!$object instanceof Withable) {
        if (!is_object($object)) {
            // Not callable
            throw new Exception(sprintf('"%s" must be a callable object.', $object));
        }

        if (!method_exists($object, '__enter') || !is_callable([$object, '__enter'])) {
            // Not compatible, missing enter
            throw new Exception(sprintf('Class "%s" must have a public __enter() method.', get_class($object)));
        }

        if (!method_exists($object, '__exit') || !is_callable([$object, '__exit'])) {
            // Not compatible, missing exit
            throw new Exception(sprintf('Class "%s" must have a public __exit() method.', get_class($object)));
        }
    }

    $exception = null;
    $enterValue = null;

    // Try to enter the object
    try {
        $enterValue = $object->__enter();
    } catch (Exception $e) {
        $exception = $e;
    }

    if (null === $exception) {
        // No exception yet, lets call the action callable
        try {
            $callable($enterValue);
        } catch (Exception $e) {
            $exception = $e;
        }
    }

    // Now, run the exit of the object
    $exitValue = $object->__exit($enterValue, $exception);

    if (!is_bool($exitValue)) {
        // We need a true or false for the return
        throw new Exception(sprintf('Class "%s": __exit() method must return a boolean.', get_class($object)));
    }

    if (false === $exitValue && null !== $exception) {
        throw $exception;
    }

    return $object;
}
