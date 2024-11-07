<?php

namespace iutnc\nrv\render;

interface Renderer
{
    const COMPACT = 2;
    const LONG = 1;
    public function render(int $type): string;
}