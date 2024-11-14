<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthnException;

class CreateStaffAction extends Action
{

    protected function get(): string
    {
        // Vérifie si l'utilisateur est un admin
        $user = Authz::checkRole(100);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }
        return <<<HTML
        <div class="container">
            <h2 class="title is-4">Créer un compte staff</h2>
            <form method="POST" action="?action=createStaff" class="box">
                <div class="field">
                    <label class="label" for="email">Email:</label>
                    <div class="control">
                        <input class="input" type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="password">Mot de passe:</label>
                    <div class="control">
                        <input class="input" type="password" id="password" name="password" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="confirm_password">Confirmer le mot de passe:</label>
                    <div class="control">
                        <input class="input" type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="submit">Créer le compte</button>
                    </div>
                </div>
            </form>
        </div>
        HTML;
    }

    protected function post(): string
    {
        // Vérifie si l'utilisateur est un admin
        $user = Authz::checkRole(100);
        if (is_string($user)) {
            $errorMessage = $user;
            return "<div class='notification is-danger'>$errorMessage</div>";
        }

        // Récupération des informations du formulaire
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
        $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_SPECIAL_CHARS);

        // Vérifier si les mots de passe correspondent
        if ($password !== $confirmPassword) {
            return "<div class='notification is-danger'>Les mots de passe ne correspondent pas.</div>";
        }

        try {
            // Création du compte staff
            AuthnProvider::createStaff($email, $password);
            return "<div class='notification is-success'>Le compte staff $email a bien été créé.</div>";
        } catch (AuthnException $e) {
            return "<div class='notification is-danger'>Erreur de création : " . $e->getMessage() . "</div>";
        }
    }

}
