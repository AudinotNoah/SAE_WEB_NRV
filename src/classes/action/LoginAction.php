<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\auth\AuthnProvider;

class LoginAction extends Action {

    // Limite de tentatives avant de bloquer l'accès temporairement
    const MAX_ATTEMPTS = 5;
    // Temps en secondes avant de réinitialiser le compteur de tentatives après un délai
    const LOCKOUT_TIME = 900; // 15 minutes


    public function get(): string {
        return <<<HTML
        <div class="container">
            <h2 class="title is-4">Connexion</h2>
            <form method="POST" action="?action=login" class="box">
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
                    <div class="control">
                        <button class="button is-primary" type="submit">Se connecter</button>
                    </div>
                </div>
            </form>
        </div>
        HTML;
    }

    protected function post(): string {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

        // Vérifier si l'utilisateur est temporairement bloqué
        if ($this->isLockedOut($email)) {
            return "<div class='notification is-danger'>Votre compte est temporairement bloqué en raison de trop nombreuses tentatives de connexion. Veuillez réessayer plus tard.</div>";
        }

        try {
            // Tentative de connexion de l'utilisateur
            AuthnProvider::signin($email, $password);

            // Réinitialiser les tentatives après une connexion réussie
            $this->resetLoginAttempts($email);

            return "<div class='notification is-success'>Vous êtes connecté</div>"; // Après avoir mis à jour la session
        }
        catch (AuthnException $e) {
            // Enregistrer la tentative échouée
            $this->recordFailedAttempt($email);

            return "<div class='notification is-danger'>Erreur: " . $e->getMessage() . "</div>";
        }
    }

    // Vérifie si l'utilisateur est temporairement bloqué
    private function isLockedOut(string $email): bool {
        // Vérifier si des tentatives échouées existent dans la session
        if (isset($_SESSION['failed_attempts'][$email])) {
            $attempts = $_SESSION['failed_attempts'][$email];

            if ($attempts['count'] >= self::MAX_ATTEMPTS) {
                $lastAttemptTime = $attempts['last_attempt_time'];
                if (time() - $lastAttemptTime < self::LOCKOUT_TIME) {
                    return true; // L'utilisateur est encore bloqué
                }
            }
        }
        return false; // L'utilisateur peut encore essayer de se connecter
    }

    // Enregistre une tentative de connexion échouée
    private function recordFailedAttempt(string $email): void {
        // Si ce n'est pas déjà fait, initialiser l'élément pour l'email dans la session
        if (!isset($_SESSION['failed_attempts'][$email])) {
            $_SESSION['failed_attempts'][$email] = ['count' => 0, 'last_attempt_time' => time()];
        }

        // Incrémenter le nombre de tentatives échouées
        $_SESSION['failed_attempts'][$email]['count']++;
        $_SESSION['failed_attempts'][$email]['last_attempt_time'] = time();
    }

    // Réinitialiser les tentatives après une connexion réussie
    private function resetLoginAttempts(string $email): void {
        // Réinitialiser les tentatives dans la session
        if (isset($_SESSION['failed_attempts'][$email])) {
            unset($_SESSION['failed_attempts'][$email]);
        }
    }
}
