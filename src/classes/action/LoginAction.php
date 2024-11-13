<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\auth\AuthnProvider;

class LoginAction extends Action {

    public function get(): string {
        return <<<HTML
        <p></p>
        <form method="POST" action="?action=login">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
          </form>
        HTML;
    }

    protected function post(): string {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
        try {
            // Tentative de connexion de l'utilisateur
            AuthnProvider::signin($email, $password);


            return "Vous êtes connecté"; // Après avoir mis à jour la session
        }
        catch (AuthnException $e) {
            return "Erreur: " . $e->getMessage();
        }
    }


}