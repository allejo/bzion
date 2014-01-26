<?php

abstract class TestCase extends PHPUnit_Framework_TestCase {

    /**
     * Wipe all the objects given as parameters
     *
     * @param Model $c,...
     *            The object(s) to call the wipe() method on
     */
    protected function wipe() {
        foreach (func_get_args() as $a) {
            if ($a)
                $a->wipe();
        }
    }
}