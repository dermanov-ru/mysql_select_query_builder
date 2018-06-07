<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 01.06.2018
 * Time: 22:55
 */

// Turn off error reporting
error_reporting(1);

session_start();


/*
 * All project classes loader.
 * */
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    
    $path = $_SERVER["DOCUMENT_ROOT"] .'/lib/psr-4/'.$class.'.php';
    
    if (is_readable($path)) {
        require_once $path;
    }
});
