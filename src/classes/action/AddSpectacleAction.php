<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;

class AddSpectacleAction extends Action
{
    // Méthode GET qui génère le formulaire d'ajout d'un spectacle
    protected function get(): string
    {
        // Vérification du rôle utilisateur (doit être 50 ou plus)
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            return "<div class='notification is-danger'>$user</div>"; // Affiche un message d'erreur si le rôle n'est pas suffisant
        }

        // Récupère les données nécessaires (artistes, soirées, styles) depuis le repository
        $repository = NrvRepository::getInstance();
        $artistes = $repository->getAllNomArtiste();
        $soirees = $repository->getAllSoirees();
        $styles = $repository->getAllStyles();

        // Génère la liste des artistes sous forme de checkboxes
        $artistesListe = '';
        foreach ($artistes as $artiste) {
            $artistesListe .= "<label class='checkbox'><input type='checkbox' name='spectacle_artistes[]' value='{$artiste['idArtiste']}'> " . htmlspecialchars_decode($artiste['nomArtiste']) . "</label><br>";
        }

        // Génère la liste des styles sous forme de radio buttons
        $stylesListe = '';
        foreach ($styles as $style) {
            $stylesListe .= "<label class='radio'><input type='radio' name='spectacle_style' value='{$style['idStyle']}' required> " . htmlspecialchars_decode($style['nomStyle']) . "</label><br>";
        }

        // Génère la liste des soirées sous forme de checkboxes
        $soireesListe = '';
        foreach ($soirees as $soiree) {
            $soireesListe .= "<label class='checkbox'><input type='checkbox' name='spectacle_soirees[]' value='{$soiree['idSoiree']}'> " . htmlspecialchars_decode($soiree['nomSoiree']) . "</label><br>";
        }

        // Retourne le code HTML pour le formulaire d'ajout d'un spectacle
        return <<<HTML
        <form method="post" action="?action=add-spectacle" enctype="multipart/form-data" class="box">
            <!-- Nom du spectacle -->
            <div class="field">
                <label class="label" for="spectacle-name">Nom du spectacle :</label>
                <div class="control">
                    <input class="input" type="text" id="spectacle-name" name="spectacle_name" required>
                </div>
            </div>

            <!-- Style de musique -->
            <fieldset class="field">
                <legend class="label">Style de musique :</legend>
                <div class="control">
                    $stylesListe
                </div>
            </fieldset>

            <!-- Heure de début -->
            <div class="field">
                <label class="label" for="spectacle-horaireDebut">Heure de début :</label>
                <div class="control">
                    <input class="input" type="time" id="spectacle-horaireDebut" name="spectacle_horaireDebut" required>
                </div>
            </div>

            <!-- Heure de fin -->
            <div class="field">
                <label class="label" for="spectacle-horaireFin">Heure de fin :</label>
                <div class="control">
                    <input class="input" type="time" id="spectacle-horaireFin" name="spectacle_horaireFin" required>
                </div>
            </div>

            <!-- Description -->
            <div class="field">
                <label class="label" for="spectacle-description">Description du spectacle :</label>
                <div class="control">
                    <textarea class="textarea" id="spectacle-description" name="spectacle_description" rows="4" cols="50" required></textarea>
                </div>
            </div>

            <!-- Soirées -->
            <fieldset class="field">
                <legend class="label">Choisir les soirées où ce spectacle sera joué :</legend>
                <div class="control">
                    $soireesListe
                </div>
            </fieldset>

            <!-- Artistes -->
            <fieldset class="field">
                <legend class="label">Artistes :</legend>
                <div class="control">
                    $artistesListe
                </div>
            </fieldset>

            <!-- Importer des images -->
            <div class="field">
                <label class="label" for="liste-image">Importer des images pour le spectacle :</label>
                <div class="control">
                    <input class="input" type="file" id="liste-image" name="new_images[]" accept="image/*" multiple required>
                </div>
            </div>

            <!-- Télécharger un extrait audio -->
            <div class="field">
                <label class="label" for="audio-file">Téléchargez un extrait audio (.mp3) :</label>
                <div class="control">
                    <input class="input" type="file" id="audio-file" name="audio_file" accept=".mp3" required>
                </div>
            </div>

            <!-- Soumettre le formulaire -->
            <div class="control">
                <button class="button is-link" type="submit">Créer le spectacle</button>
            </div>
        </form>
        HTML;
    }

    // Méthode POST qui gère le traitement du formulaire et l'ajout du spectacle
    protected function post(): string
    {
        $user = Authz::checkRole(50); 
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        $nom = $_POST['spectacle_name'];
        $horaireDebut = $_POST['spectacle_horaireDebut'];
        $horaireFin = $_POST['spectacle_horaireFin'];
        $style = $_POST['spectacle_style'] ?? 'Inconnu';
        $soirees = isset($_POST['spectacle_soirees']) ? (array)$_POST['spectacle_soirees'] : [];
        $description = $_POST['spectacle_description'] ?? 'Aucune description';

        // if (!$this->validateTimeFormat($horaireDebut) || !$this->validateTimeFormat($horaireFin)) {
        //     return "<p>Erreur : L'heure de début ou de fin est invalide. Veuillez utiliser le format HH:MM.</p>" . $this->get();
        // }
        
        $artisteSelection = $_POST['spectacle_artistes'] ?? [];
        $repository = NrvRepository::getInstance();

        $images = [];
        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['tmp_name'][0])) {
            foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
                    // Génération d'un nom de fichier unique avec uniquement des chiffres
                    $extension = pathinfo($_FILES['new_images']['name'][$index], PATHINFO_EXTENSION);
                    $randomNumber = random_int(100000, 999999); // Génère un nombre aléatoire de 6 chiffres
                    $nomfichier = 'img_' . $randomNumber . '.' . $extension;

                    $dossierImage = "src/assets/images/spectacle-img/";
                    if (!is_dir($dossierImage)) {
                        mkdir($dossierImage, 0777, true);
                    }

                    $destination = "$dossierImage/$nomfichier";
                    if (move_uploaded_file($tmpName, $destination)) {
                        $nouvelleIdImage = $repository->uploadImage($nomfichier);
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
}
