<?php
class Integer extends Type {

    protected static $alias = "integer";

    public static function getDBType() {
        return "i";
    }

}
