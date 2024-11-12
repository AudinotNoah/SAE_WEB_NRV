<?php

namespace iutnc\nrv\repository;

use iutnc\nrv\festival\Spectacle;
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
        $stmt = $this->pdo->prepare('SELECT idStyle, nomStyle FROM style');
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

    public function uploadImage(string $nomfichier): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO image (nomfichier) VALUES (:nomfichier)");
        $stmt->bindParam(':nomfichier', $nomfichier);
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }


    public function setSpectacle(Spectacle $s): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO spectacle (nomSpectacle, horaireDebut, horaireFin, idStyle, statut, lienAudio, description) 
            VALUES (:nomSpectacle, :horaireDebut, :horaireFin, :idStyle, :statut, :lienAudio, :description)"
        );

        $nomSpectacle = $s->nom;
        $horaireDebut = $s->horaireDebut;
        $horaireFin = $s->horaireFin;
        $idStyle = $this->getIdStyleByName($s->style);
        $statut = "à venir"; // Par défaut
        $lienAudio = $s->lienAudio;
        $description = $s->description;

        $stmt->bindParam(':nomSpectacle', $nomSpectacle);
        $stmt->bindParam(':horaireDebut', $horaireDebut);
        $stmt->bindParam(':horaireFin', $horaireFin);
        $stmt->bindParam(':idStyle', $idStyle);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':lienAudio', $lienAudio);
        $stmt->bindParam(':description', $description);

        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    private function getIdStyleByName(string $style): int
    {
        $stmt = $this->pdo->prepare("SELECT idStyle FROM style WHERE nomStyle = :style");
        $stmt->bindParam(':style', $style);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        if ($id === false) {
            $insertStmt = $this->pdo->prepare("INSERT INTO style (nomStyle) VALUES (:style)");
            $insertStmt->bindParam(':style', $style);
            $insertStmt->execute();
            
            return (int) $this->pdo->lastInsertId();
        }

        return (int) $id;
    }


    public function associerImageAuSpectacle(int $idImage, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO spectacleimage (idSpectacle, idImage) VALUES (:idSpectacle, :idImage)");
        $stmt->bindParam(':idSpectacle', $idSpectacle);
        $stmt->bindParam(':idImage', $idImage);
        $stmt->execute();
    }

    public function associerArtisteAuSpectacle(int $idArtiste, int $idSpectacle): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO performer (idArtiste, idSpectacle) VALUES (:idArtiste, :idSpectacle)");
        $stmt->bindParam(':idArtiste', $idArtiste, PDO::PARAM_INT);
        $stmt->bindParam(':idSpectacle', $idSpectacle, PDO::PARAM_INT);
        $stmt->execute();
    }


    public function getSpectacleById(mixed $id)
    {
        $stmt = $this->pdo->prepare("SELECT s.*, GROUP_CONCAT(soiree.idSoiree) AS soirees_id
        FROM spectacle s
        LEFT JOIN spectaclesoiree ss ON s.idSpectacle = ss.idSpectacle
        LEFT JOIN soiree soiree ON ss.idSoiree = soiree.idSoiree
        WHERE s.idSpectacle = :id
        GROUP BY s.idSpectacle");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSpectacle(mixed $id, array $array): bool
    {
        $stmt = $this->pdo->prepare("UPDATE spectacle SET 
                                            nomSpectacle = :nom,
                                            description = :description,
                                            idStyle = :style,
                                            horaireDebut = :debut,
                                            horaireFin = :fin,
                                            statut = :statut
                                            lienAudio = :audio
                                        WHERE idSpectacle = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $array['nomSpectacle']);
        $stmt->bindParam(':description', $array['description']);
        $stmt->bindParam(':style', $array['idStyle']);
        $stmt->bindParam(':debut', $array['horaireDebut']);
        $stmt->bindParam(':fin', $array['horaireFin']);
        $stmt->bindParam(':statut', $array['statut']);
        $stmt->bindParam(':audio', $array['lienAudio']);
        return $stmt->execute();
    }

    public function updateSoireesForSpectacle($id, $soirees)
    {
        $stmt = $this->pdo->prepare("DELETE FROM spectaclesoiree WHERE idSpectacle = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $stmt = $this->pdo->prepare("INSERT INTO spectaclesoiree (idSpectacle, idSoiree) VALUES (:idSpectacle, :idSoiree)");
        $stmt->bindParam(':idSpectacle', $id);
        foreach ($soirees as $soiree) {
            $stmt->bindParam(':idSoiree', $soiree);
            $stmt->execute();
        }
    }


}

