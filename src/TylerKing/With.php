<?php

namespace TylerKing;

use Exception;

function with($object, callable $callable) {
    if (! is_object($object)) {
        throw new Exception(sprintf('"%s" must be a callable object.', $object));
    }

    if (! method_exists($object, '__enter') || ! is_callable([$object, '__enter'])) {
        throw new Exception(sprintf('Class "%s" must have a public __enter() method.', get_class($object)));
    }

    if (! method_exists($object, '__exit') || ! is_callable([$object, '__exit'])) {
        throw new Exception(sprintf('Class "%s" must have a public __exit() method.', get_class($object)));
    }

    $exception   = null;
    $enter_value = $object->__enter();
    try {
        $callable($enter_value);
    } catch(Exception $e) {
        $exception = $e;
    }
    $exit_value = $object->__exit($enter_value, $exception);

    if (! is_bool($exit_value)) {
        throw new Exception(sprintf('Class "%s": __exit() method must return a boolean.', get_class($object)));
    }

    if (false === $exit_value && null !== $exception) {
        throw $exception;
    }

    return $object;
}
