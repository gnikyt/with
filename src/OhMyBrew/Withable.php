<?php

namespace OhMyBrew;

/**
 * Withable interface for ensuring classes conform.
 */
interface Withable
{
    /**
     * Enter point.
     * The return from here is passed to the working callable method.
     *
     * @return mixed
     */
    public function __enter();

    /**
     * Exit point.
     * The return from enter is passed in here as the first argument.
     * If there is an error, it is passed in as the second argument.
     *
     * @param mixed          $enter The return from the enter point.
     * @param null|Exception $error Possible error from enter or action point.
     *
     * @return bool
     */
    public function __exit($enter, $error);
}
