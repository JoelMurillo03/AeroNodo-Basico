<?php

namespace App\Controllers;

use Config\Database;

class DispositivoController extends BaseController
{
    // GET /api/dispositivo
    public function index()
    {
        $db = Database::connect();
        $dispositivo = $db->table('dispositivo')->where('id', 1)->get()->getRowArray();
        if (!$dispositivo) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Dispositivo no encontrado']);
        }
        return $this->response->setJSON($dispositivo);
    }

    // POST /api/dispositivo/update
    public function update()
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'JSON inválido']);
        }

        $data = [];
        if (isset($json['estado_wifi'])) {
            $data['estado_wifi'] = (bool)$json['estado_wifi'];
        }
        if (isset($json['intensidad_wifi'])) {
            $data['intensidad_wifi'] = (int)$json['intensidad_wifi'];
        }
        $data['ultima_actualizacion'] = gmdate('Y-m-d H:i:s');

        if (empty($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'No hay datos para actualizar']);
        }

        $db = Database::connect();
        $db->table('dispositivo')->where('id', 1)->update($data);

        $updated = $db->table('dispositivo')->where('id', 1)->get()->getRowArray();
        return $this->response->setJSON([
            'status'  => 'ok',
            'message' => 'Dispositivo actualizado',
            'data'    => $updated,
        ]);
    }
}