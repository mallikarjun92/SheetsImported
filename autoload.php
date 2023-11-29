<?php

spl_autoload_register(function ($class) {
    // Convert class name to a file path
    $file = __DIR__ .'/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        
        require_once $file;
    }

    require_once __DIR__ . '/vendor/autoload.php';
});
    
?>