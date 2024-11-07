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
            return $this->renderDetails();
        }
        
        if ($type === 2) {
            return $this->renderSummary();
        }

        throw new \InvalidArgumentException("Type de rendu inconnu : $type");
    }


    private function renderDetails()
    {
        $spectacle = $this->spectacle;

        $html = "<h2>" . htmlspecialchars($spectacle->__get('nom')) . "</h2>";

        $html .= "<h3>Artistes :</h3><ul>";
        foreach ($spectacle->__get('artistes') as $artiste) {
            $html .= "<li>" . htmlspecialchars($artiste->__get('nomArtiste')) . "</li>";
        }
        $html .= "</ul>";

        $html .= "<p><strong>Description :</strong> " . htmlspecialchars($spectacle->__get('description')) . "</p>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars($spectacle->__get('style')) . "</p>";
        //mettre ici images et video

        return $html;
    }

    private function renderSummary(): string
    {
        $spectacle = $this->spectacle;
        $html = "<h2>" . htmlspecialchars($spectacle->__get('nom')) . "</h2>";
        $html .= "<p><strong>Style :</strong> " . htmlspecialchars($spectacle->__get('style')) . "</p>";
        return $html;
    }
}