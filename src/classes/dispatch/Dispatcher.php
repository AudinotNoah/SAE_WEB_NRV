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
use iutnc\nrv\action\AddSoireeAction;
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
                break;

            case 'modify-spectacle':
                $actionInstance = new ChangeSpectacleAction();
                break;

            case 'menu-staff':
                $actionInstance = new DisplayStaffMenu();
                break;

            case 'list-soirees':
                $actionInstance = new DisplaySoireesAction();
                break;

            case 'createStaff':
                $actionInstance = new CreateStaffAction();
                break;

            case 'programme':
                $actionInstance = new DisplaySpectaclesAction();
                break;

            case 'login':
                $actionInstance = new LoginAction();
                break;

            case 'logout':
                $actionInstance = new LogoutAction();
                break;

            case 'add-spectacle':
                $actionInstance = new AddSpectacleAction();
                break;

            case 'add-soiree':
                $actionInstance = new AddSoireeAction();
                break;

            default:
                $actionInstance = new DefaultAction();
                break;
        }
        $html = $actionInstance->execute();
        $this->renderPage($html);
    }

    private function renderPage(string $html): void {
        // Menu de base avec Bulma, plus stylisé
        $menu = <<<HTML
        <nav class="navbar is-spaced is-primary is-size-5 py-3">
            <div class="navbar-brand">
                <a class="navbar-item has-text-weight-bold is-size-4" href="?action=default">Accueil</a>
                <a class="navbar-item has-text-weight-semibold" href="?action=programme">Programme</a>
                <a class="navbar-item has-text-weight-semibold" href="?action=list-soirees">Liste des Soirées</a>
                <a class="navbar-item has-text-weight-semibold" href="?action=programme&trie=preferences">Votre liste de préférence</a>
            </div>
            <div class="navbar-menu">
                <div class="navbar-end">
        HTML;

        $user = Authz::checkRole(50);
        if (!is_string($user)) {
            $menu .= <<<HTML
                <a class="navbar-item has-text-weight-semibold" href="?action=menu-staff">Menu Gestion</a>
                HTML;
        }

        if (Authz::estCo()) {
            $menu .= <<<HTML
            <span class="navbar-item has-text-white is-size-5">Connecté en tant que : {$_SESSION['user']['email']}</span>
            <a class="navbar-item button is-danger is-danger" href="?action=logout">Se Déconnecter</a>
            HTML;
        }
        else{
            $menu .= <<<HTML
            <a class="navbar-item button is-primary is-succes" href="?action=login">Se Connecter</a>
            HTML;
        }

        $menu .= "</div></div></nav>";

        echo <<<HTML
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css'>
            <title>Festival de Musique - Administration</title>
        </head>
        <body>
            <script src="src/assets/js/index.js"></script>
            $menu
            <main class="section">
                <div class="container">
                    <p></p> <!-- espace -->
                    $html
                </div>
            </main>
        </body>
        </html>
        HTML;
    }
}
