<?php

namespace Paw\App\Helpers;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Paw\App\Helpers\DateHelper;

class TwigExtensions extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('edad_formateada', [self::class, 'formatEdad']),
        ];
    }

    public static function formatEdad($fechaNac, $edadFallback = 0)
    {
        return DateHelper::formatEdad($fechaNac, $edadFallback);
    }
}
