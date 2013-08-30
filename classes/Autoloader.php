<?php

/**
 * Class used to automatically load other classes without having to include
 * each one of them
 */
class AutoLoader {

    static private $classDirectories = array();

    public static function registerDirectory($dir_name) {
        AutoLoader::$classDirectories[] = $dir_name;
    }

    public static function loadClass($class_name) {
        $part = explode('\\', $class_name);

        foreach (AutoLoader::$classDirectories as $directory)
        {
            $doc = rtrim(DOC_ROOT, '/') . '/' . $directory . end($part) . '.php';
            if (file_exists($doc))
            {
                require_once($doc);
                return;
            }
        }
    }

}