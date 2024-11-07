<?php

namespace iutnc\nrv\dispatch;


use iutnc\nrv\action\DefaultAction;
use iutnc\nrv\action\DisplaySpectablesAction;
use iutnc\nrv\action\LoginAction;

class Dispatcher {

    private string $action;
    
    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = ''; 

        switch ($this->action) {

            case("programme"):
                $actionInstance = new DisplaySpectablesAction();
                $html = $actionInstance->execute();
                break;

            case("login"):
                $actionInstance = new LoginAction();
                $html = $actionInstance->execute();
                break;

            default:
                $actionInstance = new DefaultAction();
                $html = $actionInstance->execute();
                break;
        }

        $this->renderPage($html);
    }

    private function renderPage(string $html): void {

        echo $html;
    }
    
    
}