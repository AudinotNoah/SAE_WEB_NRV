<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action {

    protected function get(): string
    {
        return "
        <div class='background-image content'>
            <h2 class='title is-2 has-text-black'>Bienvenue au Festival NRV !</h2>
            <p class='is-size-5 has-text-black'>Le festival NRV à Nancy est l'événement musical de l'année. Rejoignez-nous pour une expérience inoubliable !</p>
        </div>";
    }
}
