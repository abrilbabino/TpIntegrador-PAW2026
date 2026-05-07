<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Chequeo extends RegistroSanitario
{
    public function getIconoHtml(): string
    {
        return '<span class="material-symbols-outlined">stethoscope</span>';
    }
}
