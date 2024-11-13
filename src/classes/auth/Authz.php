<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;


class Authz {

    public static function checkRole($role) {
        try {
            $user = AuthnProvider::getSignedInUser();
            
            if ((int) $user['droit'] >= $role) {
                return $user;
            }

            return "Droit insuffisant";
        } catch (AuthnException $e) {
            return $e->getMessage();
        }
    }
    
    

}