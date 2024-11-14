<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\Authz;

class DisplayStaffMenu extends Action
{
    public function execute(): string {
        $html = <<<HTML
        <div class="container">
            <h2 class="title is-3">Menu du Staff</h2>
            <div class="box">
                <ul class="menu-list">
                    <li><a href='index.php?action=add-soiree' class="button is-link is-fullwidth">Ajouter une soirée</a></li>
                    <li><a href='index.php?action=list-soirees' class="button is-link is-fullwidth">Modifier une soirée</a></li>
                    <li><a href='index.php?action=add-spectacle' class="button is-link is-fullwidth">Ajouter un spectacle</a></li>
                    <li><a href='index.php?action=programme' class="button is-link is-fullwidth">Modifier un spectacle</a></li>
                </ul>
            </div>
        </div>
        HTML;

        // Vérification du rôle de l'utilisateur avec Authz
        $user = Authz::checkRole(50);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        // Vérification du rôle administrateur (100)
        $user = Authz::checkRole(100);
        if (!is_string($user)) {
            $html .= <<<HTML
            <div class="box">
                <h2 class="title is-4">Menu Administrateur</h2>
                <ul class="menu-list">
                    <li><a href='index.php?action=createStaff' class="button is-warning is-fullwidth">Créer un compte staff</a></li>
                </ul>
            </div>
            HTML;
        }

        $html .= "</div>";  // Fermeture de la div container

        return $html;
    }
}
