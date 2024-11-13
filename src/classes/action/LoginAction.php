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

        // Vérifier si l'utilisateur est temporairement bloqué
        if ($this->isLockedOut($email)) {
            return "Votre compte est temporairement bloqué en raison de trop nombreuses tentatives de connexion. Veuillez réessayer dans " . self::LOCKOUT_TIME / 60 . " minutes.";
        }

        try {
            // Tentative de connexion de l'utilisateur
            AuthnProvider::signin($email, $password);

            // Réinitialiser les tentatives après une connexion réussie
            $this->resetLoginAttempts($email);

            return "Vous êtes connecté"; // Après avoir mis à jour la session
        }
        catch (AuthnException $e) {
            // Enregistrer la tentative échouée
            $this->recordFailedAttempt($email);

            return "Erreur: " . $e->getMessage();
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
