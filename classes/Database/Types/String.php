<?php
class String extends Type {

    protected static $alias = "string";

    public static function getDBType() {
        return "s";
    }

}
