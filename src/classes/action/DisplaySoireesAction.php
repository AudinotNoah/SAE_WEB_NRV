<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SoireeRenderer;

class DisplaySoireesAction extends Action {

    private static function createSoiree($sr, $repo) : string {
        // Création d'une instance Soiree
        $lieuNom = $repo->getLieuNom($sr['idLieu']);
        $soiree = new Soiree($sr['nomSoiree'], $sr['dateSoiree'], $lieuNom, $sr['thematique'], $sr['horaire'], $sr['tarif']);

        // Utilisation du renderer pour générer le HTML
        $renderer = new SoireeRenderer($soiree);
        $soiree_html = $renderer->render(1); // Mode d'affichage spécifique
        return $soiree_html;
    }

    protected function get(): string {
        $repo = NrvRepository::getInstance();

        $id = $_GET['id'] ?? null;
        $trie = $_GET['trie'] ?? null;
        $thematique = $_GET['thematique'] ?? null;
        $soirees = $repo->getAllSoirees();


        if (!$id) {
            $html = "<h2>Soirées Disponibles</h2><ul>";

            switch ($trie) {
                case 'date':
                    // Option de tri par date (à implémenter)
                    return "Tri par date non disponible pour le moment";
                case 'thematique':
                    if ($thematique) {
                        foreach ($soirees as $sr) {
                            if (strtolower($sr['thematique']) === strtolower($thematique)) {
                                $html .= DisplaySoireesAction::createSoiree($sr, $repo) .
                                    "<li><a href='?action=programme&id={$sr['idSoiree']}'>Plus d'info</a></li>";
                            }
                        }
                    } else {
                        return "Aucune thématique sélectionnée";
                    }
                    break;
                case 'lieu':
                    // Option de tri par lieu (à implémenter)
                    return "Tri par lieu non disponible pour le moment";
                default:
                    foreach ($soirees as $sr) {
                        $html .= DisplaySoireesAction::createSoiree($sr, $repo) .
                            "<li><a href='?action=programme&id={$sr['idSoiree']}'>Liste des spectacles</a></li>";
                    }
                    break;
            }
        } else {
            // Afficher les informations détaillées pour une soirée spécifique
            $html = "<h2>Infos Soirée : </h2><ul>";
            $sr = $soirees[$id - 1]; // Sélection de la soirée par son ID
            $lieuNom = $repo->getLieuNom($sr['idLieu']);
            $soiree = new Soiree($sr['nomSoiree'], $sr['dateSoiree'], $lieuNom, $sr['thematique'], $sr['horaire'], $sr['tarif']);

            // Utilisation du renderer pour afficher les détails
            $renderer = new SoireeRenderer($soiree);
            $soiree_html = $renderer->render(1); // Mode d'affichage pour les détails
            $html .= $soiree_html;
        }

        return $html;
    }
}
