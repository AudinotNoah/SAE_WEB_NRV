<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;

class AddSpectacleAction extends Action
{
    protected function get(): string
    {
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

        $spectacle = new Spectacle($nom, $horaireDebut, $horaireFin, $style, $description);

        return "<p>Le spectacle '{$spectacle->nom}' a été créé avec succès !</p>";
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
