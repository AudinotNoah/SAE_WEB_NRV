<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\repository\NrvRepository;
use PDO;

class AddSoireeAction extends Action
{
    // Méthode pour afficher le formulaire de création d'une nouvelle soirée
    protected function get(): string
    {
        $repository = NrvRepository::getInstance();

        // Récupère tous les lieux de soirée et tous les spectacles
        $lieux = $repository->getAllLieuxDeSoiree();
        $spectacles = $repository->getAllSpectacles();

        // Construction de la liste des lieux avec un bouton radio pour chaque lieu
        $lieuxListe = '';
        foreach ($lieux as $lieu) {
            $adresse = htmlspecialchars_decode($lieu['adresse']); // Ajoute l'adresse du lieu
            $lieuxListe .= "<label class='radio'><input type='radio' name='soiree_lieu' value='{$lieu['idLieu']}' required> {$adresse}</label><br>";
        }

        // Construction de la liste des spectacles avec une case à cocher pour chaque spectacle
        $specListe = '';
        foreach ($spectacles as $spec) {
            $nomSpectacle = htmlspecialchars_decode($spec['nomSpectacle']); // Ajoute le nom du spectacle
            $specListe .= "<label class='checkbox'><input type='checkbox' class='spectacle-selection' name='soiree_spectacle[]' value='{$spec['idSpectacle']}'> {$nomSpectacle}</label><br>";
        }

        // Génère le HTML pour le formulaire de création de soirée
        return <<<HTML
        <div class="container">
            <h1 class="title">Créer une nouvelle soirée</h1>
            <form method="post" action="?action=add-soiree" enctype="multipart/form-data">
                <div class="field">
                    <label class="label" for="soiree-name">Nom de la soirée :</label>
                    <div class="control">
                        <input class="input" type="text" id="soiree-name" name="soiree_name" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="theme-soiree">Thématique :</label>
                    <div class="control">
                        <input class="input" type="text" id="theme-soiree" name="theme_soiree" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="date-soiree">Date de la soirée :</label>
                    <div class="control">
                        <input class="input" type="date" id="date-soiree" name="date_soiree" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="soiree-horaireDebut">Heure de début (HH:MM) :</label>
                    <div class="control">
                        <input class="input" type="time" id="soiree-horaireDebut" name="soiree_horaireDebut" required>
                    </div>
                </div>

                <fieldset class="field">
                    <legend class="label">Lieu :</legend>
                    $lieuxListe
                </fieldset>

                <div class="field">
                    <label class="label" for="tarif-soiree">Tarif d'un billet :</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" id="tarif-soiree" name="tarif_soiree" required>
                    </div>
                </div>

                <fieldset class="field">
                    <legend class="label">Choisir les spectacles associés à la soirée :</legend>
                    $specListe
                </fieldset>

                <div class="control">
                    <button class="button is-link" type="submit">Créer la soirée</button>
                </div>
            </form>
        </div>
        
        <script>
            // Script pour limiter la sélection à 3 spectacles
            document.addEventListener('DOMContentLoaded', () => {
                const checkboxes = document.querySelectorAll('.spectacle-selection');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        const checkedCount = document.querySelectorAll('.spectacle-selection:checked').length;
                        if (checkedCount > 3) {
                            checkbox.checked = false; // Annule la sélection si plus de 3 spectacles
                            alert("Vous pouvez sélectionner un maximum de 3 spectacles.");
                        }
                    });
                });
            });
        </script>
        HTML;
    }

    // Méthode pour traiter les données envoyées par le formulaire
    protected function post(): string
    {
        // Sanitize et récupère les valeurs soumises par le formulaire
        $nom = htmlspecialchars_decode(filter_var($_POST['soiree_name'], FILTER_SANITIZE_SPECIAL_CHARS));
        $dateSoiree = htmlspecialchars_decode(filter_var($_POST['date_soiree'], FILTER_SANITIZE_SPECIAL_CHARS));
        $horaireDebut = htmlspecialchars_decode(filter_var($_POST['soiree_horaireDebut'], FILTER_SANITIZE_SPECIAL_CHARS));
        $lieu = htmlspecialchars_decode(filter_var($_POST['soiree_lieu'], FILTER_SANITIZE_SPECIAL_CHARS));
        $tarif = htmlspecialchars_decode(filter_var($_POST['tarif_soiree'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        $theme = htmlspecialchars_decode(filter_var($_POST['theme_soiree'] ?? 'Aucun thème', FILTER_SANITIZE_SPECIAL_CHARS));

        // Récupère les spectacles sélectionnés
        $spectacles = isset($_POST['soiree_spectacle']) && is_array($_POST['soiree_spectacle']) ? $_POST['soiree_spectacle'] : [];

        // Création de l'objet Soiree
        $repository = NrvRepository::getInstance();
        $soiree = new Soiree(
            $nom,
            $dateSoiree,
            $lieu,
            $theme,
            $horaireDebut,
            $tarif
        );

        // Enregistre la soirée et récupère son ID
        $idSoiree = $repository->setSoiree($soiree);

        // Associe les spectacles à la soirée
        $repository->associeSoireeSpectacle($idSoiree, $spectacles);

        // Redirection vers la page de la soirée nouvellement créée
        $url = "Location: index.php?action=list-soirees&id=" . $idSoiree;
        header($url);
        exit;

        return "";
    }
}
