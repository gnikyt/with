<?php

namespace TylerKing\With;

use Exception;

function with($object, callable $callable) {
    if (! method_exists($object, '__enter')) {
        throw new Exception(sprintf('Class "%s" must have a __enter() method.', get_class($object)));
    }

    if (! method_exists($object, '__exit')) {
        throw new Exception(sprintf('Class "%s" must have a __exit() method.', get_class($object)));
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
