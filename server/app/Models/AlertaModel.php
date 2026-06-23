<?php

namespace App\Models;

use CodeIgniter\Model;

class AlertaModel extends Model
{
    protected $table = 'alertas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['lectura_id', 'nivel', 'valor_detectado', 'fecha'];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}