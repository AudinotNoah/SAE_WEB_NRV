<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action {

    protected function get(): string
    {
        return "
        <div class='content has-text-centered' style='
            background-image: url(\"src/assets/images/siteImage/backAccueil.jpg\");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        '>
            <h2 class='title is-2 has-text-black'>Bienvenue au Festival NRV !</h2>
            <p class='is-size-5 has-text-black'>
                Le festival NRV à Nancy est l'événement musical de l'année. Rejoignez-nous pour une expérience inoubliable !
            </p>
        </div>";
    }
}
