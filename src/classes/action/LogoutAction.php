<?php

namespace iutnc\nrv\action;

class LogoutAction extends Action {

    public function get(): string {
        // Détruit la session pour déconnecter l'utilisateur
        session_destroy();

        // Redirige vers la page d'accueil
        header('Location: ?action=default');
        exit;
    }

    protected function post(): string {
        return $this->get();
    }
}

