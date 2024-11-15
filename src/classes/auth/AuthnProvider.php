<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\repository\NrvRepository;

class AuthnProvider
{
    // Permet à un utilisateur de se connecter en vérifiant ses identifiants
    public static function signin(string $email, string $password)
    {
        $repo = NrvRepository::getInstance(); // Accès au repository
        $user = $repo->findInfos($email); // Recherche l'utilisateur par email

        if (!$user) {
            throw new AuthnException("Email invalide"); // Erreur si l'utilisateur n'existe pas
        }
        if (!password_verify($password, $user->mdp)) {
            throw new AuthnException("Mot de passe invalide"); // Erreur si le mot de passe ne correspond pas
        }

        // Stocke les informations de l'utilisateur dans la session
        $_SESSION['user'] = [
            'id' => $user->idUtil,
            'email' => $user->email,
            'role' => $user->role,
            'droit'=> $user->droit
        ];
    }

    // Crée un utilisateur "staff" avec vérification préalable
    public static function createStaff(string $email, string $password)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email); // Vérifie si l'email est déjà utilisé

        if ($user) {
            throw new AuthnException("Email déjà utilisé");
        }

        if (strlen($password) < 10) {
            throw new AuthnException("Le mot de passe est trop court (minimum 10 caractères)");
        }

        // Ajoute le nouvel utilisateur dans la base de données
        $repo->createStaff($email, $password);
    }

    // Récupère le rôle de l'utilisateur à partir de son email
    public static function getUserRole($email)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);
        return $user->role;
    }

    // Récupère l'ID de l'utilisateur à partir de son email
    public static function getUserId($email)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);
        return $user->idUtil;
    }

    // Récupère l'utilisateur actuellement connecté depuis la session
    public static function getSignedInUser(): array {
        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Pas connecté."); // Erreur si aucun utilisateur n'est connecté
        }

        return $_SESSION['user']; // Retourne les informations de l'utilisateur connecté
    }
}
