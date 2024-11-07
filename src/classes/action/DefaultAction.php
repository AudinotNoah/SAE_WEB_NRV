<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action {
    
    protected function get(): string
    {   return "
    <div class='content'>
        <h2>Bienvenue au Festival NRV !</h2>
        <p>Le festival NRV à Nancy est l'événement musical de l'année. Rejoignez-nous pour une expérience inoubliable !</p>
    </div>";
    }
}