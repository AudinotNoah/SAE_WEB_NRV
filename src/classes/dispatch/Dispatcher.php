<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action\AddSpectacleAction;
use iutnc\nrv\action\ChangeSoireeAction;
use iutnc\nrv\action\ChangeSpectacleAction;
use iutnc\nrv\action\CreateStaffAction;
use iutnc\nrv\action\DefaultAction;
use iutnc\nrv\action\DisplaySoireesAction;
use iutnc\nrv\action\DisplaySpectaclesAction;
use iutnc\nrv\action\LoginAction;
use iutnc\nrv\action\LogoutAction;
use iutnc\nrv\action\DisplayStaffMenu;
use iutnc\nrv\auth\Authz;

class Dispatcher {

    private string $action;

    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = '';

        switch ($this->action) {

            case 'modify-soiree':
                $actionInstance = new ChangeSoireeAction();
                $html = $actionInstance->execute();
                break;

            case 'modify-spectacle':
                $actionInstance = new ChangeSpectacleAction();
                $html = $actionInstance->execute();
                break;

            case 'menu-staff':
                $actionInstance = new DisplayStaffMenu();
                $html = $actionInstance->execute();
                break;

            case 'list-soirees':
                $actionInstance = new DisplaySoireesAction();
                $html = $actionInstance->execute();
                break;

            case 'createStaff':
                $actionInstance = new CreateStaffAction();
                $html = $actionInstance->execute();
                break;

            case 'programme':
                $actionInstance = new DisplaySpectaclesAction();
                $html = $actionInstance->execute();
                break;

            case 'login':
                $actionInstance = new LoginAction();
                $html = $actionInstance->execute();
                break;

            case 'logout':
                $actionInstance = new LogoutAction();
                $html = $actionInstance->execute();
                break;

            case 'add-spectacle':
                $actionInstance = new AddSpectacleAction();
                $html = $actionInstance->execute();
                break;

            default:
                $actionInstance = new DefaultAction();
                $html = $actionInstance->execute();
                break;
        }

        $this->renderPage($html);
    }

    private function renderPage(string $html): void {
        // Vérifie si l'utilisateur est connecté
        $isConnected = isset($_SESSION['user']);
        $userRole = $_SESSION['user']['role'] ?? null;

        // Menu de base
        $menu = <<<HTML
        <nav>
            <a href="?action=default">Accueil</a>
            <a href="?action=programme">Programme</a>
            <a href="?action=list-soirees">Liste des Soirées</a>
            <a href="?action=programme&trie=preferences">Votre liste de préférence</a>
        HTML;
        $user = Authz::checkRole(50); 
        if (!is_string($user)) {
            $menu .= <<<HTML
                <a href="?action=menu-staff">Menu Gestion</a>
                HTML;
        }

        $user = Authz::checkRole(0); 
        if (!is_string($user)) {
            $menu .= <<<HTML
            <span>Connecté en tant que : {$_SESSION['user']['email']}</span>
            <a href="?action=logout">Se Déconnecter</a>
            HTML;
        }
        else{
            $menu .= <<<HTML
            <a href="?action=login">Se Connecter</a>
            HTML;
        }

        $menu .= "</nav>";

        echo <<<HTML
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' href='src/assets/css/style.css'>
        <title>projet web</title>
    </head>
    <body>
        <script src="src/assets/js/index.js"></script>
        $menu
        <main>
            <p></p> <!-- espace -->
            $html
        </main>
    </body>
    </html>
    HTML;
    }
}
