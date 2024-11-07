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

        if (!password_verify($password, $user->passwd)) {
            throw new AuthnException("mdp invalide");
        }

        $_SESSION['user'] = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ];
    }

}
