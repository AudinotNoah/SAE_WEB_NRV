<?php

namespace iutnc\nrv\repository;

use Exception;
use PDO;


// le code pour __construct et getInstance  vient du cours
class DeefyRepository {

    private PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                self::$config['dsn'],
                self::$config['username'],
                self::$config['password']
            );

        } catch (PDOException $e) {
            echo 'Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    public static function getInstance(): DeefyRepository {
        if (is_null(self::$instance)) {
            if (empty(self::$config)) {
                throw new \Exception("Database configuration not set. Use setConfig() before getInstance().");
            }
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Erreur lors de la lecture du fichier de configuration");
        }
        self::$config = $conf;
    }
}