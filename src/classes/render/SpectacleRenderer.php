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

        $html = "<h2>" . htmlspecialchars($spectacle->__get('nom')) . "</h2>";
        $html .= "<p><strong>Artistes :</strong> " . htmlspecialchars($spectacle->__get('artistes')) . "</p>";
        $html .= "</ul>";

        $html .= "<p><strong>Description :</strong> " . htmlspecialchars($spectacle->__get('description')) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars($spectacle->__get('style')) . "</p>";
        //mettre ici images et video

        $images = $spectacle->__get('images');
        if (!empty($images)) {
            $html .= "<h3>Images :</h3>";
            foreach ($images as $image) {
                $html .= "<img src='" . htmlspecialchars($image) . "' alt='Image de {$spectacle->__get('nom')}'>";
            }
        }   

        $html .= "<audio controls>
                <source src='{$this->spectacle->lienAudio}' type='audio/mpeg'>
                Votre navigateur ne supporte pas la balise audio.
            </audio>";

        return $html;
    }

    private function renderCompact(): string
    {
        $spectacle = $this->spectacle;
        $html = "<h2>" . htmlspecialchars($spectacle->__get('nom')) . "</h2>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars($spectacle->__get('style')) . "</p>";
        return $html;
    }
}