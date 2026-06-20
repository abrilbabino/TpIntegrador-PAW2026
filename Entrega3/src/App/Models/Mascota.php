<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\MascotaNotFoundException;

class Mascota extends Model
{
    public $table = 'mascota';
    
    public $fields = [
        'id' => null,
        'refugio_id' => null,
        'nombre' => null,
        'especie' => null,
        'descripcion' => null,
        'edad' => null,
        'tamano' => null,
        'temperamento' => null,
        'estado_adopcion' => null,
        'vacunado' => null,
        'castrado' => null,
        'imagen' => 'default-pet.jpg',
        'sexo' => 'Desconocido',
        'fecha_adopcion' => null,
        'svg' => null,
    ];

    public function setId($id){
        if(!is_numeric($id) || $id < 0){
            throw new InvalidValueFormatException("El ID de la mascota debe ser un entero mayor a 0");
        }
        $this->fields['id'] = $id;
    }

    public function setNombre(string $nombre){
        $this->fields['nombre'] = $nombre;
    }

    public function setImagen(string $imagen){
        $this->fields['imagen'] = $imagen ?? 'default-pet.jpg';
    }

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!isset($values[$field])) {
                continue;
            }
            $method = "set" . ucfirst($field);
            if (method_exists($this, $method)) {
                $this->$method($values[$field]);
            } else {
                $this->fields[$field] = $values[$field];
            }
        }
    }

    public function load($id){
        if(!is_numeric($id) || $id < 0){
            throw new InvalidValueFormatException("El ID de la mascota debe ser un entero mayor a 0");
        }
        $params = ['id' => $id];
        $record = current($this->queryBuilder->select($this->table, $params));
        if ($record) {
            $this->set($record);
        }
        else{
            throw new MascotaNotFoundException("No se encontró una mascota con el ID proporcionado");
        }
    }

    public static function validarArchivoSvg(array $archivo): ?string
    {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null; // Sin archivo subido, no es error
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        $mime      = file_exists($archivo['tmp_name']) ? mime_content_type($archivo['tmp_name']) : '';
        $contenido = file_exists($archivo['tmp_name'])
            ? file_get_contents($archivo['tmp_name'], false, null, 0, 512)
            : '';

        $esValido = $extension === 'svg'
            && in_array($mime, ['image/svg+xml', 'text/xml', 'text/plain', 'application/xml'], true)
            && stripos($contenido, '<svg') !== false;

        return $esValido ? null : 'El archivo no es un SVG válido.';
    }
}
