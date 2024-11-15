<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Spectacle;

class SpectacleRenderer implements Renderer
{
    // Spectacle à rendre
    protected Spectacle $spectacle;

    // Constructeur qui initialise le spectacle à afficher
    public function __construct(Spectacle $spectacle)
    {
        $this->spectacle = $spectacle;
    }

    // Méthode principale pour afficher le spectacle
    // $type = 1 pour tout afficher (détail complet), $type = 2 pour un rendu compact (résumé)
    public function render(int $type = 1): string
    {
        if ($type === 1) {
            return $this->renderLong(); // Rendu complet
        }

        if ($type === 2) {
            return $this->renderCompact(); // Rendu compact
        }

        // Si un type inconnu est fourni, on lève une exception
        throw new \InvalidArgumentException("Type de rendu inconnu : $type");
    }

    // Méthode pour le rendu complet
    private function renderLong(): string
    {
        $spectacle = $this->spectacle;

        // Début de la construction du HTML
        $html = "<div class='box'>";
        $html .= "<h2 class='title is-4'>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . " - " . htmlspecialchars_decode($spectacle->statut, ENT_QUOTES) . "</h2>";

        // Liste des artistes
        $html .= "<p><strong>Artistes :</strong></p><ul>";
        foreach ($spectacle->__get('artistes') as $artiste) {
            $html .= "<li>" . htmlspecialchars_decode($artiste, ENT_QUOTES) . "</li>";
        }
        $html .= "</ul>";

        // Description et style du spectacle
        $html .= "<p><strong>Description :</strong> " . htmlspecialchars_decode($spectacle->__get('description'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";

        // Calcul de la durée à partir des horaires
        $debut = new \DateTime($spectacle->__get('horaireDebut'));
        $fin = new \DateTime($spectacle->__get('horaireFin'));
        $duree = $debut->diff($fin);
        $html .= "<p><strong>Durée :</strong> " . $duree->format('%H:%I') . "</p>";

        // Ajout des images du spectacle
        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $html .= "<h3 class='title is-5'>Images :</h3>";
            foreach ($images as $image) {
                $html .= "<img src='src/assets/images/spectacle-img/" . htmlspecialchars_decode($image, ENT_QUOTES) . "' alt='Image de {$spectacle->__get('nom')}' class='image is-128x128'>";
            }
        }

        // Ajout d'un extrait audio
        $html .= "<h3 class='title is-5'>Écoutez un extrait :</h3>";
        $html .= "<audio class='audio' controls>
                    <source src='src/assets/media/{$spectacle->lienAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>";

        $html .= "</div>"; // Fin de la construction

        return $html;
    }

    // Méthode pour le rendu compact (résumé)
    private function renderCompact(): string
    {
        $spectacle = $this->spectacle;

        // Couleur de fond aléatoire
        $backgroundColor = $this->generateRandomColor();

        // Première image utilisée comme fond (si disponible)
        $backgroundImage = '';
        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $backgroundImage = "src/assets/images/spectacle-img/" . htmlspecialchars_decode($images[0], ENT_QUOTES);
        }

        // Construction d'un résumé simple
        $html = "<div class='box' style='text-align: center; background-color: $backgroundColor; padding: 10px; margin: 10px;'>";
        $html .= "<img src='$backgroundImage' alt='Image de {$spectacle->__get('nom')}' width='128' height='128' style='border-radius: 10px; margin-bottom: 10px;'>";
        $html .= "<h2 class='title is-6'>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";
        $html .= "</div>";

        return $html;
    }

    // Méthode pour générer une couleur de fond aléatoire
    private function generateRandomColor(): string
    {
        $colors = ['#FFDDC1', '#C1FFD7', '#C1D7FF', '#FFD1C1', '#D1C1FF', '#C1FFD1'];
        return $colors[array_rand($colors)];
    }
}
