<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Cirugia extends RegistroSanitario
{
    public function getIconoHtml(): string
    {
        return '<span class="material-symbols-outlined">surgical</span>';
    }
}
