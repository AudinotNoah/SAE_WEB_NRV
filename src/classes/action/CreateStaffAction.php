<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthnException;

class CreateStaffAction extends Action
{

    protected function get(): string
    {
        //verifie si l'utilisateur est un admin
        $user = Authz::checkRole(100);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }
        return <<<HTML
        <form method="POST" action="?action=createStaff">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Créer</button>
          </form>
        HTML;
    }

    protected function post(): string
    {   
        $user = Authz::checkRole(100);
        if (is_string($user)) {
            $errorMessage = $user;
            return $errorMessage;
        }
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        try{
            AuthnProvider::createStaff($email, $password);
            return "Le compte staff $email a bien été créé avec le mot de passe $password";
        }
        catch (AuthnException $e) {
            return "Erreur de création : " . $e->getMessage();
        }
    }

}
