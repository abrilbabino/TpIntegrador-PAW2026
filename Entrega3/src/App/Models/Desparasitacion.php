<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Desparasitacion extends RegistroSanitario
{
    public function getIconoHtml(): string
    {
        return '<span class="material-symbols-outlined">pest_control</span>';
    }
}
