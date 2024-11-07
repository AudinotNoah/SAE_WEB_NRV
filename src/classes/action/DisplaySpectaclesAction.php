<?php

namespace iutnc\nrv\action;
use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Spectacle;

class DisplaySpectaclesAction extends Action {
    protected function get(): string
    {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->getAllSpectacles();
        $html ="";
        foreach ($spectacles as $sp) {
            $stylenom = $repo->getStyleNom($sp['idSpectacle']);
            new Spectacle($sp['nomSpectacle'],$sp['horaireDebut'],$sp['horaireFin'],$stylenom,$sp['description']);
        }
        return $html;
    } 

}