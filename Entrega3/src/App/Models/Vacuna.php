<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Vacuna extends RegistroSanitario
{
    public function getIcono(): string
    {
        return 'syringe';
    }
}
