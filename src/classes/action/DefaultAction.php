<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action {
    
    protected function get(): string
    {   
        return "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport'' content='width=device-width, initial-scale=1.0'>
    <title>Festival NRV - Nancy</title>
    <style>
    /* Styles basiques pour le menu principal */
    body {
        font-family: Arial, sans-serif;
            background-color: #333;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        
        header {
        background-color: #444;
            padding: 20px;
            text-align: center;
        }
        
        header h1 {
        margin: 0;
        font-size: 2em;
        }

        nav {
        background-color: #555;
            display: flex;
            justify-content: center;
            padding: 15px;
        }

        nav a {
        color: #fff;
        text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
        background-color: #777;
        }

        .content {
        padding: 20px;
            text-align: center;
        }

        .content h2 {
        color: #ddd;
        font-size: 1.5em;
        }

        .content p {
        color: #aaa;
    }
    </style>
</head>
<body>
    <!-- En-tête avec le titre du festival -->
    <header>
        <h1>Festival NRV - Nancy</h1>
    </header>

    <!-- Menu principal -->
    <nav>
        <a href='?action=programme'>Programme</a>
        <a href='?action=artistes'>Artistes</a>
        <a href='?action=infos'>Infos pratiques</a>
        <a href='?action=contact'>Contact</a>
        <a href='?action=login'>Connexion</a>
    </nav>

    <!-- Contenu de la page par défaut -->
    <div class='content'>
        <h2>Bienvenue au Festival NRV !</h2>
        <p>Le festival NRV à Nancy est l'événement musical de l'année. Rejoignez-nous pour une expérience inoubliable !</p>
    </div>
</body>
</html>
";
    }
}