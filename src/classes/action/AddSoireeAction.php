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
    protected function get(): string
    {
        $repository = NrvRepository::getInstance();
        $lieux = $repository->getAllLieuxDeSoiree();
        $spectacles = $repository->getAllSpectacles();

        $lieuxListe = '';
        foreach ($lieux as $lieu) {
            $adresse = htmlspecialchars_decode($lieu['adresse']); // Ajout
            $lieuxListe .= "<label class='radio'><input type='radio' name='soiree_lieu' value='{$lieu['idLieu']}' required> {$adresse}</label><br>";
        }

        $specListe = '';
        foreach ($spectacles as $spec) {
            $nomSpectacle = htmlspecialchars_decode($spec['nomSpectacle']); // Ajout
            $specListe .= "<label class='checkbox'><input type='checkbox' class='spectacle-selection' name='soiree_spectacle[]' value='{$spec['idSpectacle']}'> {$nomSpectacle}</label><br>";
        }

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
            document.addEventListener('DOMContentLoaded', () => {
                const checkboxes = document.querySelectorAll('.spectacle-selection');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        const checkedCount = document.querySelectorAll('.spectacle-selection:checked').length;
                        if (checkedCount > 3) {
                            checkbox.checked = false; // Annule la sélection
                            alert("Vous pouvez sélectionner un maximum de 3 spectacles.");
                        }
                    });
                });
            });
        </script>
        HTML;
    }

    protected function post(): string
    {
        $nom = htmlspecialchars_decode(filter_var($_POST['soiree_name'], FILTER_SANITIZE_SPECIAL_CHARS)); // Ajout
        $dateSoiree = htmlspecialchars_decode(filter_var($_POST['date_soiree'], FILTER_SANITIZE_SPECIAL_CHARS)); // Ajout
        $horaireDebut = htmlspecialchars_decode(filter_var($_POST['soiree_horaireDebut'], FILTER_SANITIZE_SPECIAL_CHARS)); // Ajout
        $lieu = htmlspecialchars_decode(filter_var($_POST['soiree_lieu'], FILTER_SANITIZE_SPECIAL_CHARS)); // Ajout
        $tarif = htmlspecialchars_decode(filter_var($_POST['tarif_soiree'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)); // Ajout
        $theme = htmlspecialchars_decode(filter_var($_POST['theme_soiree'] ?? 'Aucun thème', FILTER_SANITIZE_SPECIAL_CHARS)); // Ajout
        $spectacles = filter_var($_POST['soiree_spectacle'], FILTER_SANITIZE_SPECIAL_CHARS);

        $repository = NrvRepository::getInstance();

        $soiree = new Soiree(
            $nom,
            $dateSoiree,
            $lieu,
            $theme,
            $horaireDebut,
            $tarif
        );

        $idSoiree = $repository->setSoiree($soiree);

        $repository->associeSoireeSpectacle($idSoiree, $spectacles);

        $url = "Location: index.php?action=list-soirees&id=" . $idSoiree;
        header($url);
        exit;

        return "";
    }
}
