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
        
        /** 
         * if ($this->validateTimeFormat($horaireDebut)) {
     *       $this->horaireDebut = $horaireDebut;
      *  } else {
       *     throw new \InvalidArgumentException("Format d'heure invalide pour horaireDebut : $horaireDebut");
       * }
            */
       
       
        $this->horaireDebut = $horaireDebut;
        
        
        /** 
       * if ($this->validateTimeFormat($horaireFin)) {
       *     $this->horaireFin = $horaireFin;
       * } else {
       *     throw new \InvalidArgumentException("Format d'heure invalide pour horaireFin : $horaireFin");
       * }
            */


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

    /**jsp si ca va être utilise #inshallah
    private function validateTimeFormat(string $time): bool
{
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
}
    */
}