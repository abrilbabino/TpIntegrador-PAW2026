<?php

namespace Paw\App\Models;

use Paw\Core\Model;

use Paw\App\Models\Mascota;

class DiccionarioCollection extends Model
{
    // Obtiene todos los registros de tabla diccionario (especie, tamano, temperamento).

    public function obtenerTodos(string $tabla): array
    {
        $db = $this->getQueryBuilder();
        $resultados = $db->select($tabla, []);
        
        usort($resultados, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });
        
        return array_map(function($fila) {
            $m = new Mascota();
            $m->fields = [
                'id' => (int)$fila['id'],
                'especie' => $fila['nombre'], 
                'tamano' => $fila['nombre'], 
                'temperamento' => $fila['nombre'], 
                'nombre' => $fila['nombre']
            ];
            return $m;
        }, $resultados);
    }

    //Busca el ID de un nombre en el diccionario, si no existe, lo inserta y retorna el nuevo ID.
    
    public function obtenerOCrearId(string $tabla, string $nombre): int
    {
        $db = $this->getQueryBuilder();
        
        $nombreLimpio = ucfirst(strtolower(trim($nombre)));
        
        $existente = $db->select($tabla, ['nombre' => $nombreLimpio]);
        
        if (!empty($existente)) {
            return (int)$existente[0]['id'];
        }

        // Si no existe, lo insertamos
        $db->insert($tabla, ['nombre' => $nombreLimpio]);
        
        // Volvemos a buscarlo para obtener el ID generado
        $nuevo = $db->select($tabla, ['nombre' => $nombreLimpio]);
        
        return (int)$nuevo[0]['id'];
    }
}
