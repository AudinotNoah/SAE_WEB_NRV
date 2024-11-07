<?php

namespace iutnc\nrv\festival;
use iutnc\nrv\exception\InvalidPropertyNameException;

class Artiste{
    
    protected string $nomArtiste;

    public function __construct(string $nom){
        $this->nomArtiste = $nom;
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