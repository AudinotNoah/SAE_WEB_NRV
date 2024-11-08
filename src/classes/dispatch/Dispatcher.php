<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action\AddSpectacleAction;
use iutnc\nrv\action\CreateStaffAction;
use iutnc\nrv\action\DefaultAction;
use iutnc\nrv\action\DisplaySoireesAction;
use iutnc\nrv\action\DisplaySpectaclesAction;
use iutnc\nrv\action\LoginAction;

class Dispatcher {

    private string $action;

    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = '';

        switch ($this->action) {

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
        // Vérification si l'utilisateur est connecté
        $userEmail = $_SESSION['user']['email'] ?? null;

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
        <nav>
            <a href='?action=default'>Accueil</a>
            <a href='?action=programme'>Programme</a>
            <a href='?action=list-soirees'>Liste des Soirées</a>
            <a href='?action=infos'>Infos pratiques</a>
            <a href='?action=login'>Connexion</a>
            <a href='?action=createStaff'>Créer un Staff</a>
            <!-- Afficher l'utilisateur connecté -->
            <span>
                Connecter en tant que : $userEmail 
            </span>
        </nav>
        <main>
            <p></p> <!-- espace -->
            $html
        </main>
    </body>
    </html>
    HTML;
    }

}