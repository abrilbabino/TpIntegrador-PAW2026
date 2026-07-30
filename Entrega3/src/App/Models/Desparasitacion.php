<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Desparasitacion extends RegistroSanitario
{
    public function getIcono(): string
    {
        return 'pest_control';
    }
}
