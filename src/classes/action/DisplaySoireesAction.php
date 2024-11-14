<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\Authz;
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
        $soiree_html = $renderer->render(2); // Mode d'affichage spécifique
        return $soiree_html;
    }

    protected function get(): string {
        $repo = NrvRepository::getInstance();

        $id = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $soirees = $repo->getAllSoirees();

        if (!$id) {
            // Titre et début de liste
            $html = "<h2 class='title is-3'>Soirées Disponibles</h2><div class='columns is-multiline'>";
            foreach ($soirees as $sr) {
                // Chaque soirée est présentée sous forme de carte
                $html .= "<div class='column is-one-third'>
                            <div class='card'>
                                <div class='card-content'>" .
                    DisplaySoireesAction::createSoiree($sr, $repo) .
                    "</div>
                                <div class='card-footer'>
                                    <a href='?action=list-soirees&id={$sr['idSoiree']}' class='card-footer-item button is-info is-fullwidth'>Informations supplémentaires</a>
                                </div>
                            </div>
                          </div>";
            }
            $html .= "</div>"; // Fin de la liste
        } else {
            // Détails d'une soirée spécifique
            $html = "<h2 class='title is-3'>Infos Soirée : </h2>";
            foreach ($soirees as $soiree) {
                if ($soiree['idSoiree'] == $id) {
                    $sr = $soiree;
                    break;
                }
            }

            // Vérification du rôle pour afficher l'option de modification
            $user = Authz::checkRole(50);
            if (!is_string($user)) {
                $html .= "<div class='buttons is-centered'>
                            <a href='?action=modify-soiree&id={$id}' class='button is-warning'>Modifier cette soirée</a>
                          </div>";
            }

            // Affichage des détails de la soirée
            $lieuNom = $repo->getLieuNom($sr['idLieu']);
            $soiree = new Soiree($sr['nomSoiree'], $sr['dateSoiree'], $lieuNom, $sr['thematique'], $sr['horaire'], $sr['tarif']);

            $renderer = new SoireeRenderer($soiree);
            $soiree_html = $renderer->render(1);
            $html .= $soiree_html;

            // Affichage des spectacles associés à la soirée
            $spectacles = $repo->getAllSpectacles();
            $specAtSoiree = $repo->getSpecAtSoiree((int) $id);
            $specAtSoiree = array_column($specAtSoiree, 'idSpectacle');

            $html .= "<h3 class='title is-4'>Les spectacles disponibles dans cette soirée sont :</h3>";
            $html .= "<div class='columns is-multiline'>";
            foreach ($spectacles as $sp) {
                if (in_array($sp['idSpectacle'], $specAtSoiree)) {
                    $html .= "<div class='column is-one-third'>
                                <div class='card'>
                                    <div class='card-content'>" .
                        DisplaySpectaclesAction::createSpec($sp, $repo, 2) .
                        "</div>
                                    <div class='card-footer'>
                                        <a href='?action=programme&id={$sp['idSpectacle']}' class='card-footer-item button is-secondary is-fullwidth'>Plus d'info</a>
                                    </div>
                                </div>
                              </div>";
                }
            }
            $html .= "</div>"; // Fin des spectacles
        }

        return $html;
    }
}
