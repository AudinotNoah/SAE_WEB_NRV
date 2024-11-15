<?php

namespace iutnc\nrv\festival;

use iutnc\nrv\exception\InvalidPropertyNameException;

class Soiree
{
    // Propriétés de la soirée
    protected string $nomSoiree; // Nom de la soirée
    protected string $dateSoiree; // Date de la soirée (exemple : 07-11-2024)
    protected string $lieu; // Lieu où se déroule la soirée
    protected float $tarif; // Tarif d'entrée pour la soirée
    protected string $thematique; // Thématique de la soirée
    protected string $horaire; // Horaire de la soirée

    // Constructeur pour initialiser les propriétés
    public function __construct(string $ns, $ds, $l, $th, $h, float $t)
    {
        $this->nomSoiree = $ns; // Initialise le nom de la soirée
        $this->dateSoiree = $ds; // Initialise la date
        $this->lieu = $l; // Initialise le lieu
        $this->tarif = $t; // Initialise le tarif
        $this->thematique = $th; // Initialise la thématique
        $this->horaire = $h; // Initialise l'horaire
    }

    // Méthode magique pour accéder aux propriétés
    public function __get($property)
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
