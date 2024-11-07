<?php

namespace iutnc\nrv\festival;
use iutnc\nrv\exception\InvalidPropertyNameException;

class Lieu{
    protected string $adresse;
    protected string $nomLieu;
    protected int $nbPlaceAssises;
    protected int $nbPlaceDebout;
    protected string $image;

    public function __construct(string $Padresse,string $PnomLieu,int $PnbPlaceAssises,int $PnbPlaceDebout,string $Pimage){
        $this->adresse = $Padresse;
        $this->nomLieu = $PnomLieu;
        $this->nbPlaceAssises = $PnbPlaceAssises;
        $this->nbPlaceDebout = $PnbPlaceDebout;
        $this->image = $Pimage;
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