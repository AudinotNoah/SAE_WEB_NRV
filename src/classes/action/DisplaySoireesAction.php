<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\Authz;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\action\DisplaySpectaclesAction;


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
        $soirees = $repo->getAllSoirees();

        if (!$id) {
            $html = "<h2>Soirées Disponibles</h2><ul>";
            foreach ($soirees as $sr) {
                $html .= DisplaySoireesAction::createSoiree($sr, $repo) .
                    "<li><a href='?action=list-soirees&id={$sr['idSoiree']}'>Informations supplémentaires</a></li>";
            }
        } else {
            $html = "<h2>Infos Soirée : </h2><ul>";
            foreach ($soirees as $soiree) {
                if ($soiree['idSoiree'] == $id) {
                    $sr = $soiree;
                    break;
                }
            }

            $user = Authz::checkRole(50);
            if (!is_string($user)) {
                $html .= "<button><a href='?action=modify-soiree&id={$id}'\">Modifier ce spectacle</a></button>";
            }


            $lieuNom = $repo->getLieuNom($sr['idLieu']);
            $soiree = new Soiree($sr['nomSoiree'], $sr['dateSoiree'], $lieuNom, $sr['thematique'], $sr['horaire'], $sr['tarif']);

            $renderer = new SoireeRenderer($soiree);
            $soiree_html = $renderer->render(1); 
            $html .= $soiree_html;

            $spectacles = $repo->getAllSpectacles();
            $specAtSoiree = $repo->getSpecAtSoiree((int) $id);
            $specAtSoiree = array_column($specAtSoiree, 'idSpectacle');

            $html .= "<h2>Les spectacles disponibles sont :</h2>";
            foreach ($spectacles as $sp) {
                if (in_array($sp['idSpectacle'], $specAtSoiree)) {
                    $html .= DisplaySpectaclesAction::createSpec($sp, $repo, 2) . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
                }
            }


        }

        return $html;
    }
}
