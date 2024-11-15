<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Soiree;

class SoireeRenderer implements Renderer
{
    // La soirée à afficher
    protected Soiree $soiree;

    // Constructeur pour initialiser l'objet Soiree
    public function __construct(Soiree $soiree)
    {
        $this->soiree = $soiree;
    }

    // Méthode principale pour afficher la soirée
    // $type = 1 pour un affichage détaillé, $type = 2 pour un affichage compact
    public function render(int $type = 1): string
    {
        if ($type === 1) {
            return $this->renderLong(); // Rendu détaillé
        }

        if ($type === 2) {
            return $this->renderCompact(); // Rendu compact
        }

        // Si un type inconnu est fourni, on lève une exception
        throw new \InvalidArgumentException("Type de rendu inconnu : $type");
    }

    // Rendu détaillé de la soirée
    private function renderLong(): string
    {
        $soiree = $this->soiree;

        // Construction de l'affichage HTML avec tous les détails
        $html = "<div class='box'>";
        $html .= "<h2 class='title is-4'>" . htmlspecialchars_decode($soiree->__get('nomSoiree'), ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars_decode($soiree->__get('dateSoiree'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars_decode($soiree->__get('lieu'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Tarif :</strong> " . htmlspecialchars_decode(number_format($soiree->__get('tarif'), 2), ENT_QUOTES) . " €</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars_decode($soiree->__get('thematique'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Horaire :</strong> " . htmlspecialchars_decode($soiree->__get('horaire'), ENT_QUOTES) . "</p>";
        $html .= "</div>";

        return $html; // Retourne le HTML
    }

    // Rendu compact de la soirée
    private function renderCompact(): string
    {
        $soiree = $this->soiree;

        // Génération d'une couleur de fond aléatoire
        $backgroundColor = $this->generateRandomColor();

        // Construction de l'affichage HTML réduit
        $html = "<div class='box' style='background-color: {$backgroundColor};'>";
        $html .= "<h2 class='title is-5'>" . htmlspecialchars_decode($soiree->__get('nomSoiree'), ENT_QUOTES) . "</h2>";
        $html .= "<p><strong>Date :</strong> " . htmlspecialchars_decode($soiree->__get('dateSoiree'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Lieu :</strong> " . htmlspecialchars_decode($soiree->__get('lieu'), ENT_QUOTES) . "</p>";
        $html .= "<p><strong>Thématique :</strong> " . htmlspecialchars_decode($soiree->__get('thematique'), ENT_QUOTES) . "</p>";
        $html .= "</div>";

        return $html; // Retourne le HTML
    }

    // Génération d'une couleur aléatoire pour le rendu compact
    private function generateRandomColor(): string
    {
        $colors = ['#FFE699', '#FFB6A3', '#B3E5FC', '#C4F1BE', '#E3D7FF', '#FFDAB9']; // Palette de couleurs
        return $colors[array_rand($colors)]; // Retourne une couleur aléatoire
    }
}
