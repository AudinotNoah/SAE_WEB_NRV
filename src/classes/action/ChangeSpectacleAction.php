<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;

class ChangeSpectacleAction extends Action {

    // Méthode GET pour afficher le formulaire de modification d'un spectacle
    protected function get(): string {
        $repo = NrvRepository::getInstance();

        // Récupère l'ID du spectacle à partir de l'URL
        $id = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

        // Vérifie si l'utilisateur a les bons droits d'accès (rôle 50)
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        // Si l'ID n'est pas fourni
        if (!$id) {
            return "<div class='notification is-warning'>Aucun spectacle spécifié.</div>";
        }

        // Récupère les informations du spectacle
        $spectacle = $repo->getSpectacleById($id);
        if (!$spectacle) {
            return "<div class='notification is-danger'>Le spectacle spécifié n'existe pas.</div>";
        }

        // Si le spectacle a des soirées associées, on les traite
        if (isset($spectacle['soirees_id']) && $spectacle['soirees_id']) {
            $spectacle['soirees_id'] = explode(',', $spectacle['soirees_id']);
        } else {
            $spectacle['soirees_id'] = [];
        }

        // Récupère tous les styles et soirées disponibles
        $styles = $repo->getAllStyles();
        $soirees = $repo->getAllSoirees();

        // Génère le formulaire HTML pour modifier le spectacle
        $html = "<div class='container'>";
        $html .= "<h2 class='title is-4'>Modifier le Spectacle</h2>";
        $html .= "<form method='POST' action='' enctype='multipart/form-data' class='box'>";

        // Champ pour le nom du spectacle
        $html .= "<div class='field'>
                    <label class='label' for='nom'>Nom du spectacle:</label>
                    <div class='control'>
                        <input class='input' type='text' id='nom' name='nom' value='" . htmlspecialchars_decode($spectacle['nomSpectacle'], ENT_QUOTES) . "' required>
                    </div>
                  </div>";

        // Champ pour la description du spectacle
        $html .= "<div class='field'>
                    <label class='label' for='description'>Description:</label>
                    <div class='control'>
                        <textarea class='textarea' id='description' name='description' required>" . htmlspecialchars_decode($spectacle['description'], ENT_QUOTES) . "</textarea>
                    </div>
                  </div>";

        // Champ pour le style du spectacle
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

        // Champ pour l'horaire de début
        $html .= "<div class='field'>
                    <label class='label' for='horaireDebut'>Horaire de début:</label>
                    <div class='control'>
                        <input class='input' type='time' id='horaireDebut' name='horaireDebut' value='" . htmlspecialchars($spectacle['horaireDebut']) . "' required>
                    </div>
                  </div>";

        // Champ pour l'horaire de fin
        $html .= "<div class='field'>
                    <label class='label' for='horaireFin'>Horaire de fin:</label>
                    <div class='control'>
                        <input class='input' type='time' id='horaireFin' name='horaireFin' value='" . htmlspecialchars($spectacle['horaireFin']) . "' required>
                    </div>
                  </div>";

        // Champ pour le statut du spectacle
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

        // Champ pour importer des images
        $html .= "<div class='field'>
                    <label class='label' for='liste-image'>Importer des images pour le spectacle :</label>
                    <div class='control'>
                        <input class='input' type='file' id='liste-image' name='new_images[]' accept='image/*'>
                    </div>
                </div>";

        // Champ pour modifier l'audio (fichier MP3)
        $html .= "<div class='field'>
                    <label class='label' for='audio'>Modifier l'audio (fichier MP3):</label>
                    <div class='control'>
                        <input class='input' type='file' id='audio' name='audio' accept='audio/mp3'>
                    </div>
                  </div>";

        // Champ pour choisir les soirées où ce spectacle sera joué
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

        // Bouton pour soumettre le formulaire
        $html .= "<div class='field'>
                    <div class='control'>
                        <button class='button is-primary' type='submit'>Enregistrer les modifications</button>
                    </div>
                  </div>";

        // Fermeture du formulaire
        $html .= "</form></div>";

        return $html;
    }

    // Méthode POST pour traiter les modifications du spectacle
    protected function post(): string {
        $id = $_GET['id'] ?? null;
        $repo = NrvRepository::getInstance();

        // Vérifie les droits de l'utilisateur (rôle 50)
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        // Si l'ID n'est pas fourni
        if (!$id) {
            return "<div class='notification is-warning'>Aucun spectacle spécifié.</div>";
        }

        // Récupère et nettoie les données du formulaire
        $nom = filter_var($_POST['nom'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $idstyle = filter_var($_POST['style'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($_POST['horaireDebut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireFin = filter_var($_POST['horaireFin'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $statut = filter_var($_POST['statut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $soirees = isset($_POST['soirees']) ? array_map('intval', $_POST['soirees']) : [];

        // Vérifie que tous les champs sont remplis
        if (!$nom || !$description || !$idstyle || !$horaireDebut || !$horaireFin || !$statut) {
            return "<div class='notification is-warning'>Merci de remplir tous les champs.</div>";
        }

        // Gestion du fichier audio (vérifications de type et de taille)
        $allowedAudioExtension = 'mp3';
        $maxAudioFileSize = 10 * 1024 * 1024;

        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $audioFile = $_FILES['audio'];
            $audioExtension = strtolower(pathinfo($audioFile['name'], PATHINFO_EXTENSION));

            if ($audioExtension !== $allowedAudioExtension) {
                return "<div class='notification is-danger'>Le fichier audio doit être au format MP3.</div>";
            }

            if ($audioFile['size'] > $maxAudioFileSize) {
                return "<div class='notification is-danger'>Le fichier audio est trop volumineux. La taille maximale autorisée est de 10 Mo.</div>";
            }

            // Déplace le fichier audio dans le répertoire prévu
            $audioFilePath = 'uploads/audios/' . uniqid() . '.' . $audioExtension;
            move_uploaded_file($audioFile['tmp_name'], $audioFilePath);
        }

        // Mise à jour des informations du spectacle dans la base de données
        $updateData = [
            'nomSpectacle' => $nom,
            'description' => $description,
            'idStyle' => $idstyle,
            'horaireDebut' => $horaireDebut,
            'horaireFin' => $horaireFin,
            'statut' => $statut,
            'soirees_id' => implode(',', $soirees)
        ];

        if (isset($audioFilePath)) {
            $updateData['audioFile'] = $audioFilePath;
        }

        $result = $repo->updateSpectacle($id, $updateData);

        if ($result) {
            return "<div class='notification is-success'>Spectacle modifié avec succès.</div>";
        } else {
            return "<div class='notification is-danger'>Erreur lors de la modification du spectacle.</div>";
        }
    }
}
