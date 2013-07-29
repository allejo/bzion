<?php

include('bzion-config.php');

function __autoload($class_name)
{
    global $classesDir;

    foreach ($classesDir as $directory)
    {
        if (file_exists( DOC_ROOT . '/' . $directory . $class_name . '.php'))
        {
            require_once ( DOC_ROOT . '/' . $directory . $class_name . '.php');
            return;
        }
    }
}

$db = new Database();
