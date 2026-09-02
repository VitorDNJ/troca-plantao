<?php
/**
 * Configuração de conexão com o banco de dados (PDO / MySQL-MariaDB)
 * Ajuste os valores abaixo conforme o ambiente (XAMPP local ou servidor da instituição).
 */
return [
    'host'      => getenv('DB_HOST') ?: '127.0.0.1',
    'port'      => getenv('DB_PORT') ?: '3306',
    'database'  => getenv('DB_NAME') ?: 'troca_plantao',
    'username'  => getenv('DB_USER') ?: 'root',
    'password'  => getenv('DB_PASS') ?: '',
    'charset'   => 'utf8mb4',
];
