<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\auth\Authz;

class ChangeSoireeAction extends Action {

    protected function get(): string
    {
        $repo = NrvRepository::getInstance();

        $id = $_GET['id'] ?? null;

        // Vérifier si l'utilisateur est connecté et a le rôle nécessaire
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        if (!$id) {
            return "<p>Aucune soirée spécifiée.</p>";
        }

        $soiree = $repo->getSoireeById($id);
        if (!$soiree) {
            return "<p>La soirée spécifiée n'existe pas.</p>";
        }

        // Convertir la chaîne des IDs des spectacles en tableau
        if (isset($soiree['spectacles_id']) && $soiree['spectacles_id']) {
            $soiree['spectacles_id'] = explode(',', $soiree['spectacles_id']);
        } else {
            $soiree['spectacles_id'] = []; // Si aucun spectacle n'est associé, initialiser comme tableau vide
        }

        // Liste des noms des lieux
        $lieux = $repo->gettAllNomLieu();

        // Liste des spectacles disponibles
        $spectacles = $repo->getAllSpectacles();

        $html = "<h2>Modifier la Soirée</h2>";
        $html .= "<form method='POST' action='' enctype='multipart/form-data'>";

        // Nom de la soirée
        $html .= "<label for='nom'>Nom de la soirée:</label>";
        $html .= "<input type='text' id='nom' name='nom' value='" . htmlspecialchars($soiree['nomSoiree']) . "' required><br><br>";

        // Date de la soirée
        $html .= "<label for='date'>Date:</label>";
        $html .= "<input type='date' id='date' name='date' value='" . htmlspecialchars($soiree['dateSoiree']) . "' required><br><br>";

        // Heure de début
        $html .= "<label for='horaire'>Heure de début:</label>";
        $html .= "<input type='time' id='horaire' name='horaire' value='" . htmlspecialchars($soiree['horaire']) . "' required><br><br>";

        // Lieu
        $html .= "<label for='lieu'>Lieu:</label>";
        $html .= "<select id='lieu' name='lieu' required>";
        foreach ($lieux as $lieu) {
            $selected = ($soiree['idLieu'] === $lieu['idLieu']) ? 'selected' : '';
            $html .= "<option value='" . $lieu['idLieu'] . "' $selected>" . htmlspecialchars($lieu['nomLieu']) . "</option>";
        }
        $html .= "</select><br><br>";

        // Tarif de la soirée
        $html .= "<label for='tarif'>Tarif:</label>";
        $html .= "<input type='number' id='tarif' name='tarif' value='" . htmlspecialchars($soiree['tarif']) . "' step='0.01' required><br><br>";

        // Thématique de la soirée
        $html .= "<label for='thematique'>Thématique:</label>";
        $html .= "<input type='text' id='thematique' name='thematique' value='" . htmlspecialchars($soiree['thematique']) . "' required><br><br>";


        $html .= "<fieldset id='spectacle-selection'>";
        $html .= "<legend>Choisir les spectacles qui seront joués lors de cette soirée (maximum 3):</legend>";
        foreach ($spectacles as $spectacle) {
            $checked = in_array($spectacle['idSpectacle'], $soiree['spectacles_id']) ? 'checked' : '';
            $html .= "<label><input type='checkbox' name='spectacles[]' value='{$spectacle['idSpectacle']}' $checked> " . htmlspecialchars($spectacle['nomSpectacle']) . "</label><br>";
        }
        $html .= "</fieldset>";

        // Ajoutez le script JavaScript
                $html .= <<<HTML
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('#spectacle-selection input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const checkedCount = document.querySelectorAll('#spectacle-selection input[type="checkbox"]:checked').length;
                    if (checkedCount > 3) {
                        checkbox.checked = false; // Annule la sélection
                        alert("Vous pouvez sélectionner un maximum de 3 spectacles.");
                    }
                });
            });
        });
        </script>
        HTML;



        // Bouton de soumission
        $html .= "<button type='submit'>Modifier la soirée</button>";
        $html .= "</form>";




        return $html;
    }



    protected function post(): string
    {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
        $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_STRING);
        $lieu = filter_var($_POST['lieu'], FILTER_SANITIZE_STRING);
        $tarif = filter_var($_POST['tarif'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $thematique = filter_var($_POST['thematique'], FILTER_SANITIZE_STRING);
        $spectacles = isset($_POST['spectacles']) ? array_map('intval', $_POST['spectacles']) : [];



        $repo = NrvRepository::getInstance();
        $soiree = $repo->getSoireeById($id);
        if (!$soiree) {
            return "<p>La soirée spécifiée n'existe pas.</p>";
        }

        $result = $repo->updateSoiree($id, $nom, $date, $horaire, $lieu, $tarif, $thematique, $spectacles);
        if ($result) {
            $url = "Location: ?action=list-soirees&id=" . $id;
            header($url);
            // return "<p>La soirée a été modifiée avec succès.</p>";
        } else {
            return "<p>Une erreur s'est produite lors de la modification de la soirée.</p>";
        }

        return '';

    }

}
