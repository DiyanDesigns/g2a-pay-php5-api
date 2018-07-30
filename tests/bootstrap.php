<?php

require_once __DIR__.'/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (file_exists(__DIR__.'/../src/'.str_replace('\\', '/', $class).'.php')) {
        require __DIR__.'/../src/'.str_replace('\\', '/', $class).'.php';
    }
});
