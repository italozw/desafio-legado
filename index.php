<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . "/App/";
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

$config = require __DIR__ . '/Config/aplicativo.php';
define('BASE_URL', $config['base_folder']);
define('BASE_JS', $config['base_js']);



$router = new Core\roteador();
require_once __DIR__ . "/App/Rotas/rotas.php";

$router->envio();