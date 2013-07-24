<?php

    include('bzion-config.php');

    $classesDir = array (
        ROOT_DIR . 'classes/'
    );

    function __autoload($class_name)
    {
        global $classesDir;

        foreach ($classesDir as $directory)
        {
            if (file_exists($directory . $class_name . '.php'))
            {
                require_once ($directory . $class_name . '.php');
                return;
            }
        }
    }

    $mysqli = new DatabaseConnection();