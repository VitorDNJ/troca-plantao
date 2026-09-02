<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Conexão PDO única (singleton) com MySQL/MariaDB usando prepared statements.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Nunca expor detalhes sensíveis de conexão ao usuário final.
                error_log('Erro de conexão com o banco: ' . $e->getMessage());
                http_response_code(500);
                die('Não foi possível conectar ao banco de dados. Verifique config/database.php.');
            }
        }

        return self::$instance;
    }
}
