<?php

namespace iutnc\nrv\action;
use iutnc\nrv\repository\NrvRepository;


class DisplaySpectablesAction extends Action {
    protected function get(): string
    {
        $repo = NrvRepository::getInstance();
        $spectacles = $repo->getAllSpectacles();
        $html ="";
        foreach ($spectacles as $sp) {
            $html = $html . '$sp["id"]';
        }
        return $html;
    } 

}