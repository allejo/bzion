<?php

if (!@include("bzion-config.php")) {
    header("Location: install.php");
    die();
}

function __autoload($class_name)
{
    global $classesDir;
    $part = explode('\\', $class_name);

    foreach ($classesDir as $directory)
    {
        $doc = rtrim(DOC_ROOT, '/') . '/' . $directory . end($part) . '.php';
        if (file_exists($doc))
        {
            require_once($doc);
            return;
        }
    }
}

$db = new Database();
