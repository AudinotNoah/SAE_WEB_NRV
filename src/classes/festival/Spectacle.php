<?php

namespace iutnc\nrv\festival;

use iutnc\nrv\exception\InvalidPropertyNameException;
use iutnc\nrv\festival\Artiste;

class Spectacle
{
    // Déclaration des propriétés du spectacle
    protected string $nom; // Nom du spectacle
    protected string $horaireDebut; // Heure de début
    protected string $horaireFin; // Heure de fin
    protected string $style; // Style musical ou artistique
    protected string $description; // Description du spectacle
    protected array $artistes; // Liste des artistes participant
    protected array $images; // Liste des images associées
    protected string $lienAudio; // Lien vers un extrait audio
    protected string $statut; // Statut du spectacle (ex : complet, disponible)

    // Constructeur pour initialiser un spectacle avec des valeurs par défaut si certaines informations ne sont pas fournies
    public function __construct(
        $nom,
        $horaireDebut,
        $horaireFin,
        $style = "Inconnu",
        $description = "Aucune description",
        $artistes = [],
        $images = [],
        $lienAudio,
        $statut = " "
    ) {
        $this->nom = $nom;
        $this->horaireDebut = $horaireDebut;
        $this->horaireFin = $horaireFin;
        $this->style = $style;
        $this->description = $description;
        $this->artistes = $artistes;
        $this->images = $images;
        $this->lienAudio = $lienAudio;
        $this->statut = $statut;
    }

    // Méthode magique pour accéder aux propriétés de l'objet
    public function __get(string $property): mixed
    {
        // Vérifie si la propriété demandée existe
        if (property_exists($this, $property)) {
            return $this->$property; // Retourne la valeur de la propriété
        } else {
            // Si la propriété n'existe pas, lève une exception personnalisée
            throw new InvalidPropertyNameException($property);
        }
    }
}
