<?php
abstract class Type {

    protected static $alias;

    public static function getAlias() {
        return static::$alias;
    }

    public static function is($other) {
        return static::$alias === $other::getAlias();
    }

    public static function getDBType() {
        return "i";
    }

    public static function store($content) {
        return $content;
    }

    public static function restore($stored) {
        return $stored;
    }


    public static function String() {
        return new String();
    }
    public static function Integer() {
        return new Integer();
    }
    public static function Double() {
        return new Double();
    }
    public static function DateTime() {
        return new DBDateTime();
    }

    public static function Int() {
        return self::Integer();
    }
    public static function Number() {
        return self::Double();
    }

}
