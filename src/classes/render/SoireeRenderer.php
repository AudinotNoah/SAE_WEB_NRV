<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Soiree;
use iutnc\nrv\festival\Spectacle;

class SoireeRenderer implements Renderer
{
    protected Soiree $soiree;

    public function __construct(Spectacle $soiree)
    {
        $this->soiree = $soiree;
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
        $soiree = $this->soiree;

        $html = "<h2>" . htmlspecialchars($soiree->__get('nom')) . "</h2>";
        $html .= "<p><strong>Artistes :</strong></p><ul>";
        foreach ($soiree->__get('artistes') as $artiste) {
            $html .= "<li>" . htmlspecialchars($artiste) . "</li>";
        }
        $html .= "</ul>";

        $html .= "<p><strong>Description :</strong> " . htmlspecialchars($soiree->__get('description')) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars($soiree->__get('style')) . "</p>";
        //mettre ici images et video

        $images = $soiree->__get('images');
        if (!empty($images)) {
            $html .= "<h3>Images :</h3>";
            foreach ($images as $image) {
                $html .= "<img src='" . htmlspecialchars($image) . "' alt='Image de {$soiree->__get('nom')}'>";
            }
        }

        $html .= "<audio controls>
                <source src='media/audio/{$this->soiree->lienAudio}' type='audio/mpeg'>
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