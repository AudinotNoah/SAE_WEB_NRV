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
        // Vérification du rôle utilisateur
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            return "<div class='notification is-danger'>$user</div>";
        }

        // Récupère les données soumises via le formulaire
        $nom = filter_var($_POST['spectacle_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($_POST['spectacle_horaireDebut'], FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireFin = filter_var($_POST['spectacle_horaireFin'], FILTER_SANITIZE_SPECIAL_CHARS);
        $style = filter_var($_POST['spectacle_style'] ?? 'Inconnu', FILTER_SANITIZE_SPECIAL_CHARS);
        $soirees = isset($_POST['spectacle_soirees']) ? array_map('intval', $_POST['spectacle_soirees']) : [];
        $description = filter_var($_POST['spectacle_description'] ?? 'Aucune description', FILTER_SANITIZE_SPECIAL_CHARS);

        // Traitement des artistes sélectionnés
        $artisteSelection = $_POST['spectacle_artistes'] ?? [];
        $repository = NrvRepository::getInstance();

        // Traitement des images téléchargées
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

        // Traitement du fichier audio téléchargé
        $allowedAudioExtensions = ['mp3'];
        $audioFile = $_FILES['audio_file'] ?? null;
        if ($audioFile && $audioFile['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($audioFile['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedAudioExtensions)) {
                return "<div class='notification is-danger'>Erreur : L'extension du fichier audio n'est pas autorisée. Extension autorisée : mp3.</div>" . $this->get();
            }

            $audioFileSize = $audioFile['size'];
            $maxAudioFileSize = 10 * 1024 * 1024;
            if ($audioFileSize > $maxAudioFileSize) {
                return "<div class='notification is-danger'>Erreur : Le fichier audio est trop volumineux. La taille maximale autorisée est de 10 Mo.</div>" . $this->get();
            }

            $uniqueAudioName = uniqid('audio_', true) . '.' . $extension;
            $audioDestination = "src/assets/audio/" . $uniqueAudioName;
            if (!move_uploaded_file($audioFile['tmp_name'], $audioDestination)) {
                return "<div class='notification is-danger'>Erreur : Impossible de télécharger le fichier audio.</div>" . $this->get();
            }
        } else {
            return "<div class='notification is-danger'>Erreur : Le fichier audio est requis.</div>" . $this->get();
        }

        // Enregistrer le spectacle dans la base de données
        $spectacle = new Spectacle(
            $nom,
            $description,
            $horaireDebut,
            $horaireFin,
            $images,
            $audioDestination,
            $style,
            $artisteSelection
        );

        $repository->addSpectacle($spectacle, $soirees);

        // Redirection ou message de succès après l'ajout
        return "<div class='notification is-success'>Le spectacle a été ajouté avec succès !</div>" . $this->get();
    }
}
