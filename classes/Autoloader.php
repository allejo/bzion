<?php

/**
 * Class used to automatically load other classes without having to include
 * each one of them
 */
class AutoLoader {

    /**
     * The list of directories where classes are saved
     * @var array
     */
    static private $classDirectories = array();

    /**
     * Add a directory to the list of directories where class files are located
     *
     * @param string $dir_name The path to the directory
     */
    public static function registerDirectory($dir_name) {
        // Add a trailing slash to the directory name
        $dir_name = rtrim($dir_name, "/") . "/";

        AutoLoader::$classDirectories[] = $dir_name;
    }

    /**
     * Include the file where a class is located
     *
     * @param string $class_name The name of the class
     */
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
