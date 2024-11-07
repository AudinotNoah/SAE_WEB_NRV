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
        try{
            AuthnProvider::signin($email, $password);
            return "Vous êtes connecté";
        }
        catch (AuthnException $e) {
            return "Erreur de login : " . $e->getMessage();
        }


    }
}