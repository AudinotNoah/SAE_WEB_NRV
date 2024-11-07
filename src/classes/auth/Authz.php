<?php

namespace iutnc\nrv\auth;



class Authz {

    public static function checkRole(): bool {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        return $_SESSION['user']['role'] === 'admin';
    }

}