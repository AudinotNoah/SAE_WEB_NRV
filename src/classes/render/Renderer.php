<?php

namespace iutnc\nrv\render;

interface Renderer
{
    public function render(int $type): string;
}