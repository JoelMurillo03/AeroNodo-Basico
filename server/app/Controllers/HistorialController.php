<?php

namespace App\Controllers;

use App\Models\SensorModel;

class HistorialController extends BaseController
{
    public function index()
    {
        helper('fecha'); 
        $model = new SensorModel();
        $perPage = 15;

        // Obtener parámetros de búsqueda
        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $buscar = $this->request->getGet('buscar');

        $query = $model->orderBy('fecha', 'DESC');

        // Filtro por rango de fechas
        if (!empty($desde)) {
            $query->where('fecha >=', $desde . ' 00:00:00');
        }
        if (!empty($hasta)) {
            $query->where('fecha <=', $hasta . ' 23:59:59');
        }

        // Filtro por ID o texto
        if (!empty($buscar)) {
            if (is_numeric($buscar)) {
                $query->where('id', (int)$buscar);
            } else {
                $query->like('id', $buscar);
            }
        }

        $data['registros'] = $query->paginate($perPage);
        $data['pager'] = $query->pager;
        $data['desde'] = $desde;
        $data['hasta'] = $hasta;
        $data['buscar'] = $buscar;
        $data['total'] = $query->countAllResults(false); // sin resetear

        return view('historial', $data);
    }

    /**
     * Exportar a CSV todos los registros (o filtrados)
     */
    public function exportar()
    {
        helper('fecha');
        $model = new SensorModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $query = $model->orderBy('fecha', 'DESC');

        if (!empty($desde)) {
            $query->where('fecha >=', $desde . ' 00:00:00');
        }
        if (!empty($hasta)) {
            $query->where('fecha <=', $hasta . ' 23:59:59');
        }

        $registros = $query->findAll();

        // Crear CSV
        $filename = 'historial_aeronodo_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Fecha', 'Temperatura (°C)', 'Humedad (%)', 'Presión (hPa)', 'Calidad Aire (ppm)', 'Índice', 'Estado WiFi']);

        foreach ($registros as $row) {
            $fecha = $row['fecha_local'] ?? fecha_local($row['fecha']);
            fputcsv($output, [
                $row['id'],
                $fecha,
                $row['temperatura'],
                $row['humedad'],
                $row['presion'] ?? '',
                $row['calidad_aire'] ?? '',
                $row['indice'] ?? '',
                $row['estado_wifi'] ? 'Conectado' : 'Desconectado'
            ]);
        }

        fclose($output);
        exit;
    }
}