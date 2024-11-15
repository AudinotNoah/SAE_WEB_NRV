<?php

namespace iutnc\nrv\exception;

use Exception;

// Exception personnalisée pour les erreurs d'authentification
class AuthnException extends Exception
{
    // Constructeur de l'exception
    public function __construct(string $property)
    {
        // Appelle le constructeur parent avec un message personnalisé
        parent::__construct("Erreur connection : $property");
    }
}
