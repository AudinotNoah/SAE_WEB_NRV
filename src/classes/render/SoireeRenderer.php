<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Soiree;

class SoireeRenderer implements Renderer
{
    protected Soiree $soiree;

    public function __construct(Soiree $soiree)
    {
        $this->soiree = $soiree;
    }

    // 1 pour tout afficher, 2 pour un résumé
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
        $soiree = $this->soiree;

        $html = "<h2>" . htmlspecialchars_decode($soiree->__get('nomSoiree'), ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars_decode($soiree->__get('dateSoiree'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars_decode($soiree->__get('lieu'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Tarif :</strong> " . htmlspecialchars_decode(number_format($soiree->__get('tarif'), 2), ENT_QUOTES) . " €</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars_decode($soiree->__get('thematique'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Horaire :</strong> " . htmlspecialchars_decode($soiree->__get('horaire'), ENT_QUOTES) . "</p>";


        return $html;
    }

    private function renderCompact(): string
    {
        $soiree = $this->soiree;

        $html = "<h2>" . htmlspecialchars_decode($soiree->__get('nomSoiree'), ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars_decode($soiree->__get('dateSoiree'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars_decode($soiree->__get('lieu'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars_decode($soiree->__get('thematique'), ENT_QUOTES) . "</p>";

        return $html;
    }
}




