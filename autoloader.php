<?php
spl_autoload_register(function ($className) {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

    $baseDir = __DIR__;

    $filePath = $baseDir . DIRECTORY_SEPARATOR . $className . '.php';

    if (file_exists($filePath)) {
        require_once $filePath;
    }
});