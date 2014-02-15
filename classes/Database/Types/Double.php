<?php
class Double extends Type {

    protected static $alias = "double";

    public static function getDBType() {
        return "d";
    }

}
