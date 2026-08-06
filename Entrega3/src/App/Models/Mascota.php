<?php

namespace Paw\App\Models;

use Paw\Core\Model;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\ModelNotFoundException;

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
        'fecha_nacimiento' => null,
        'tamano' => null,
        'temperamento' => null,
        'estado_adopcion' => null,
        'vacunado' => null,
        'castrado' => null,
        'imagen' => 'default-pet.jpg',
        'sexo' => 'Desconocido',
        'fecha_adopcion' => null,
        'svg' => null,
        'fecha_publicacion' => null,
        'visitas' => 0,
        'ideal_depto'    => false,
        'convive_perros' => false,
        'convive_gatos'  => false,
        'apto_ninos'     => false,
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
        } else {
            throw new ModelNotFoundException("No se encontró una mascota con el ID proporcionado");
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

    public static function validarEdicion(array $post, array $opcionesPermitidas): array
    {
        $errores = [];

        $nombre = trim($post['nombre'] ?? '');
        $largoNombre = function_exists('mb_strlen') ? mb_strlen($nombre) : strlen($nombre);
        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif ($largoNombre < 2 || $largoNombre > 60) {
            $errores['nombre'] = 'El nombre debe tener entre 2 y 60 caracteres.';
        } elseif (!preg_match("/^[\\p{L}\\s'-]+$/u", $nombre)) {
            $errores['nombre'] = 'Solo se permiten letras, espacios, apóstrofes y guiones.';
        }

        $especie = trim($post['especie'] ?? '');
        if ($especie === '') {
            $errores['especie'] = 'Debe seleccionar una especie.';
        } elseif (!in_array(strtolower($especie), $opcionesPermitidas['especies'], true)) {
            $errores['especie'] = 'La especie seleccionada no es válida.';
        }

        $tamanio = trim($post['tamanio'] ?? '');
        if ($tamanio === '') {
            $errores['tamanio'] = 'Debe seleccionar un tamaño.';
        } elseif (!in_array(strtolower($tamanio), $opcionesPermitidas['tamanos'], true)) {
            $errores['tamanio'] = 'El tamaño seleccionado no es válido.';
        }

        $temperamento = trim($post['temperamento'] ?? '');
        if ($temperamento === '') {
            $errores['temperamento'] = 'Debe seleccionar un temperamento.';
        } elseif (!in_array(strtolower($temperamento), $opcionesPermitidas['temperamentos'], true)) {
            $errores['temperamento'] = 'El temperamento seleccionado no es válido.';
        }

        $sexo = trim($post['sexo'] ?? '');
        if (!in_array($sexo, ['macho', 'hembra'], true)) {
            $errores['sexo'] = 'Debe seleccionar un sexo válido.';
        }

        $esterilizado = trim($post['esterilizado'] ?? '');
        if (!in_array($esterilizado, ['si', 'no'], true)) {
            $errores['esterilizado'] = 'Debe indicar si la mascota está esterilizada.';
        }

        $descripcionMascota = trim($post['descripcion_mascota'] ?? '');
        $largoDescripcion = function_exists('mb_strlen') ? mb_strlen($descripcionMascota) : strlen($descripcionMascota);
        if ($descripcionMascota === '') {
            $errores['descripcion_mascota'] = 'La descripción es obligatoria.';
        } elseif ($largoDescripcion < 10 || $largoDescripcion > 500) {
            $errores['descripcion_mascota'] = 'La descripción debe tener entre 10 y 500 caracteres.';
        }

        $fechaNac = trim($post['fecha_nacimiento'] ?? '');
        if ($fechaNac === '') {
            $errores['fecha_nacimiento'] = 'La fecha de nacimiento es obligatoria.';
        } else {
            $d = \DateTime::createFromFormat('Y-m-d', $fechaNac);
            if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                $errores['fecha_nacimiento'] = 'La fecha de nacimiento no es válida.';
            } else {
                $hoy = new \DateTime();
                if ($d > $hoy) {
                    $errores['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
                }
            }
        }

        return $errores;
    }
}
