<?php

namespace iutnc\deefy\render;

interface Renderer
{
    public function render(int $type): string;
}