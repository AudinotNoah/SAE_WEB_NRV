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
        $password = $_POST['password'];
        try {
            // Tentative de connexion de l'utilisateur
            AuthnProvider::signin($email, $password);

            // Enregistrer l'utilisateur dans la session
            $_SESSION['user']['id'] = AuthnProvider::getUserId($email);
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['role'] = AuthnProvider::getUserRole($email);

            return "Vous êtes connecté"; // Après avoir mis à jour la session
        }
        catch (AuthnException $e) {
            return "Erreur: " . $e->getMessage();
        }
    }


}