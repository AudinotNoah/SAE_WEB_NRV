<?php

namespace iutnc\nrv\festival;

use iutnc\nrv\exception\InvalidPropertyNameException;

class Lieu
{
    // Propriétés d'un lieu
    protected string $adresse; // Adresse du lieu
    protected string $nomLieu; // Nom du lieu
    protected int $nbPlaceAssises; // Nombre de places assises disponibles
    protected int $nbPlaceDebout; // Nombre de places debout disponibles
    protected string $image; // Image représentant le lieu

    // Constructeur pour initialiser les propriétés du lieu
    public function __construct(string $Padresse, string $PnomLieu, int $PnbPlaceAssises, int $PnbPlaceDebout, string $Pimage)
    {
        $this->adresse = $Padresse; // Initialise l'adresse du lieu
        $this->nomLieu = $PnomLieu; // Initialise le nom du lieu
        $this->nbPlaceAssises = $PnbPlaceAssises; // Initialise le nombre de places assises
        $this->nbPlaceDebout = $PnbPlaceDebout; // Initialise le nombre de places debout
        $this->image = $Pimage; // Initialise l'image du lieu
    }

    // Méthode magique pour accéder aux propriétés
    public function __get(string $property): mixed
    {
        // Vérifie si la propriété demandée existe
        if (property_exists($this, $property)) {
            return $this->$property; // Retourne la valeur de la propriété
        } else {
            // Lance une exception si la propriété n'existe pas
            throw new InvalidPropertyNameException($property);
        }
    }
}
