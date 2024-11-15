<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;

class Authz {

    // Méthode pour vérifier si l'utilisateur possède un rôle suffisant
    public static function checkRole($role) {
        try {
            // Récupère l'utilisateur connecté via AuthnProvider
            $user = AuthnProvider::getSignedInUser();

            // Vérifie si le droit de l'utilisateur est supérieur ou égal au rôle requis
            if ((int) $user['droit'] >= $role) {
                return $user;
            }

            // Retourne un message d'erreur si les droits sont insuffisants
            return "Droit insuffisant";
        } catch (AuthnException $e) {
            // Gère les exceptions d'authentification
            return $e->getMessage();
        }
    }

    // Méthode pour vérifier si un utilisateur est connecté
    public static function estCo() {
        try {
            // Si l'utilisateur est connecté, aucun problème
            $user = AuthnProvider::getSignedInUser();
            return true;
        } catch (AuthnException $e) {
            // Retourne false si aucune session n'est active
            return false;
        }
    }
}
