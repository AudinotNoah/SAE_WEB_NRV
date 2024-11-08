<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\repository\NrvRepository;
use PDO;

class AddSpectacleAction extends Action
{

    protected function get(): string
{
    $repository = NrvRepository::getInstance();
    $artistes = $repository->getAllNomArtiste();

    $artistesCheckboxes = '';
    foreach ($artistes as $artiste) {
        $artistesCheckboxes .= "<label><input type='checkbox' name='spectacle_artistes[]' value='{$artiste['idArtiste']}'> {$artiste['nomArtiste']}</label><br>";
    }

    return <<<HTML
    <form method="post" action="?action=add-spectacle" enctype="multipart/form-data">
        <label for="spectacle-name">Nom du spectacle :</label>
        <input type="text" id="spectacle-name" name="spectacle_name" required>

        <label for="spectacle-lieu">Lieu :</label>
        <input type="text" id="spectacle-lieu" name="spectacle_lieu" required>

        <label for="spectacle-style">Style de musique :</label>
        <input type="text" id="spectacle-style" name="spectacle_style" required>

        <label for="spectacle-horaireDebut">Heure de début (HH:MM) :</label>
        <input type="text" id="spectacle-horaireDebut" name="spectacle_horaireDebut" required>

        <label for="spectacle-horaireFin">Heure de fin (HH:MM) :</label>
        <input type="text" id="spectacle-horaireFin" name="spectacle_horaireFin" required>

        <fieldset>
            <legend>Artistes :</legend>
            $artistesCheckboxes
        </fieldset>

        <label for="new-image">Télécharger une nouvelle image :</label>
        <input type="file" id="new-image" name="new_image" accept="image/*">

        <button type="submit">Créer le spectacle</button>
    </form>
    HTML;
}

    protected function post(): string
{
    $nom = $_POST['spectacle_name'];
    $horaireDebut = $_POST['spectacle_horaireDebut'];
    $horaireFin = $_POST['spectacle_horaireFin'];
    $style = $_POST['spectacle_style'];
    $description = $_POST['spectacle_description'] ?? "Aucune description";

    if (!$this->validateTimeFormat($horaireDebut) || !$this->validateTimeFormat($horaireFin)) {
        return "<p>Erreur : L'heure de début ou de fin est invalide. Veuillez utiliser le format HH:MM.</p>" . $this->get();
    }

    $repository = NrvRepository::getInstance();

    // Récupération des ID des artistes sélectionnés
    $selectedArtistes = $_POST['spectacle_artistes'] ?? []; // Liste des ID des artistes

    // Création de l'instance de Spectacle avec les artistes et images
    $spectacle = new Spectacle($nom, $horaireDebut, $horaireFin, $style, $description, $selectedArtistes, "oui.png", 'test.mp3');

    return "<p>Le spectacle '{$spectacle->nom}' a été créé avec succès avec ses artistes et images associés !</p>";
}



    /**
     * Validation du format d'heure (HH:MM)
     * 
     * @param string $time
     * @return bool
     */
    private function validateTimeFormat(string $time): bool
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }



}
