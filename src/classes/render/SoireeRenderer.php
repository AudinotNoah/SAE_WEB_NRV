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

        $html = "<h2>" . htmlspecialchars($soiree->__get('nomSoiree')) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars($soiree->__get('dateSoiree')) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars($soiree->__get('lieu')) . "</p>";
        $html .= "<p><strong>Tarif :</strong> " . htmlspecialchars(number_format($soiree->__get('tarif'), 2)) . " €</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars($soiree->__get('thematique')) . "</p>";
        $html .= "<p><strong>Horaire :</strong> " . htmlspecialchars($soiree->__get('horaire')) . "</p>";


        return $html;
    }

    private function renderCompact(): string
    {
        $soiree = $this->soiree;

        $html = "<h2>" . htmlspecialchars($soiree->__get('nomSoiree')) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars($soiree->__get('dateSoiree')) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars($soiree->__get('lieu')) . "</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars($soiree->__get('thematique')) . "</p>";

        return $html;
    }
}




