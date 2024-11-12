<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\repository\NrvRepository;


class AuthnProvider
{

    public static function signin(string $email, string $password)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);

        if (!$user) {
            throw new AuthnException("Email invalide");
        }

        if (!password_verify($password, $user->mdp)) {
            throw new AuthnException("Mot de passe invalide");
        }

        $_SESSION['user'] = [
            'id' => $user->idUtil,
            'email' => $user->email,
            'role' => $user->role,
            'droit'=> $user->droit
        ];
    }

    public static function createStaff(string $email, string $password)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);

        if ($user) {
            throw new AuthnException("Email déjà utilisé");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $repo->createStaff($email, $hash);
    }

    public static function getUserRole($email)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);
        return $user->role;
    }

    public static function getUserId($email)
    {
        $repo = NrvRepository::getInstance();
        $user = $repo->findInfos($email);
        return $user->idUtil;
    }

    public static function getSignedInUser(): array {
        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Pas connecté.");
        }
    
        if (!isset($_SESSION['user']['droit'])) {
            throw new AuthnException("Droit non défini dans la session.");
        }
    
        return $_SESSION['user'];
    }
    

}
