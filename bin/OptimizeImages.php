<?php

include dirname(__DIR__) . "/vendor/autoload.php";

define('ROOT_PATH', dirname(__DIR__));

$path = realpath(dirname(__DIR__) . '/static/');

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){

    if ($object->isFile()) {
        $info = getimagesize($name);
        if ($info['mime'] == 'image/png') {
            echo "optimize $name\n";
            system("optipng -o7 " . $name);
        } elseif ($info['mime'] == 'image/jpeg') {
            echo "optimize $name\n";
            system("jpegtran -optimize {$name} {$name}");
        }
    }
}