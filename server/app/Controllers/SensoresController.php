<?php

namespace App\Controllers;

use App\Models\SensorModel;
use CodeIgniter\I18n\Time;
use Config\Database;

class SensoresController extends BaseController
{
    // POST /api/sensores
    public function store()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'No se recibió JSON válido']);
        }

        $data = [
            'temperatura'   => isset($json['temperatura']) ? round((float)$json['temperatura'], 2) : null,
            'humedad'       => isset($json['humedad']) ? round((float)$json['humedad'], 2) : null,
            'presion'       => isset($json['presion']) ? round((float)$json['presion'], 2) : null,
            'calidad_aire'  => isset($json['calidad_aire']) ? (int)$json['calidad_aire'] : null,
            'estado_wifi'   => isset($json['estado_wifi']) ? (bool)$json['estado_wifi'] : true,
            'indice'        => isset($json['indice']) ? round((float)$json['indice'], 2) : null,
            'fecha'         => gmdate('Y-m-d H:i:s'),
        ];

        // Validar datos mínimos
        if ($data['temperatura'] === null || $data['humedad'] === null) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'Faltan datos: temperatura y humedad son obligatorios']);
        }

        $model = new SensorModel();
        try {
            $id = $model->insert($data);
            $registro = $model->find($id);

            // Generar alertas si calidad_aire supera umbrales
            if (isset($data['calidad_aire']) && $data['calidad_aire'] > 500) {
                $this->generarAlerta($id, $data['calidad_aire']);
            }

            return $this->response->setStatusCode(201)
                ->setJSON([
                    'status'  => 'ok',
                    'message' => 'Datos almacenados',
                    'id'      => $id,
                    'data'    => $registro,
                ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // GET /api/sensores  (últimas 10 lecturas)
    public function index()
    {
        $model = new SensorModel();
        $data = $model->orderBy('id', 'DESC')->limit(10)->findAll();
        return $this->response->setJSON($data);
    }

    // GET /api/historial
    public function historial()
    {
        $model = new SensorModel();
        $data = $model->orderBy('id', 'DESC')->findAll();
        return $this->response->setJSON($data);
    }

    // GET /sensores/ultimo
    public function latest()
    {
        helper('fecha');
        $model = new SensorModel();
        $data = $model->orderBy('id', 'DESC')->first();
        if (!$data) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'No hay registros']);
        }
        $data['fecha_local'] = fecha_local($data['fecha']);
        return $this->response->setJSON($data);
    }

    // GET /sensores/recientes/(:num)
    public function recent(int $lim = 10)
    {
        helper('fecha');
        $model = new SensorModel();
        $data = $model->orderBy('id', 'DESC')->limit($lim)->findAll();
        $data = array_reverse($data); // orden ascendente
        foreach ($data as &$row) {
            $row['fecha_local'] = fecha_local($row['fecha']);
        }
        return $this->response->setJSON($data);
    }

    // GET /sensores/(:num)
    public function show($id)
    {
        helper('fecha');
        $model = new SensorModel();
        $data = $model->find($id);
        if (!$data) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Registro no encontrado']);
        }
        $data['fecha_local'] = fecha_local($data['fecha']);
        return $this->response->setJSON($data);
    }

    // PUT /sensores/(:num)
    public function update($id)
    {
        $model = new SensorModel();
        $sensor = $model->find($id);
        if (!$sensor) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Registro no encontrado']);
        }

        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'No se recibió JSON válido']);
        }

        $data = [];
        $allowed = ['temperatura', 'humedad', 'presion', 'calidad_aire', 'estado_wifi', 'indice'];
        foreach ($allowed as $field) {
            if (isset($json[$field])) {
                $data[$field] = is_numeric($json[$field]) ? (float)$json[$field] : $json[$field];
            }
        }

        if (empty($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'No se enviaron datos para actualizar']);
        }

        try {
            $model->update($id, $data);
            $actualizado = $model->find($id);
            return $this->response->setJSON([
                'status'  => 'ok',
                'message' => 'Registro actualizado',
                'data'    => $actualizado,
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // DELETE /sensores/(:num)
    public function delete($id)
    {
        $model = new SensorModel();
        $sensor = $model->find($id);
        if (!$sensor) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Registro no encontrado']);
        }

        try {
            $model->delete($id);
            return $this->response->setJSON([
                'status'  => 'ok',
                'message' => 'Registro eliminado',
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // -------------------- Privados --------------------
    private function generarAlerta($lectura_id, $valor)
    {
        $db = Database::connect();
        $nivel = 'normal';
        if ($valor > 800) {
            $nivel = 'critica';
        } elseif ($valor > 500) {
            $nivel = 'moderada';
        } else {
            return; // no insertar alertas normales
        }

        $db->table('alertas')->insert([
            'lectura_id'     => $lectura_id,
            'nivel'          => $nivel,
            'valor_detectado' => $valor,
            'fecha'          => gmdate('Y-m-d H:i:s'),
        ]);
    }
}