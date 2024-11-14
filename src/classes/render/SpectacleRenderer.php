<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Spectacle;

class SpectacleRenderer implements Renderer
{
    protected Spectacle $spectacle;

    public function __construct(Spectacle $spectacle)
    {
        $this->spectacle = $spectacle;
    }

    //1 pour tout afficher, 2 pour un résumé
    public function render(int $type = 1): string
    {
        if ($type === 1) {
            return $this->renderLong();
        }

        if ($type === 2) {
            return $this->renderCompact();
        }

        throw new \InvalidArgumentException("Type de rendu inconnu : $type");
    }

    private function renderLong(): string
    {
        $spectacle = $this->spectacle;

        $html = "<div class='box'>";
        $html .= "<h2 class='title is-4'>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . " - " . htmlspecialchars_decode($spectacle->statut, ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Artistes :</strong></p><ul>";
        foreach ($spectacle->__get('artistes') as $artiste) {
            $html .= "<li>" . htmlspecialchars_decode($artiste, ENT_QUOTES) . "</li>";
        }
        $html .= "</ul>";

        $html .= "<p><strong>Description :</strong> " . htmlspecialchars_decode($spectacle->__get('description'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";

        // Durée on recupere les heures de debut et de fin
        $debut = new \DateTime($spectacle->__get('horaireDebut'));
        $fin = new \DateTime($spectacle->__get('horaireFin'));
        $duree = $debut->diff($fin);
        $html .= "<p><strong>Durée :</strong> " . $duree->format('%H:%I') . "</p>";

        // Ajouter des images
        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $html .= "<h3 class='title is-5'>Images :</h3>";
            foreach ($images as $image) {
                $html .= "<img src='src/assets/images/spectacle-img/" . htmlspecialchars_decode($image, ENT_QUOTES) . "' alt='Image de {$spectacle->__get('nom')}' class='image is-128x128'>";
            }
        }

        // Ajouter le lien audio
        $html .= "<h3 class='title is-5'>Écoutez un extrait :</h3>";
        $html .= "<audio class='audio' controls>
                    <source src='src/assets/media/{$spectacle->lienAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>";

        $html .= "</div>";

        return $html;
    }

    private function renderCompact(): string
    {
        $spectacle = $this->spectacle;
        $backgroundColor = $this->generateRandomColor();

        $backgroundImage = '';
        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $backgroundImage = "src/assets/images/spectacle-img/" . htmlspecialchars_decode($images[0], ENT_QUOTES);
        }

        $html = "<div class='box' style='text-align: center; background-color: $backgroundColor; padding: 10px; margin: 10px;'>";
        $html .= "<img src='$backgroundImage' alt='Image de {$spectacle->__get('nom')}' width='128' height='128' style='border-radius: 10px; margin-bottom: 10px;'>";
        $html .= "<h2 class='title is-6'>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";
        $html .= "</div>";

        return $html;
    }



    private function generateRandomColor(): string
    {
        $colors = ['#FFDDC1', '#C1FFD7', '#C1D7FF', '#FFD1C1', '#D1C1FF', '#C1FFD1'];
        return $colors[array_rand($colors)];
    }


}
