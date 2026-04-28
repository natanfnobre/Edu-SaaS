<?php

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require CONFIG_PATH . '/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['dbname'],
                $cfg['charset']
            );

            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
            } catch (PDOException $e) {
                throw new RuntimeException('Erro de conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /** Útil em testes para forçar reconexão */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
