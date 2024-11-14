<?php
namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\auth\Authz;

class DisplaySpectaclesAction extends Action {

    // Crée l'affichage d'un spectacle
    public static function createSpec($sp, $repo, $choixRendu): string {
        $stylenom = $repo->getStyleNom($sp['idStyle']);
        $images = $repo->getImagesBySpectacleId($sp['idSpectacle']);
        $artistes = $repo->getArtisteBySpectacleId($sp['idSpectacle']);
        $s = new Spectacle($sp['nomSpectacle'], $sp['horaireDebut'], $sp['horaireFin'], $stylenom, $sp['description'], $artistes, $images, $sp['lienAudio'], $sp['statut']);
        $renderer = new SpectacleRenderer($s);
        return $renderer->render($choixRendu);
    }

    // Crée les options pour les filtres (Style, Date, Lieu)
    private static function getSelectOptions(array $options, string $nom, string $val, string $valchoisis): string {
        $html = "<div class='field'>";
        $html .= "<label for='$nom' class='label'>Choisir un $nom :</label>";
        $html .= "<div class='control'>";
        $html .= "<div class='select is-fullwidth'>";
        $html .= "<select name='$nom' id='$nom' onchange='this.form.submit()'>";
        $html .= "<option value=''>Sélectionner</option>";

        foreach ($options as $option) {
            $valeur = $option[$val];
            $valeuroption = ($nom === 'lieu') ? $option['idLieu'] : $valeur;
            $html .= "<option value='{$valeuroption}'" . ($valchoisis == $valeuroption ? ' selected' : '') . ">{$valeur}</option>";
        }

        $html .= "</select>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }


    // Crée le formulaire des filtres de tri
    private static function getOptions($repo): string {
        $trichoix = filter_var($_GET['trie'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

        $html = "<form method='GET' action='' id='filterForm' class='field is-grouped is-grouped-multiline mb-4'>";
        $html .= "<input type='hidden' name='action' value='programme'>";

        // Label principal
        $html .= "<div class='control'>";
        $html .= "<label for='trie' class='label mr-2'>Trier par :</label>";
        $html .= "</div>";

        // Sélecteur de tri principal
        $html .= "<div class='control'>";
        $html .= "<div class='select is-fullwidth'>";
        $html .= "<select name='trie' id='trie' onchange='this.form.submit()'>";
        $html .= "<option value=''>Pas de filtre</option>";
        $html .= "<option value='style'" . ($trichoix === 'style' ? ' selected' : '') . ">Style</option>";
        $html .= "<option value='date'" . ($trichoix === 'date' ? ' selected' : '') . ">Date</option>";
        $html .= "<option value='lieu'" . ($trichoix === 'lieu' ? ' selected' : '') . ">Lieu</option>";
        $html .= "<option value='preferences'" . ($trichoix === 'preferences' ? ' selected' : '') . ">Préférences</option>";
        $html .= "</select>";
        $html .= "</div>";
        $html .= "</div>";

        // Sous-sélecteur dynamique basé sur la sélection de tri
        if ($trichoix === 'style') {
            $html .= "<div class='control'>";
            $html .= self::getSelectOptions($repo->getAllStyles(), 'style', 'nomStyle', $_GET['style'] ?? '');
            $html .= "</div>";
        } elseif ($trichoix === 'date') {
            $html .= "<div class='control'>";
            $html .= self::getSelectOptions($repo->getAllDates(), 'date', 'dateSoiree', $_GET['date'] ?? '');
            $html .= "</div>";
        } elseif ($trichoix === 'lieu') {
            $html .= "<div class='control'>";
            $html .= self::getSelectOptions($repo->getAllLieux(), 'lieu', 'lieuAdresse', $_GET['lieu'] ?? '');
            $html .= "</div>";
        }

        $html .= "</form>";
        return $html;
    }



    // Affiche les spectacles filtrés sous forme de cartes
    private function renderFilteredSpectacles($repo, array $spectacles, string $trie, ?string $choix): string {
        $html = "<div class='columns is-multiline'>";
        foreach ($spectacles as $sp) {
            $valide = true;
            switch ($trie) {
                case 'style':
                    $valide = $choix !== null && strtolower($choix) === strtolower($repo->getStyleNom($sp['idSpectacle']));
                    break;
                case 'date':
                    $valide = $choix !== null && in_array($sp['idSpectacle'], $repo->getAllSpecAtDate($choix));
                    break;
                case 'lieu':
                    $valide = $choix !== null && in_array($sp['idSpectacle'], $repo->getAllSpecAtLieu($choix));
                    break;
                case 'preferences':
                    $valide = !empty($_COOKIE['preferences']) && in_array($sp['idSpectacle'], explode(',', $_COOKIE['preferences']));
                    break;
                default:
                    $valide = true;
                    break;
            }
            if ($valide) {
                $html .= "<div class='column is-one-third'>
                            <div class='card'>
                                <div class='card-content'>" .
                    self::createSpec($sp, $repo, 2) .
                    "</div>
                                <div class='card-footer'>
                                    <a href='?action=programme&id={$sp['idSpectacle']}' class='card-footer-item button is-info is-fullwidth'>Plus d'info</a>
                                </div>
                            </div>
                          </div>";
            }
        }
        $html .= "</div>"; // Fin des colonnes
        return $html;
    }


    // Méthode principale qui gère l'affichage des spectacles
    protected function get(): string {
        $repo = NrvRepository::getInstance();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $trie = filter_var($_GET['trie'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $choix = filter_var($_GET[$trie] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $spectacles = $repo->getAllSpectacles();

        if (!$id) {
            $html = "<h2 class='title is-3'>Spectacles Disponibles</h2>";
            $html .= "<form method='GET' action='' class='mb-4'>";
            $html .= "<input type='hidden' name='action' value='programme'>";
            $html .= self::getOptions($repo);
            $html .= "</form>";
            $html .= $this->renderFilteredSpectacles($repo, $spectacles, $trie, $choix);
        } else {
            $html = "<h2 class='title is-3'>Infos Spectacle :</h2>";
            if (Authz::checkRole(50)) {
                $html .= "<div class='buttons is-centered'>
                                <a href='?action=modify-spectacle&id={$id}' class='button is-warning'>Modifier ce spectacle</a>
                              </div>";
            }
            $spectacle = $repo->getSpectacleById($id);
            if ($spectacle) {
                $html .= "<div class='card'>
                            <div class='card-content'>" .
                    self::createSpec($spectacle, $repo, 1) .
                    "</div>";

                $html .= "</div>";
            }
        }

        return $html;
    }
}
