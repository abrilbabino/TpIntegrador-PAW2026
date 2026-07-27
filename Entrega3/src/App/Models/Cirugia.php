<?php

declare(strict_types=1);

namespace Paw\App\Models;

class Cirugia extends RegistroSanitario
{
    public function getIcono(): string
    {
        return 'surgical';
    }
}
