<?php

namespace iutnc\nrv\action;

abstract class Action {

    // Déclaration des propriétés protégées
    protected ?string $http_method = null;  // Méthode HTTP (GET, POST, etc.)
    protected ?string $hostname = null;     // Nom de l'hôte (domaine)
    protected ?string $script_name = null;  // Nom du script en cours d'exécution

    // Constructeur qui initialise les propriétés avec des valeurs provenant de la requête HTTP
    public function __construct(){
        // Récupère la méthode HTTP de la requête (GET, POST, etc.)
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        // Récupère le nom de l'hôte (le domaine) à partir de l'en-tête HTTP
        $this->hostname = $_SERVER['HTTP_HOST'];
        // Récupère le nom du script en cours d'exécution à partir de la variable globale $_SERVER
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    // Méthode protégée qui retourne une réponse pour la méthode GET
    protected function get(){
        return "get";  // Retourne simplement le texte "get"
    }

    // Méthode protégée qui retourne une réponse pour la méthode POST
    protected function post(){
        return "post";  // Retourne simplement le texte "post"
    }

    // Méthode publique qui exécute la logique basée sur la méthode HTTP de la requête
    public function execute(): string
    {
        // Vérifie si la méthode HTTP est GET
        if ($this->http_method === 'GET') {
            return $this->get();  // Appelle la méthode GET
        }
        // Vérifie si la méthode HTTP est POST
        elseif ($this->http_method === 'POST') {
            return $this->post();  // Appelle la méthode POST
        } else {
            // Si la méthode HTTP n'est ni GET ni POST, retourne un message d'erreur
            return "Methode inconnu";
        }
    }
}
