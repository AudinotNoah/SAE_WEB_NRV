<?php

namespace iutnc\nrv\festival;

use iutnc\deefy\exception\InvalidPropertyNameException;

class Spectacle
{
    protected string $nom;
    protected string $horaireDebut;
    protected string $horaireFin;
    protected string $style;
    protected string $description;

    public function __construct($nom, $horaireDebut, $horaireFin, $style = "Inconnu", $description = "Aucune description")
    {
        $this->nom = $nom;
        $this->horaireDebut = $horaireDebut;
        $this->horaireFin = $horaireFin;
        $this->style = $style;
        $this->description = $description;
    }

    public function __get(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new InvalidPropertyNameException($property);
        }
    }

}