<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Tratamiento extends RegistroSanitario
{
    public function getIcono(): string
    {
        return 'pill';
    }
}
