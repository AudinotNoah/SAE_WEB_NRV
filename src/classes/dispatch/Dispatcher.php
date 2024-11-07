<?php

namespace iutnc\nrv\dispatch;


use iutnc\nrv\action\DefaultAction;
use iutnc\nrv\action\DisplaySpectablesAction;

class Dispatcher {

    private string $action;
    
    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = ''; 

        switch ($this->action) {

            case 'programme':
                $actionInstance = new DisplaySpectaclesAction();
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