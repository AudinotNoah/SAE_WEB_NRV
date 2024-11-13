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

    //1 pour tous avoir, 2 pour un résumé
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


    private function renderLong()
    {
        $spectacle = $this->spectacle;

        $html = "<h2>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . " - " . htmlspecialchars_decode($spectacle->statut, ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Artistes :</strong></p><ul>";
        foreach ($spectacle->__get('artistes') as $artiste) {
            $html .= "<li>" . htmlspecialchars_decode($artiste, ENT_QUOTES) . "</li>";
        }
        $html .= "</ul>";

        $html .= "<p><strong>Description :</strong> " . htmlspecialchars_decode($spectacle->__get('description'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";
        //mettre ici images et video

        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $html .= "<h3>Images :</h3>";
            foreach ($images as $image) {
                $html .= "<img src='src/assets/images/spectacle-img/" . htmlspecialchars_decode($image, ENT_QUOTES) . "' alt='Image de {$spectacle->__get('nom')}'>";
            }
        }   

        $html .= "<audio controls>
                <source src='src/assets/media/{$this->spectacle->lienAudio}' type='audio/mpeg'>
                Votre navigateur ne supporte pas la balise audio.
            </audio>";

        return $html;
    }

    private function renderCompact(): string
    {
        $spectacle = $this->spectacle;
        $html = "<h2>" . htmlspecialchars_decode($spectacle->nom, ENT_QUOTES) . " - " . htmlspecialchars_decode($spectacle->statut, ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars_decode($spectacle->__get('style'), ENT_QUOTES) . "</p>";
        return $html;
    }
}