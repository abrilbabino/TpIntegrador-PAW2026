<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Vacuna extends RegistroSanitario
{
    public function getIconoHtml(): string
    {
        return '<span class="material-symbols-outlined">syringe</span>';
    }
}
