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

    $return = call_user_func([$object, '__enter']);
    $error  = null;
    try {
        call_user_func_array($callable, [$return]);
    } catch(Exception $e) {
        $error = $e;
    }
    call_user_func_array([$object, '__exit'], [$return, $error]);

    return $object;
}
