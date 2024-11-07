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
        if (!$id){
            $spectacles = $repo->getAllSpectacles();
            $html = "<h2>Spectacles Disponibles</h2><ul>";
            foreach ($spectacles as $sp) {
                $stylenom = $repo->getStyleNom($sp['idSpectacle']);
                $s = new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description']);
                $renderer = new SpectacleRenderer($s);
                $spec_html = $renderer->render(2);
                $html = $html . $spec_html;
                $html = $html . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
            }
            return $html;
        }
        else{
            
        }
        
    } 

}