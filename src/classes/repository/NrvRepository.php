<?php

namespace iutnc\nrv\repository;

use Exception;
use PDO;
use PDOException; // pour eviter l'erreur sur certains pc avec vscode

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

    public function getAllSoirees(): array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    idSoiree, 
                    nomSoiree, 
                    dateSoiree, 
                    idLieu,
                    tarif,
                    thematique,
                    horaire
                FROM soiree
            ');
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                echo "Aucune donnée n'a été trouvée dans la table `soiree`.";
            }

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
        $password_hash = password_hash($mdp, PASSWORD_BCRYPT);
        $stmt->bindParam(':mdp', $password_hash);
        return $stmt->execute();
    }


    public function getAllStyles(){
        $stmt = $this->pdo->prepare('SELECT nomStyle FROM style');
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result ?: []; 
    }
    public function getImagesBySpectacleId(int $idSpectacle)
    {
        $query = "SELECT i.nomfichier 
                  FROM Image i
                  JOIN SpectacleImage si ON si.idImage = i.idImage
                  WHERE si.idSpectacle = :idSpectacle";
                  
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idSpectacle', $idSpectacle, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    public function getArtisteBySpectacleId(int $idSpectacle)
    {
    $artistes = [];
    $query = "SELECT a.nomArtiste 
              FROM Artiste a
              JOIN Performer p ON a.idArtiste = p.idArtiste
              WHERE p.idSpectacle = :idSpectacle";
    
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':idSpectacle', $idSpectacle, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $artistes[] = $row['nomArtiste'];
    }
    
    return $artistes;
    }

    public function getAllNomArtiste(): array
    {
        $stmt = $this->pdo->prepare("SELECT idArtiste, nomArtiste FROM artiste");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getLieuNom(mixed $idLieu)
    {
        $stmt = $this->pdo->prepare("SELECT nomLieu FROM lieu WHERE idLieu = :idLieu");
        $stmt->bindParam(':idLieu', $idLieu);
        $stmt->execute();
        return $stmt->fetchColumn();
    }


    public function getAllDates() : array{
        $stmt = $this->pdo->prepare("SELECT dateSoiree FROM soiree");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getAllSpecAtDate(string $datesoir) : array{
        $stmt = $this->pdo->prepare('SELECT idspectacle FROM soiree
                                    inner join spectaclesoiree as ss on ss.idsoiree = soiree.idsoiree
                                    WHERE datesoiree = :datesoir');
        $stmt->bindParam(':datesoir', $datesoir, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }


    public function getAllLieux() : array {
        $stmt = $this->pdo->prepare('SELECT idLieu, CONCAT(nomLieu, ", ", adresse) AS lieuAdresse FROM lieu');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public function getAllSpecAtLieu(string $idLieu) : array{
        $stmt = $this->pdo->prepare('SELECT idspectacle FROM soiree
                                    inner join spectaclesoiree as ss on ss.idsoiree = soiree.idsoiree
                                    WHERE idLieu = :idLieu');
        $stmt->bindParam(':idLieu', $idLieu, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function getAllSoireeForSpec(int $idSpectacle) : array{
        $stmt = $this->pdo->prepare('
                SELECT 
                    soiree.idSoiree, 
                    nomSoiree, 
                    dateSoiree, 
                    idLieu,
                    tarif,
                    thematique,
                    horaire
                FROM soiree
                inner join spectaclesoiree ss on ss.idsoiree = soiree.idsoiree
                where :idSpectacle = ss.idSpectacle

            ');
            $stmt->bindParam(':idSpectacle', $idSpectacle, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                echo "Aucune donnée n'a été trouvée dans la table `soiree`.";
            }

            return $result ?: [];
    }

 
}

