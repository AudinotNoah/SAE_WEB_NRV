<?php

namespace iutnc\nrv\festival;

use iutnc\nrv\exception\InvalidPropertyNameException;

class Soiree
{
    protected string $nomSoiree;
    protected string $dateSoiree; // exemple : 07-11-2024
    protected string $lieu;
    protected float $tarif;
    protected string $thematique;
    protected string $horaire;

    public function __construct(string $ns, $ds, $l, $th, $h, float $t)
    {
        $this->nomSoiree = $ns;
        $this->dateSoiree = $ds;
        $this->lieu = $l;
        $this->tarif = $t;
        $this->thematique = $th;
        $this->horaire = $h;
    }

    public function __get($property)
    {
        if(property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new InvalidPropertyNameException($property);
        }
    }


}