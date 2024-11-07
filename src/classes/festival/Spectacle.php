<?php

namespace iutnc\nrv\festival;

use iutnc\nrv\exception\InvalidPropertyNameException;
use iutnc\nrv\festival\Artiste;

class Spectacle
{
    protected string $nom;
    protected string $horaireDebut;
    protected string $horaireFin;
    protected string $style;
    protected string $description;
    protected array $artistes;
    protected array $images;
    protected string $lienAudio;

    public function __construct($nom, $horaireDebut, $horaireFin, $style = "Inconnu", $description = "Aucune description", $artistes = [], $images = [], $lienAudio)
    {
        $this->nom = $nom;
        $this->horaireDebut = $horaireDebut;
        $this->horaireFin = $horaireFin;
        $this->style = $style;
        $this->description = $description;
        $this->artistes = $artistes;
        $this->images = $images;
        $this->lienAudio;
    
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