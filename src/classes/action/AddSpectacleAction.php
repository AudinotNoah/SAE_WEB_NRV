<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;

class AddSpectacleAction extends Action
{
    protected function get(): string
    {
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            return "<div class='notification is-danger'>$user</div>";
        }

        $repository = NrvRepository::getInstance();
        $artistes = $repository->getAllNomArtiste();
        $soirees = $repository->getAllSoirees();
        $styles = $repository->getAllStyles();

        $artistesListe = '';
        foreach ($artistes as $artiste) {
            $artistesListe .= "<label class='checkbox'><input type='checkbox' name='spectacle_artistes[]' value='{$artiste['idArtiste']}'> " . htmlspecialchars_decode($artiste['nomArtiste']) . "</label><br>";
        }

        $stylesListe = '';
        foreach ($styles as $style) {
            $stylesListe .= "<label class='radio'><input type='radio' name='spectacle_style' value='{$style['idStyle']}' required> " . htmlspecialchars_decode($style['nomStyle']) . "</label><br>";
        }

        $soireesListe = '';
        foreach ($soirees as $soiree) {
            $soireesListe .= "<label class='checkbox'><input type='checkbox' name='spectacle_soirees[]' value='{$soiree['idSoiree']}' required> " . htmlspecialchars_decode($soiree['nomSoiree']) . "</label><br>";
        }

        return <<<HTML
        <form method="post" action="?action=add-spectacle" enctype="multipart/form-data" class="box">
            <div class="field">
                <label class="label" for="spectacle-name">Nom du spectacle :</label>
                <div class="control">
                    <input class="input" type="text" id="spectacle-name" name="spectacle_name" required>
                </div>
            </div>

            <fieldset class="field">
                <legend class="label">Style de musique :</legend>
                <div class="control">
                    $stylesListe
                </div>
            </fieldset>

            <div class="field">
                <label class="label" for="spectacle-horaireDebut">Heure de début :</label>
                <div class="control">
                    <input class="input" type="time" id="spectacle-horaireDebut" name="spectacle_horaireDebut" required>
                </div>
            </div>

            <div class="field">
                <label class="label" for="spectacle-horaireFin">Heure de fin :</label>
                <div class="control">
                    <input class="input" type="time" id="spectacle-horaireFin" name="spectacle_horaireFin" required>
                </div>
            </div>

            <div class="field">
                <label class="label" for="spectacle-description">Description du spectacle :</label>
                <div class="control">
                    <textarea class="textarea" id="spectacle-description" name="spectacle_description" rows="4" cols="50" required></textarea>
                </div>
            </div>

            <fieldset class="field">
                <legend class="label">Choisir les soirées où ce spectacle sera joué :</legend>
                <div class="control">
                    $soireesListe
                </div>
            </fieldset>

            <fieldset class="field">
                <legend class="label">Artistes :</legend>
                <div class="control">
                    $artistesListe
                </div>
            </fieldset>

            <div class="field">
                <label class="label" for="liste-image">Importer des images pour le spectacle :</label>
                <div class="control">
                    <input class="input" type="file" id="liste-image" name="new_images[]" accept="image/*" multiple required>
                </div>
            </div>

            <div class="field">
                <label class="label" for="audio-file">Téléchargez un extrait audio (.mp3) :</label>
                <div class="control">
                    <input class="input" type="file" id="audio-file" name="audio_file" accept=".mp3" required>
                </div>
            </div>

            <div class="control">
                <button class="button is-link" type="submit">Créer le spectacle</button>
            </div>
        </form>
        HTML;
    }

    protected function post(): string
    {
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            return "<div class='notification is-danger'>$user</div>";
        }

        $nom = filter_var($_POST['spectacle_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($_POST['spectacle_horaireDebut'], FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireFin = filter_var($_POST['spectacle_horaireFin'], FILTER_SANITIZE_SPECIAL_CHARS);
        $style = filter_var($_POST['spectacle_style'] ?? 'Inconnu', FILTER_SANITIZE_SPECIAL_CHARS);
        $soirees = isset($_POST['spectacle_soirees']) ? array_map('intval', $_POST['spectacle_soirees']) : [];
        $description = filter_var($_POST['spectacle_description'] ?? 'Aucune description', FILTER_SANITIZE_SPECIAL_CHARS);

        $artisteSelection = $_POST['spectacle_artistes'] ?? [];
        $repository = NrvRepository::getInstance();

        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $maxFileSize = 10 * 1024 * 1024;

        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['tmp_name'][0])) {
            foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
                    $extension = strtolower(pathinfo($_FILES['new_images']['name'][$index], PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions)) {
                        return "<div class='notification is-danger'>Erreur : L'extension du fichier n'est pas autorisée. Extensions autorisées : jpg, jpeg, png.</div>" . $this->get();
                    }

                    if ($_FILES['new_images']['size'][$index] > $maxFileSize) {
                        return "<div class='notification is-danger'>Erreur : Le fichier est trop volumineux. La taille maximale autorisée est de 10 Mo.</div>" . $this->get();
                    }

                    $imageSize = getimagesize($tmpName);
                    if (!$imageSize) {
                        return "<div class='notification is-danger'>Erreur : Le fichier téléchargé n'est pas une image valide.</div>" . $this->get();
                    }

                    $uniqueId = uniqid('img_', true);
                    $nomfichier = $uniqueId . '.' . $extension;

                    $dossierImage = "src/assets/images/spectacle-img/";
                    if (!is_dir($dossierImage)) {
                        mkdir($dossierImage, 0777, true);
                    }

                    $destination = "$dossierImage/$nomfichier";
                    if (move_uploaded_file($tmpName, $destination)) {
                        $nouvelleIdImage = $repository->uploadImage($nomfichier);
                        $images[] = $nouvelleIdImage;
                    } else {
                        return "<div class='notification is-danger'>Erreur : Impossible d'importer l'image {$index}</div>" . $this->get();
                    }
                } else {
                    return "<div class='notification is-danger'>Erreur : Un problème est survenu avec l'image {$index}</div>" . $this->get();
                }
            }
        } else {
            return "<div class='notification is-danger'>Erreur : Vous devez importer au moins une image</div>" . $this->get();
        }

        $allowedAudioExtension = 'mp3';
        $maxAudioFileSize = 10 * 1024 * 1024;

        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $audioExtension = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));

            if ($audioExtension !== $allowedAudioExtension) {
                return "<div class='notification is-danger'>Erreur : Le fichier audio doit être au format .mp3</div>" . $this->get();
            }

            if ($_FILES['audio_file']['size'] > $maxAudioFileSize) {
                return "<div class='notification is-danger'>Erreur : Le fichier audio est trop volumineux. La taille maximale autorisée est de 10 Mo.</div>" . $this->get();
            }

            $uniqueAudioId = uniqid('audio_', true);
            $audioFilename = $uniqueAudioId . '.' . $audioExtension;

            $audioDir = "src/assets/media";
            if (!is_dir($audioDir)) {
                mkdir($audioDir, 0777, true);
            }

            $audioDestination = "$audioDir/$audioFilename";

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audioDestination)) {
                $audioFile = $audioFilename; // Stocke le nom du fichier audio pour une utilisation ultérieure
            } else {
                return "<div class='notification is-danger'>Erreur : Impossible d'importer le fichier audio</div>" . $this->get();
            }
        } else {
            return "<div class='notification is-danger'>Erreur : Vous devez importer un fichier audio</div>" . $this->get();
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

