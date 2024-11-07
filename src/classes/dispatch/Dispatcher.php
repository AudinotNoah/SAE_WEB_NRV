<?php

namespace iutnc\nrv\dispatch;


class Dispatcher {

    private string $action;
    
    public function __construct(string $action) {
        $this->action = $action;
    }

    public function run(): void {
        $html = ''; 

        switch ($this->action) {
            case 'default':
                $actionInstance = new DefaultAction();
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
        
        $menu = <<<HTML
        HTML;
    
        echo $menu;
    }
    
    
}