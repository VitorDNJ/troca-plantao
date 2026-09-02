<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

// Autoload simples PSR-4 (App\ => app/)
spl_autoload_register(function (string $class) use ($root) {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $root . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../Helpers/helpers.php';

$appConfig = require $root . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

error_reporting(E_ALL);
ini_set('display_errors', '0'); // nunca expor erros em produção
ini_set('log_errors', '1');
ini_set('error_log', $root . '/storage/logs/php_errors.log');

\App\Core\Session::start();
