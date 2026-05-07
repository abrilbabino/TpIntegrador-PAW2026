<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Tratamiento extends RegistroSanitario
{
    public function getIconoHtml(): string
    {
        return '<span class="material-symbols-outlined">pill</span>';
    }
}
