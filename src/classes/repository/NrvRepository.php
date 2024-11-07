<?php

namespace iutnc\nrv\repository;

use Exception;
use PDO;

class NrvRepository {

    private PDO $pdo;
    private static ?NrvRepository $instance = null;
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

    public static function getInstance(): NrvRepository {
        if (is_null(self::$instance)) {
            if (empty(self::$config)) {
                throw new \Exception("Database configuration not set. Use setConfig() before getInstance().");
            }
            self::$instance = new NrvRepository(self::$config);
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


    public function findInfos(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchObject();
    }

    public function getAllSpectacles(): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    idSpectacle, 
                    nomSpectacle, 
                    horaireDebut, 
                    horaireFin, 
                    idSoiree, 
                    idStyle, 
                    statut, 
                    lienAudio, 
                    description 
                FROM spectacle
            ');
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result ?: []; 

        } catch (Exception $e) {
            return [];
        }
    }



    public function getStyleNom(string $id){
        $stmt = $this->pdo->prepare("SELECT nomStyle FROM style WHERE idStyle = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }



    public function createStaff(string $email, string $mdp)
    {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateur (email, mdp, role, droit) VALUES (:email, :mdp, 'staff', 50)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mdp', password_hash($mdp, PASSWORD_BCRYPT));
        return $stmt->execute();
    }

 
}