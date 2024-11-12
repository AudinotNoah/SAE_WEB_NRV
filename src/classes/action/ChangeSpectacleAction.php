<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;

class ChangeSpectacleAction extends Action {

    protected $repo;

    public function __construct() {
        parent::__construct();
        $this->repo = NrvRepository::getInstance();
    }

    public function execute(): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->post();
        }
        return $this->get();
    }

    protected function get(): string {
        $id = $_GET['id'] ?? null;

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'staff' && $_SESSION['user']['role'] != 'admin')) {
            return "<p>Vous n'avez pas l'autorisation de modifier ce spectacle.</p>";
        }

        if (!$id) {
            return "<p>Aucun spectacle spécifié.</p>";
        }

        $spectacle = $this->repo->getSpectacleById($id);
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
        $styles = $this->repo->getAllStyles();

        // Liste des soirées disponibles
        $soirées = $this->repo->getAllSoirees();

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
            $html .= "<option value='" . $style['nomStyle'] . "' $selected>" . $style['nomStyle'] . "</option>";
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
        $spectacle = $this->repo->getSpectacleById($id);


        if (!$id) {
            return "<p>Aucun spectacle spécifié.</p>";
        }

        $nom = $_POST['nom'] ?? null;
        $description = $_POST['description'] ?? null;
        $style = $_POST['style'] ?? null;
        $horaireDebut = $_POST['horaireDebut'] ?? null;
        $horaireFin = $_POST['horaireFin'] ?? null;
        $statut = $_POST['statut'] ?? null;
        $soirees = $_POST['soirees'] ?? [];

        if (!$nom || !$description || !$style || !$horaireDebut || !$horaireFin || !$statut) {
            return "<p>Merci de remplir tous les champs.</p>";
        }

        // Traitement du fichier audio
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $audioFile = $_FILES['audio'];
            $audioPath = 'path_to_audio_directory/' . basename($audioFile['name']);
            move_uploaded_file($audioFile['tmp_name'], $audioPath);

        } else {
            $audioPath = $spectacle['lienAudio']; // Garder l'ancien fichier audio si aucun nouveau n'est téléchargé
        }

        // Mise à jour du spectacle
        $success = $this->repo->updateSpectacle($id, [
            'nomSpectacle' => $nom,
            'description' => $description,
            'idStyle' => $style,
            'horaireDebut' => $horaireDebut,
            'horaireFin' => $horaireFin,
            'statut' => $statut,
            'lienAudio' => $audioPath,
        ]);

        if ($success) {
            // Mise à jour des soirées sélectionnées
            $this->repo->updateSoireesForSpectacle($id, $soirees);

            return "<p>Le spectacle a été mis à jour avec succès.</p>";
        } else {
            return "<p>Erreur lors de la mise à jour du spectacle. Veuillez réessayer.</p>";
        }
    }

}


