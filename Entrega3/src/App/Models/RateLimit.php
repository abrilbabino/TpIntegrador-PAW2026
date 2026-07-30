<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class RateLimit extends Model
{
    protected $table = 'login_attempts';
    protected $fields = [
        'username' => null,
        'intentos' => 0,
        'bloqueado_hasta' => null
    ];

    /**
     * Revisa si un usuario está actualmente bloqueado.
     * Si no está bloqueado, retorna 0.
     * Si está bloqueado, retorna los minutos restantes de bloqueo.
     */
    public function obtenerMinutosRestantesBloqueo(string $username): int
    {
        $registro = $this->queryBuilder->selectOne($this->table, ['username' => $username]);
        
        if ($registro && $registro['bloqueado_hasta'] !== null) {
            $bloqueadoHasta = new \DateTime($registro['bloqueado_hasta']);
            $ahora = new \DateTime();
            
            if ($bloqueadoHasta > $ahora) {
                $interval = $ahora->diff($bloqueadoHasta);
                $minutos = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                return $minutos > 0 ? $minutos : 1;
            }
        }
        
        return 0;
    }

    /**
     * Registra un intento fallido y actualiza la tabla.
     * Calcula penalizaciones progresivas.
     */
    public function registrarIntentoFallido(string $username): void
    {
        $registro = $this->queryBuilder->selectOne($this->table, ['username' => $username]);

        if (!$registro) {
            $this->queryBuilder->insert($this->table, [
                'username' => $username,
                'intentos' => 1,
                'bloqueado_hasta' => null
            ]);
            return;
        }

        $nuevosIntentos = $registro['intentos'] + 1;
        
        $bloqueadoHasta = null;
        if ($nuevosIntentos == 5) {
            $bloqueadoHasta = (new \DateTime())->modify('+1 minute')->format('Y-m-d H:i:s');
        } elseif ($nuevosIntentos == 6) {
            $bloqueadoHasta = (new \DateTime())->modify('+5 minutes')->format('Y-m-d H:i:s');
        } elseif ($nuevosIntentos == 7) {
            $bloqueadoHasta = (new \DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
        } elseif ($nuevosIntentos >= 8) {
            $bloqueadoHasta = (new \DateTime())->modify('+60 minutes')->format('Y-m-d H:i:s');
        }

        $this->queryBuilder->update($this->table, [
            'intentos' => $nuevosIntentos,
            'bloqueado_hasta' => $bloqueadoHasta
        ], ['username' => $username]);
    }

    /**
     * Resetea los intentos tras un login exitoso.
     */
    public function resetearIntentos(string $username): void
    {
        $registro = $this->queryBuilder->selectOne($this->table, ['username' => $username]);
        if ($registro && $registro['intentos'] > 0) {
            $this->queryBuilder->update($this->table, [
                'intentos' => 0,
                'bloqueado_hasta' => null
            ], ['username' => $username]);
        }
    }
}
