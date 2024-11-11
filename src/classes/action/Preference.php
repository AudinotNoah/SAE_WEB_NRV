<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\auth\AuthnProvider;

class Preference extends Action {

    public function get(): string {
        $html = "<h2>Liste des préférences</h2><ul>";
    
        if (isset($_COOKIE['preferences']) && $_COOKIE['preferences'] !== '') {
            $preferences = explode(',', $_COOKIE['preferences']);
            
            foreach ($preferences as $spectacleId) {
                // $spectacle = $this->getSpectacleDetails($spectacleId); // Fonction pour obtenir les infos du spectacle
                $html .= "<li>dd</li>";
            }
        } else {
            $html .= "<li>Votre liste de préférences est vide.</li>";
        }
    
        $html .= "</ul>";
        return $html;
    }
    
}