<?php

namespace iutnc\nrv\exception;

use Exception;

// Exception personnalisée pour les noms de propriétés invalides
class InvalidPropertyNameException extends Exception
{
    // Constructeur de l'exception
    public function __construct(string $property)
    {
        // Appelle le constructeur parent avec un message personnalisé
        parent::__construct("Invalid property name: $property");
    }
}
