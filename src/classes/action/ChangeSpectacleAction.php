<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;


class ChangeSpectacleAction extends Action {

    protected function get(): string {
        $repo = NrvRepository::getInstance();

        $id = $_GET['id'] ?? null;

        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        if (!$id) {
            return "<p>Aucun spectacle spécifié.</p>";
        }

        $spectacle = $repo->getSpectacleById($id);
        if (!$spectacle) {
            return "<p>Le spectacle spécifié n'existe pas.</p>";
        }

        // Convertir la chaîne des IDs des soirées en tableau
        if (isset($spectacle['soirees_id']) && $spectacle['soirees_id']) {
            $spectacle['soirees_id'] = explode(',', $spectacle['soirees_id']);
        } else {
            $spectacle['soirees_id'] = []; // Si aucune soirée n'est associée, initialiser comme tableau vide
        }

        // Liste des noms des styles
        $styles = $repo->getAllStyles();

        // Liste des soirées disponibles
        $soirées = $repo->getAllSoirees();

        $html = "<h2>Modifier le Spectacle</h2>";
        $html .= "<form method='POST' action='' enctype='multipart/form-data'>";
        $html .= "<label for='nom'>Nom du spectacle:</label>";
        $html .= "<input type='text' id='nom' name='nom' value='" . htmlspecialchars($spectacle['nomSpectacle']) . "' required><br><br>";

        $html .= "<label for='description'>Description:</label>";
        $html .= "<textarea id='description' name='description' required>" . htmlspecialchars($spectacle['description']) . "</textarea><br><br>";

        $html .= "<label for='style'>Style:</label>";
        $html .= "<select id='style' name='style' required>";

        foreach ($styles as $style) {
            $selected = ($spectacle['idStyle'] === $style['idStyle']) ? 'selected' : '';
            $html .= "<option value='" . $style['idStyle'] . "' $selected>" . $style['nomStyle'] . "</option>";
        }


        $html .= "</select><br><br>";

        $html .= "<label for='horaireDebut'>Horaire de début:</label>";
        $html .= "<input type='time' id='horaireDebut' name='horaireDebut' value='" . htmlspecialchars($spectacle['horaireDebut']) . "' required><br><br>";

        $html .= "<label for='horaireFin'>Horaire de fin:</label>";
        $html .= "<input type='time' id='horaireFin' name='horaireFin' value='" . htmlspecialchars($spectacle['horaireFin']) . "' required><br><br>";

        $html .= "<label for='statut'>Statut:</label>";
        $html .= "<select id='statut' name='statut' required>";
        $statuts = ['A venir', 'Annulé'];
        foreach ($statuts as $statut) {
            $selected = ($spectacle['statut'] === $statut) ? 'selected' : '';
            $html .= "<option value='$statut' $selected>$statut</option>";
        }
        $html .= "</select><br><br>";

        // Champ pour l'audio
        $html .= "<label for='audio'>Modifier l'audio (fichier MP3):</label>";
        $html .= "<input type='file' id='audio' name='audio' accept='audio/mp3'><br><br>";

        // Liste des soirées
        $html .= "<label>Choisir les soirées où ce spectacle sera joué:</label><br>";
        foreach ($soirées as $soiree) {
            $checked = in_array($soiree['idSoiree'], $spectacle['soirees_id']) ? 'checked' : '';
            $html .= "<input type='checkbox' name='soirees[]' value='" . $soiree['idSoiree'] . "' $checked> " . htmlspecialchars($soiree['nomSoiree']) . "<br>";
        }
        $html .= "<br>";

        $html .= "<button type='submit'>Enregistrer les modifications</button>";
        $html .= "</form>";

        return $html;
    }



    protected function post(): string {
        $id = $_GET['id'] ?? null;
        $repo = NrvRepository::getInstance();

        $spectacle = $repo->getSpectacleById($id);

        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }


        if (!$id) {
            return "<p>Aucun spectacle spécifié.</p>";
        }

        $nom = filter_var($_POST['nom'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($_POST['description'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $idstyle = filter_var($_POST['style'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($_POST['horaireDebut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireFin = filter_var($_POST['horaireFin'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $statut = filter_var($_POST['statut'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $soirees = isset($_POST['soirees']) ? array_map('intval', $_POST['soirees']) : [];


        if (!$nom || !$description || !$idstyle || !$horaireDebut || !$horaireFin || !$statut) {
            return "<p>Merci de remplir tous les champs.</p>";
        }

        // Traitement du fichier audio
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $audioFile = $_FILES['audio'];
            $audioPath = "src/assets/media/" . basename($audioFile['name']);
            move_uploaded_file($audioFile['tmp_name'], $audioPath);
            $nomFichier = basename($audioFile['name']);

        } else {
            $nomFichier =  $repo->getAudio($id);; // Garder l'ancien fichier audio si aucun nouveau n'est téléchargé
            
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
            // Mise à jour des soirées sélectionnées
            $repo->updateSoireesForSpectacle($id, $soirees);
            $url = "Location: index.php?action=programme&id=" . $id;
            header($url);

            // return "<p>Le spectacle a été mis à jour avec succès.</p>";
        } else {
            return "<p>Erreur lors de la mise à jour du spectacle. Veuillez réessayer.</p>";
        }
    }

}


