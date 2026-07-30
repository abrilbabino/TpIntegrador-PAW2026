<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Chequeo extends RegistroSanitario
{
    public function getIcono(): string
    {
        return 'stethoscope';
    }
}
