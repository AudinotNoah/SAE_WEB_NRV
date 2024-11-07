<?php

namespace iutnc\nrv\action;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\render\SpectacleRenderer;

class DisplaySpectaclesAction extends Action {
    protected function get(): string
    {
        $repo = NrvRepository::getInstance();

        $id = $_GET['id'] ?? null;
        $trie = $_GET['trie'] ?? null;
        $style = $_GET['style'] ?? null;
        $spectacles = $repo->getAllSpectacles();
        if (!$id){
            $html = "<h2>Spectacles Disponibles</h2><ul>";
            switch ($trie){
                case 'date':
                    // pas possible pour le moment
                    return "date";
                case 'style':
                    if ($style){
                        foreach ($spectacles as $sp) {
                            $stylenom = $repo->getStyleNom($sp['idSpectacle']);
                            if (strtolower($stylenom) === strtolower($style)){
                                $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description']);
                                $renderer = new SpectacleRenderer($s);
                                $spec_html = $renderer->render(2);
                                $html = $html . $spec_html;
                                $html = $html . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
                            }
                        }
                    }
                    else{
                        return "Aucun style sélectionné";
                    }
                    break;
                case 'lieu':
                    // pas possible pour le moment
                    return "lieu";
                default:
                    foreach ($spectacles as $sp) {
                        $stylenom = $repo->getStyleNom($sp['idSpectacle']);
                        $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description']);
                        $renderer = new SpectacleRenderer($s);
                        $spec_html = $renderer->render(2);
                        $html = $html . $spec_html;
                        $html = $html . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
                    }
                    break;
            }
                
        }
        else{
            $html = "<h2>Infos : </h2><ul>";
            $sp = $spectacles[$id-1];
            $stylenom = $repo->getStyleNom($sp['idSpectacle']);
            $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description']);
            $renderer = new SpectacleRenderer($s);
            $spec_html = $renderer->render(1);
            $html = $html . $spec_html;
        }
        return $html;
    } 

}