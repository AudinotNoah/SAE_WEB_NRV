<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\Authz;

class DisplayStaffMenu extends Action
{
    public function execute(): string {
        $html = <<<HTML
        <div class='content'>
            <h2>Menu du Staff</h2>
            <ul>
                <li><a href='index.php?action=add-soirees'>Ajouter une soirée</a></li>
                <li><a href='index.php?action=modif-soiree'>Modifier une soirée</a></li>
                <li><a href='index.php?action=add-spectacle'>Ajouter un spectacle</a></li>
                <li><a href='index.php?action=modif-spectacle'>Modifier un spectacle</a></li>
        HTML;

        
        $user = Authz::checkRole(50); 
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }

        $user = Authz::checkRole(100); 
        if (!is_string($user)) {
            $html .= "<h2>Menu Administrateur</h2>";
            $html .= "<li><a href='index.php?action=createStaff'>Créer un user</a></li>";
        }
        $html .= "</ul></div>";
    
        return $html;
    }
    
}
