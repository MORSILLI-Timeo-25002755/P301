<?php

require_once __DIR__ . '/page.php';

spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../../' . $classPath . '.php';

    if (file_exists($file)) {
        require $file;
        return;
    }

    // Fallback: check lowercase first directory (e.g. Controllers/ -> controllers/)
    $parts = explode(DIRECTORY_SEPARATOR, $classPath);
    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]);
        $altFile = __DIR__ . '/../../' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
        if (file_exists($altFile)) {
            require $altFile;
            return;
        }
    }

    require $file;
});