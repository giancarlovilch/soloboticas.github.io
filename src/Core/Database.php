<?php

/**
 * El Tanque Matriz de Suministro (Clase Database)
 * Implementa el patrón Singleton para que toda la obra use la misma tubería.
 */
class Database
{
    private static ?PDO $connection = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Localización de los planos de configuración[cite: 7]
            $configPath = __DIR__ . '/../../config/database.php';

            if (!file_exists($configPath)) {
                throw new Exception("No se encontró el archivo de configuración de base de datos.");
            }

            $config = require $configPath;

            if (!is_array($config)) {
                throw new Exception("La configuración de base de datos no es válida.");
            }

            // Ensamblado del DSN para MySQL[cite: 7]
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        // Refuerzo antisísmico contra Inyección SQL[cite: 7]
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Error de conexión a la base de datos", 0, $e);
            }
        }

        return self::$connection;
    }
}