<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action\AddSpectacleAction;
use iutnc\nrv\action\ChangeSpectacleAction;
use iutnc\nrv\action\CreateStaffAction;
use iutnc\nrv\action\DefaultAction;
use iutnc\nrv\action\DisplaySoireesAction;
use iutnc\nrv\action\DisplaySpectaclesAction;
use iutnc\nrv\action\LoginAction;
use iutnc\nrv\action\LogoutAction;
use iutnc\nrv\action\DisplayStaffMenu;
use iutnc\nrv\action\PreferenceAction;

class Dispatcher {

    private string $action;

    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = '';

        switch ($this->action) {

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
                // Vérifie si l'utilisateur a le rôle admin (100)
                if ($_SESSION['user']['role'] ?? null === 'admin') {
                    $actionInstance = new CreateStaffAction();
                    $html = $actionInstance->execute();
                } else {
                    $html = "<p>Accès refusé : cette section est réservée aux administrateurs.</p>";
                }
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
            
            case 'preference':
                $actionInstance = new PreferenceAction();
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
            <a href="?action=preference">Votre liste de préférence</a>
    HTML;

        if ($isConnected) {
            // Si l'utilisateur est connecté, afficher les options suivantes
            $menu .= <<<HTML
            <a href="?action=logout">Se Déconnecter</a>
            <!-- Afficher l'utilisateur connecté -->
            <span>Connecté en tant que : {$_SESSION['user']['email']}</span>
            HTML;

            // Si l'utilisateur est staff, afficher le menu staff
            if ($userRole === 'staff') {
                $menu .= <<<HTML
                <a href="?action=menu-staff">Menu Staff</a>
                HTML;
            }

            // Si l'utilisateur est admin, afficher "Créer un Staff"
            if ($userRole === 'admin') {
                $menu .= <<<HTML
            <a href="?action=createStaff">Créer un Staff</a>
            HTML;
            }
        } else {
            // Si l'utilisateur n'est pas connecté, afficher les options suivantes
            $menu .= <<<HTML
            <a href="?action=login">Se Connecter</a>
        HTML;
        }

        // Fermeture du menu
        $menu .= "</nav>";

        // Génération du HTML final
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
