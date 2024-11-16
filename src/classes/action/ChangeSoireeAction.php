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
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        if (!$id) {
            return "<p class='notification is-warning'>Aucune soirée spécifiée.</p>";
        }

        $soiree = $repo->getSoireeById($id);
        if (!$soiree) {
            return "<p class='notification is-danger'>La soirée spécifiée n'existe pas.</p>";
        }

        // Convertir la chaîne des IDs des spectacles en tableau
        if (isset($soiree['spectacles_id']) && $soiree['spectacles_id']) {
            $soiree['spectacles_id'] = explode(',', $soiree['spectacles_id']);
        } else {
            $soiree['spectacles_id'] = []; // Si aucun spectacle n'est associé, initialiser comme tableau vide
        }

        // Liste des noms des lieux
        $lieux = $repo->getAllLieux();

        // Liste des spectacles disponibles
        $spectacles = $repo->getAllSpectacles();

        $html = "<div class='container'>";
        $html .= "<h2 class='title is-4'>Modifier la Soirée</h2>";
        $html .= "<form method='POST' action='' enctype='multipart/form-data' class='box'>";

        // Nom de la soirée
        $html .= "<div class='field'>
                    <label class='label' for='nom'>Nom de la soirée:</label>
                    <div class='control'>
                        <input class='input' type='text' id='nom' name='nom' value='" . htmlspecialchars_decode($soiree['nomSoiree'], ENT_QUOTES) . "' required>
                    </div>
                  </div>";

        // Date de la soirée
        $html .= "<div class='field'>
                    <label class='label' for='date'>Date:</label>
                    <div class='control'>
                        <input class='input' type='date' id='date' name='date' value='" . htmlspecialchars($soiree['dateSoiree']) . "' required>
                    </div>
                  </div>";

        // Heure de début
        $html .= "<div class='field'>
                    <label class='label' for='horaire'>Heure de début:</label>
                    <div class='control'>
                        <input class='input' type='time' id='horaire' name='horaire' value='" . htmlspecialchars($soiree['horaire']) . "' required>
                    </div>
                  </div>";

        // Lieu
        $html .= "<div class='field'>
                    <label class='label' for='lieu'>Lieu:</label>
                    <div class='control'>
                        <div class='select'>
                            <select id='lieu' name='lieu' required>";
        foreach ($lieux as $lieu) {
            $selected = ($soiree['idLieu'] === $lieu['idLieu']) ? 'selected' : '';
            $html .= "<option value='" . $lieu['idLieu'] . "' $selected>" . htmlspecialchars_decode($lieu['LieuAdresse'], ENT_QUOTES) . "</option>";
        }
        $html .= "</select></div></div></div>";

        // Tarif de la soirée
        $html .= "<div class='field'>
                    <label class='label' for='tarif'>Tarif:</label>
                    <div class='control'>
                        <input class='input' type='number' id='tarif' name='tarif' value='" . htmlspecialchars_decode($soiree['tarif'], ENT_QUOTES) . "' step='0.01' required>
                    </div>
                  </div>";

        // Thématique de la soirée
        $html .= "<div class='field'>
                    <label class='label' for='thematique'>Thématique:</label>
                    <div class='control'>
                        <input class='input' type='text' id='thematique' name='thematique' value='" . htmlspecialchars_decode($soiree['thematique'], ENT_QUOTES) . "' required>
                    </div>
                  </div>";

        // Spectacles
        $html .= "<div class='field'>
                    <fieldset id='spectacle-selection'>
                    <label class='label'>Choisir les spectacles qui seront joués lors de cette soirée (maximum 3):</label>
                    <div class='control'>";
        foreach ($spectacles as $spectacle) {
            $checked = in_array($spectacle['idSpectacle'], $soiree['spectacles_id']) ? 'checked' : '';
            $html .= "<label class='checkbox'>
                        <input type='checkbox' name='spectacles[]' value='{$spectacle['idSpectacle']}' $checked>
                        " . htmlspecialchars_decode($spectacle['nomSpectacle'], ENT_QUOTES) . "
                      </label><br>";
        }
        $html .= "</div></div>";

        // Script JavaScript pour limiter à 3 spectacles
        $html .= <<<HTML
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('#spectacle-selection input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const checkedCount = document.querySelectorAll('#spectacle-selection input[type="checkbox"]:checked').length;
                    if (checkedCount > 3) {
                        checkbox.checked = false;
                        alert("Vous pouvez sélectionner un maximum de 3 spectacles.");
                    }
                });
            });
        });
        </script>
        HTML;

        // Bouton de soumission
        $html .= "<div class='field'>
                    <div class='control'>
                        <button class='button is-primary' type='submit'>Modifier la soirée</button>
                    </div>
                  </div>";

        $html .= "</form>";
        $html .= "</div>";

        return $html;
    }

    protected function post(): string
    {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_SPECIAL_CHARS);
        $date = filter_var($_POST['date'], FILTER_SANITIZE_SPECIAL_CHARS);
        $horaire = filter_var($_POST['horaire'], FILTER_SANITIZE_SPECIAL_CHARS);
        $lieu = filter_var($_POST['lieu'], FILTER_SANITIZE_SPECIAL_CHARS);
        $tarif = filter_var($_POST['tarif'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $thematique = filter_var($_POST['thematique'], FILTER_SANITIZE_SPECIAL_CHARS);
        $spectacles = isset($_POST['spectacles']) ? array_map('intval', $_POST['spectacles']) : [];

        $repo = NrvRepository::getInstance();
        $soiree = $repo->getSoireeById($id);
        if (!$soiree) {
            return "<p class='notification is-danger'>La soirée spécifiée n'existe pas.</p>";
        }

        $result = $repo->updateSoiree($id, $nom, $date, $horaire, $lieu, $tarif, $thematique, $spectacles);
        if ($result) {
            $url = "Location: ?action=list-soirees&id=" . $id;
            header($url);
        } else {
            return "<p class='notification is-danger'>Une erreur s'est produite lors de la modification de la soirée.</p>";
        }

        return '';
    }

}
