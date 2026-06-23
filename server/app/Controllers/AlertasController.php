<?php

namespace App\Controllers;

use App\Models\AlertaModel;
use Config\Database;

class AlertasController extends BaseController
{
    // GET /api/alertas (para el dashboard - últimas 10 activas)
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

    // GET /alertas (vista completa)
    public function listado()
    {
        $model = new AlertaModel();
        $perPage = 15;

        $nivel = $this->request->getGet('nivel');
        $buscar = $this->request->getGet('buscar');

        // Construir la consulta con el modelo
        $query = $model->orderBy('alertas.fecha', 'DESC');

        // JOIN con lecturas para mostrar datos adicionales (opcional)
        // Si no necesitas los datos de lecturas, puedes omitir el join
        // Pero si quieres mostrar temperatura/humedad relacionada, lo dejamos:
        $query->join('lecturas', 'lecturas.id = alertas.lectura_id', 'left');

        // Filtros
        if (!empty($nivel) && $nivel !== 'todas') {
            $query->where('alertas.nivel', $nivel);
        }
        if (!empty($buscar)) {
            $query->groupStart()
                ->like('alertas.id', $buscar)
                ->orLike('alertas.nivel', $buscar)
                ->orLike('alertas.valor_detectado', $buscar)
                ->groupEnd();
        }

        // Paginación (AHORA SÍ FUNCIONA)
        $data['alertas'] = $query->paginate($perPage);
        $data['pager']   = $model->pager;
        $data['nivel']   = $nivel;
        $data['buscar']  = $buscar;

        // Contador de alertas activas para el badge
        $db = Database::connect();
        $activas = $db->table('alertas')->where('nivel !=', 'normal')->countAllResults();
        $data['alertas_activas'] = $activas;

        return view('alertas', $data);
    }

    // DELETE /alertas/(:num)
    public function delete($id)
    {
        $model = new AlertaModel();
        if (!$model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Alerta no encontrada']);
        }

        $model->delete($id);
        return $this->response->setJSON([
            'status'  => 'ok',
            'message' => 'Alerta eliminada'
        ]);
    }

    // POST /alertas/marcar-leida/(:num)
    public function marcarLeida($id)
    {
        // Si se quiere implementar "leída", se podría agregar un campo `leida` en la tabla
        // Por ahora, simplemente eliminamos (o se puede cambiar la lógica)
        return $this->delete($id);
    }
}