<?php

namespace iutnc\nrv\action;

class DefaultAction extends Action {
    
    protected function get(): string
    {   
        return "<header>
                <h1>Bienvenue sur NRV</h1>
            </header>";
    }
}