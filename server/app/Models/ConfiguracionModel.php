<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfiguracionModel extends Model
{
    protected $table            = 'configuraciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['clave', 'valor', 'descripcion'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene el valor de una configuración por su clave
     */
    public function getValor(string $clave, $default = null)
    {
        $row = $this->where('clave', $clave)->first();
        return $row ? $row['valor'] : $default;
    }

    /**
     * Actualiza o crea una configuración
     */
    public function setValor(string $clave, $valor, ?string $descripcion = null)
    {
        $data = ['valor' => $valor];
        if ($descripcion !== null) {
            $data['descripcion'] = $descripcion;
        }

        $existing = $this->where('clave', $clave)->first();
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['clave'] = $clave;
            return $this->insert($data);
        }
    }

    /**
     * Obtiene todas las configuraciones como array clave => valor
     */
    public function getAllAsArray(): array
    {
        $result = [];
        $rows = $this->findAll();
        foreach ($rows as $row) {
            $result[$row['clave']] = $row['valor'];
        }
        return $result;
    }
}