<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;


class AddSpectacleAction extends Action
{

    protected function get(): string
    {
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        $repository = NrvRepository::getInstance();
        $artistes = $repository->getAllNomArtiste();
        $soirees = $repository->getAllSoirees();
        $styles = $repository->getAllStyles();

        $artistesListe = '';
        foreach ($artistes as $artiste) {
            $artistesListe .= "<label><input type='checkbox' name='spectacle_artistes[]' value='{$artiste['idArtiste']}'> {$artiste['nomArtiste']}</label><br>";
        }

        $stylesListe = '';
        foreach ($styles as $style) {
            $stylesListe .= "<label><input type='radio' name='spectacle_style' value='{$style['idStyle']}'required> {$style['nomStyle']}</label><br>";
        }

        $soireesListe = '';
        foreach ($soirees as $soiree) {
            $soireesListe .= "<label><input type='checkbox' name='spectacle_soirees[]' value='{$soiree['idSoiree']} required'> {$soiree['nomSoiree']}</label><br>";
        }

        return <<<HTML
        <form method="post" action="?action=add-spectacle" enctype="multipart/form-data">
            <label for="spectacle-name">Nom du spectacle :</label>
            <input type="text" id="spectacle-name" name="spectacle_name" required>

            <fieldset>
                <legend>Style de musique :</legend>
                $stylesListe
            </fieldset>

            <label for="spectacle-horaireDebut">Heure de début :</label>
            <input type="time" id="spectacle-horaireDebut" name="spectacle_horaireDebut" required>

            <label for="spectacle-horaireFin">Heure de fin :</label>
            <input type="time" id="spectacle-horaireFin" name="spectacle_horaireFin" required>

            <label for="spectacle-description">Description du spectacle :</label>
            <textarea id="spectacle-description" name="spectacle_description" rows="4" cols="50" required></textarea>

            <fieldset>
            <legend>Choisir les soirées où ce spectacle sera joué :</legend>
            $soireesListe
            </fieldset>
        
            <fieldset>
                <legend>Artistes :</legend>
                $artistesListe
            </fieldset>

            <label for="liste-image">Importer des images pour le spectacle :</label>
            <input type="file" id="liste-image" name="new_images[]" accept="image/*" multiple required>

            <label for="audio-file">Téléchargez un extrait audio (.mp3) :</label>
            <input type="file" id="audio-file" name="audio_file" accept=".mp3" required>

            <button type="submit">Créer le spectacle</button>
        </form>
        HTML;
    }




    protected function post(): string
    {
        $user = Authz::checkRole(50); 
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        $nom = filter_var($_POST['spectacle_name'], FILTER_SANITIZE_STRING);
        $horaireDebut = filter_var($_POST['spectacle_horaireDebut'], FILTER_SANITIZE_STRING);
        $horaireFin = filter_var($_POST['spectacle_horaireFin'], FILTER_SANITIZE_STRING);
        $style = filter_var($_POST['spectacle_style'] ?? 'Inconnu', FILTER_SANITIZE_STRING);
        $soirees = isset($_POST['spectacle_soirees']) ? array_map('intval', $_POST['spectacle_soirees']) : [];
        $description = filter_var($_POST['spectacle_description'] ?? 'Aucune description', FILTER_SANITIZE_STRING);


        if (!$this->validateTimeFormat($horaireDebut) || !$this->validateTimeFormat($horaireFin)) {
            return "<p>Erreur : L'heure de début ou de fin est invalide. Veuillez utiliser le format HH:MM.</p>" . $this->get();
        }
        
        $artisteSelection = $_POST['spectacle_artistes'] ?? [];
        $repository = NrvRepository::getInstance();

        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png']; // Extensions autorisées
        $maxFileSize = 10 * 1024 * 1024; // Taille maximale autorisée pour les fichiers (10 Mo)

        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['tmp_name'][0])) {
            foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
                    // Vérification de l'extension du fichier
                    $extension = strtolower(pathinfo($_FILES['new_images']['name'][$index], PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions)) {
                        return "<p>Erreur : L'extension du fichier n'est pas autorisée. Extensions autorisées : jpg, jpeg, png, gif.</p>" . $this->get();
                    }

                    // Vérification de la taille du fichier
                    if ($_FILES['new_images']['size'][$index] > $maxFileSize) {
                        return "<p>Erreur : Le fichier est trop volumineux. La taille maximale autorisée est de 5 Mo.</p>" . $this->get();
                    }

                    // Vérification si le fichier est une image valide
                    $imageSize = getimagesize($tmpName);
                    if (!$imageSize) {
                        return "<p>Erreur : Le fichier téléchargé n'est pas une image valide.</p>" . $this->get();
                    }

                    // Génération d'un nom de fichier unique avec un identifiant unique
                    $uniqueId = uniqid('img_', true);
                    $nomfichier = $uniqueId . '.' . $extension;

                    $dossierImage = "src/assets/images/spectacle-img/";
                    if (!is_dir($dossierImage)) {
                        mkdir($dossierImage, 0777, true);
                    }

                    $destination = "$dossierImage/$nomfichier";
                    if (move_uploaded_file($tmpName, $destination)) {
                        $nouvelleIdImage = $repository->uploadImage($nomfichier); // ID de l'image
                        $images[] = $nouvelleIdImage;
                    } else {
                        return "<p>Erreur : Impossible d'importer l'image {$index}</p>" . $this->get();
                    }
                } else {
                    return "<p>Erreur : Un problème est survenu avec l'image {$index}</p>" . $this->get();
                }
            }
        } else {
            return "<p>Erreur : Vous devez importer au moins une image</p>" . $this->get();
        }


        // Traitement de l'upload du fichier audio .mp3
        $audioFile = null;
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $audioExtension = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
            if ($audioExtension === 'mp3') {
                $randomNumberAudio = random_int(100000, 999999); // Génère un nombre aléatoire de 6 chiffres
                $audioFilename = 'audio_' . $randomNumberAudio . '.mp3';

                $audioDir = "src/assets/media";
                if (!is_dir($audioDir)) {
                    mkdir($audioDir, 0777, true);
                }

                $audioDestination = "$audioDir/$audioFilename";
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audioDestination)) {
                    $audioFile = $audioFilename; // Stocke le nom de l'audio
                } else {
                    return "<p>Erreur : Impossible de télécharger le fichier audio.</p>" . $this->get();
                }
            } else {
                return "<p>Erreur : Le fichier audio doit être au format .mp3</p>" . $this->get();
            }
        } else {
            return "<p>Erreur : Vous devez importer un fichier audio .mp3</p>" . $this->get();
        }

        $spectacle = new Spectacle(
            $nom,
            $horaireDebut,
            $horaireFin,
            $style,
            $description,
            $artisteSelection,
            $images,
            $audioFile
        );

        $idSpectacle = $repository->setSpectacle($spectacle,$style);

        foreach ($images as $idImage) {
            $repository->associerImageAuSpectacle($idImage, $idSpectacle);
        }

        foreach ($artisteSelection as $idArtiste) {
            $repository->associerArtisteAuSpectacle($idArtiste, $idSpectacle);
        }

        $repository->associeSpectacleSoiree($idSpectacle, $soirees);

    
        // $renderer = new SpectacleRenderer($spectacle);
        // $spectacleHtml = $renderer->render(1);
        $url = "Location: index.php?action=programme&id=" . $idSpectacle;
        header($url);
        exit;



        return "";
    }


    /**
     * Validation du format d'heure (HH:MM)
     * 
     * @param string $time
     * @return bool
     */
    private function validateTimeFormat(string $time): bool
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }



}
