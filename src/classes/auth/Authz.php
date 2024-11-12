<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;


class Authz {

    public static function checkRole($role) {
        try {
            $user = AuthnProvider::getSignedInUser();
            
            if ($user['role'] < $role) {
                return "Role inssufisant";
            }
    
            return $user;
        } catch (AuthnException $e) {
            return $e->getMessage();
        }
    }
    
    

}