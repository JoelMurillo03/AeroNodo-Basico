<?php

namespace App\Controllers;

use Config\Database;

class AlertasController extends BaseController
{
    // GET /api/alertas
    public function index()
    {
        $db = Database::connect();
        $alertas = $db->table('alertas')
            ->orderBy('fecha', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($alertas);
    }
}