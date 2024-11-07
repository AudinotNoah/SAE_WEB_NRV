<?php

namespace iutnc\nrv\action;

abstract class Action {

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;
   
    public function __construct(){
        
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    protected function get(){
        return "get";
    }
    protected function post(){
        return "post";
    }

    
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return $this->get();
        } elseif ($this->http_method === 'POST') {
            return $this->post();
        } else {
            return "Methode inconnu";
        }
    }
    
}