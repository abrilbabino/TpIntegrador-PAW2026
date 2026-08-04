<?php

namespace Paw\App\Helpers;

class DateHelper
{
    public static function formatEdad($fechaNac, $edadFallback = 0)
    {
        if (empty($fechaNac)) {
            $edadFallback = $edadFallback ?? 0;
            return $edadFallback . ($edadFallback == 1 ? ' año' : ' años');
        }
        try {
            $d = new \DateTime($fechaNac);
            $hoy = new \DateTime();
            $diff = $hoy->diff($d);
            if ($diff->y >= 1) {
                return $diff->y . ($diff->y == 1 ? ' año' : ' años');
            } elseif ($diff->m >= 1) {
                return $diff->m . ($diff->m == 1 ? ' mes' : ' meses');
            } else {
                return $diff->d . ($diff->d == 1 ? ' día' : ' días');
            }
        } catch (\Exception $e) {
            $edadFallback = $edadFallback ?? 0;
            return $edadFallback . ($edadFallback == 1 ? ' año' : ' años');
        }
    }
}
