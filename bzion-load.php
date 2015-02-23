<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require 'vendor/autoload.php';
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

DEFINE("DOC_ROOT", dirname(__FILE__));

mb_internal_encoding("UTF-8");
