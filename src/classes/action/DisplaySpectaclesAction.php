<?php

namespace iutnc\nrv\action;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\render\SpectacleRenderer;

class DisplaySpectaclesAction extends Action {

    private static function createSpec($sp, $repo): string {
        $stylenom = $repo->getStyleNom($sp['idSpectacle']);
        $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description'],[],[],$sp['lienAudio']);
        $renderer = new SpectacleRenderer($s);
        return $renderer->render(2);
    }

    private static function getOptions($repo): string {
        $styles = $repo->getAllStyles();
        $trieselect = $_GET['trie'] ?? '';
        $selectedStyle = $_GET['style'] ?? '';

        $html = "<label for='trie'>Trier par :</label>";
        $html .= "<select name='trie' id='trie'>";
        $html .= "<option value=''>Sélectionner</option>";
        $html .= "<option value='style'" . ($trieselect === 'style' ? ' selected' : '') . ">Style</option>";
        $html .= "<option value='date'" . ($trieselect === 'date' ? ' selected' : '') . ">Date</option>";
        $html .= "<option value='lieu'" . ($trieselect === 'lieu' ? ' selected' : '') . ">Lieu</option>";
        $html .= "</select>";

        if ($trieselect === 'style') {
            $html .= "<label for='style'>Choisir un style :</label>";
            $html .= "<select name='style' id='style'>";
            $html .= "<option value=''>Sélectionner</option>";
            foreach ($styles as $style) {
                $nomstyle = $style['nomStyle'];
                $html .= "<option value='{$nomstyle}'" . ($selectedStyle === $nomstyle ? ' selected' : '') . ">{$nomstyle}</option>";
            }
            $html .= "</select>";
        }

        $html .= "<button type='submit'>Filtrer</button>";

        return $html;
    }

    protected function get(): string {
        $repo = NrvRepository::getInstance();
        $id = $_GET['id'] ?? null;
        $trie = $_GET['trie'] ?? null;
        $style = $_GET['style'] ?? null;
        $spectacles = $repo->getAllSpectacles();

        if (!$id) {
            $html = "<h2>Spectacles Disponibles</h2>";
            $html .= "<form method='GET' action=''>";
            $html .= "<input type='hidden' name='action' value='programme'>"; // sinon programme est pas dans l'url jsp pourquoi
            $html .= self::getOptions($repo);
            $html .= "</form><ul>";

            switch ($trie) {
                case 'style':
                    if ($style) {
                        foreach ($spectacles as $sp) {
                            if (strtolower($style) === strtolower($repo->getStyleNom($sp['idSpectacle']))) {
                                $html .= DisplaySpectaclesAction::createSpec($sp, $repo) . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
                            }
                        }
                    } else {
                        $html .= "Aucun style sélectionné";
                    }
                    break;

                default:
                    foreach ($spectacles as $sp) {
                        $html .= DisplaySpectaclesAction::createSpec($sp, $repo) . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
                    }
                    break;
            }

            return $html;
        } else {
            $html = "<h2>Infos : </h2><ul>";
            $sp = $spectacles[$id - 1];
            $stylenom = $repo->getStyleNom($sp['idSpectacle']);

            $images = $repo->getImagesBySpectacleId($sp['idSpectacle']);

            $artistes = $repo->getArtisteBySpectacleId($sp['idSpectacle']);

            $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description'],$artistes,$images,$sp['lienAudio']);
            $renderer = new SpectacleRenderer($s);
            $spec_html = $renderer->render(1);
            $html .= $spec_html;
            return $html;
        }
    }
}
