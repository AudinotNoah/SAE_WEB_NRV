<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;

class ChangeSpectacleAction extends Action {

    protected function get(): string {
        $repo = NrvRepository::getInstance();

        $id = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        if (!$id) {
            return "<div class='notification is-warning'>Aucun spectacle spécifié.</div>";
        }

        $spectacle = $repo->getSpectacleById($id);
        if (!$spectacle) {
            return "<div class='notification is-danger'>Le spectacle spécifié n'existe pas.</div>";
        }

        if (isset($spectacle['soirees_id']) && $spectacle['soirees_id']) {
            $spectacle['soirees_id'] = explode(',', $spectacle['soirees_id']);
        } else {
            $spectacle['soirees_id'] = [];
        }

        $styles = $repo->getAllStyles();
        $soirees = $repo->getAllSoirees();

        $html = "<div class='container'>";
        $html .= "<h2 class='title is-4'>Modifier le Spectacle</h2>";
        $html .= "<form method='POST' action='' enctype='multipart/form-data' class='box'>";

        $html .= "<div class='field'>
                    <label class='label' for='nom'>Nom du spectacle:</label>
                    <div class='control'>
                        <input class='input' type='text' id='nom' name='nom' value='" . htmlspecialchars_decode($spectacle['nomSpectacle'], ENT_QUOTES) . "' required>
                    </div>
                  </div>";

        $html .= "<div class='field'>
                    <label class='label' for='description'>Description:</label>
                    <div class='control'>
                        <textarea class='textarea' id='description' name='description' required>" . htmlspecialchars_decode($spectacle['description'], ENT_QUOTES) . "</textarea>
                    </div>
                  </div>";

        $html .= "<div class='field'>
                    <label class='label' for='style'>Style:</label>
                    <div class='control'>
                        <div class='select'>
                            <select id='style' name='style' required>";
        foreach ($styles as $style) {
            $selected = ($spectacle['idStyle'] === $style['idStyle']) ? 'selected' : '';
            $html .= "<option value='" . $style['idStyle'] . "' $selected>" . htmlspecialchars_decode($style['nomStyle'], ENT_QUOTES) . "</option>";
        }
        $html .= "</select></div></div></div>";

        $html .= "<div class='field'>
                    <label class='label' for='horaireDebut'>Horaire de début:</label>
                    <div class='control'>
                        <input class='input' type='time' id='horaireDebut' name='horaireDebut' value='" . htmlspecialchars($spectacle['horaireDebut']) . "' required>
                    </div>
                  </div>";

        $html .= "<div class='field'>
                    <label class='label' for='horaireFin'>Horaire de fin:</label>
                    <div class='control'>
                        <input class='input' type='time' id='horaireFin' name='horaireFin' value='" . htmlspecialchars($spectacle['horaireFin']) . "' required>
                    </div>
                  </div>";

        $html .= "<div class='field'>
                    <label class='label' for='statut'>Statut:</label>
                    <div class='control'>
                        <div class='select'>
                            <select id='statut' name='statut' required>";
        $statuts = ['A venir', 'Annulé'];
        foreach ($statuts as $statut) {
            $selected = ($spectacle['statut'] === $statut) ? 'selected' : '';
            $html .= "<option value='$statut' $selected>$statut</option>";
        }
        $html .= "</select></div></div></div>";

        $html .= "<div class='field'>
                    <label class='label' for='liste-image'>Importer des images pour le spectacle :</label>
                    <div class='control'>
                        <input class='input' type='file' id='liste-image' name='new_images[]' accept='image/*'>
                    </div>
                </div>
                    ";


        $html .= "<div class='field'>
                    <label class='label' for='audio'>Modifier l'audio (fichier MP3):</label>
                    <div class='control'>
                        <input class='input' type='file' id='audio' name='audio' accept='audio/mp3'>
                    </div>
                  </div>";

        $html .= "<div class='field'>
                    <label class='label'>Choisir les soirées où ce spectacle sera joué:</label>
                    <div class='control'>";
        foreach ($soirees as $soiree) {
            $checked = in_array($soiree['idSoiree'], $spectacle['soirees_id']) ? 'checked' : '';
            $html .= "<label class='checkbox'>
                        <input type='checkbox' name='soirees[]' value='" . $soiree['idSoiree'] . "' $checked> 
                        " . htmlspecialchars_decode($soiree['nomSoiree'], ENT_QUOTES) . "
                      </label><br>";
        }
        $html .= "</div></div>";

        $html .= "<div class='field'>
                    <div class='control'>
                        <button class='button is-primary' type='submit'>Enregistrer les modifications</button>
                    </div>
                  </div>";

        $html .= "</form></div>";

        return $html;
    }

    protected function post(): string {
        $id = $_GET['id'] ?? null;
        $repo = NrvRepository::getInstance();

        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        if (!$id) {
            return "<div class='notification is-warning'>Aucun spectacle spécifié.</div>";
        }

        $nom = filter_var($_POST['nom'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $idstyle = filter_var($_POST['style'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($_POST['horaireDebut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireFin = filter_var($_POST['horaireFin'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $statut = filter_var($_POST['statut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $soirees = isset($_POST['soirees']) ? array_map('intval', $_POST['soirees']) : [];

        if (!$nom || !$description || !$idstyle || !$horaireDebut || !$horaireFin || !$statut) {
            return "<div class='notification is-warning'>Merci de remplir tous les champs.</div>";
        }

        // Gestion du fichier audio
        $allowedAudioExtension = 'mp3';
        $maxAudioFileSize = 10 * 1024 * 1024;

        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $audioFile = $_FILES['audio'];
            $audioExtension = strtolower(pathinfo($audioFile['name'], PATHINFO_EXTENSION));

            if ($audioExtension !== $allowedAudioExtension) {
                return "<div class='notification is-danger'>Erreur : Le fichier audio doit être au format .mp3</div>" . $this->get();
            }

            if ($audioFile['size'] > $maxAudioFileSize) {
                return "<div class='notification is-danger'>Erreur : Le fichier audio est trop volumineux. La taille maximale autorisée est de 10 Mo.</div>" . $this->get();
            }

            $uniqueAudioId = uniqid('audio_', true);
            $audioFilename = $uniqueAudioId . '.' . $audioExtension;

            $audioDir = "src/assets/media";
            if (!is_dir($audioDir)) {
                mkdir($audioDir, 0777, true);
            }

            $audioDestination = "$audioDir/$audioFilename";
            if (move_uploaded_file($audioFile['tmp_name'], $audioDestination)) {
                $nomFichier = $audioFilename;
            } else {
                return "<div class='notification is-danger'>Erreur : Impossible de télécharger le fichier audio.</div>" . $this->get();
            }
        } else {
            $nomFichier = $repo->getAudio($id);
        }

        // Gestion du fichier image
        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $maxFileSize = 10 * 1024 * 1024;

        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['tmp_name'][0])) {
            // Si des images sont téléchargées
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
                        $nouvelleIdImage = $repo->uploadImage($nomfichier);
                        $images[] = $nouvelleIdImage;
                    } else {
                        return "<div class='notification is-danger'>Erreur : Impossible d'importer l'image {$index}</div>" . $this->get();
                    }
                } else {
                    return "<div class='notification is-danger'>Erreur : Un problème est survenu avec l'image {$index}</div>" . $this->get();
                }

                // Dissocier les anciennes images du spectacle
                $repo->dissocierImagesDuSpectacle($id);

                // Associer les images au spectacle
                foreach ($images as $idImage) {
                    $repo->associerImageAuSpectacle($idImage, $id);
                }
            }
        } else {
            // Si aucune nouvelle image n'est téléchargée, récupérer les images existantes du spectacle
            $images = $repo->getImagesBySpectacleId($id);
        }



        // Mise à jour du spectacle
        $success = $repo->updateSpectacle($id, [
            'nomSpectacle' => $nom,
            'description' => $description,
            'idStyle' => $idstyle,
            'horaireDebut' => $horaireDebut,
            'horaireFin' => $horaireFin,
            'statut' => $statut,
            'lienAudio' => $nomFichier
        ]);





        if ($success) {
            // Appel à la méthode updateSoireeSpectacle avec le nouvel idImage
            $repo->updateSoireeSpectacle($id, $soirees);
            $url = "Location: index.php?action=programme&id=" . $id;
            header($url);
        } else {
            return "<div class='notification is-danger'>Une erreur s'est produite lors de la modification du spectacle.</div>";
        }

        return "";
    }


}
