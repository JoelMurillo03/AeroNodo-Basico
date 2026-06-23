<?php

namespace App\Controllers;

use App\Models\SensorModel;
use App\Models\ConfiguracionModel; // <--- ¡IMPORTANTE! Agregar esta línea

class GraficasController extends BaseController
{
    public function index()
    {
        // 1. Obtener configuraciones (umbrales)
        $configModel = new ConfiguracionModel();
        $configs = $configModel->getAllAsArray();

        // 2. Obtener datos de sensores (últimos 100 registros)
        $model = new SensorModel();
        $limite = 100;

        $registros = $model->orderBy('fecha', 'ASC')->limit($limite)->findAll();

        // Preparar datos para gráficas
        $data['fechas'] = array_map(function($row) {
            return $row['fecha_local'] ?? $row['fecha'];
        }, $registros);

        $data['temperaturas'] = array_column($registros, 'temperatura');
        $data['humedades'] = array_column($registros, 'humedad');
        $data['presiones'] = array_column($registros, 'presion');
        $data['calidades'] = array_column($registros, 'calidad_aire');
        $data['indices'] = array_column($registros, 'indice');

        // Datos para gráfica de barras (promedios diarios)
        $data['promedios_diarios'] = $this->calcularPromediosDiarios($registros);

        // 3. Agregar configuraciones a los datos que se pasan a la vista
        $data['configs'] = $configs;

        // 4. Un solo return view con todos los datos
        return view('graficas', $data);
    }

    private function calcularPromediosDiarios($registros)
    {
        $diarios = [];
        foreach ($registros as $row) {
            $fecha = substr($row['fecha'], 0, 10);
            if (!isset($diarios[$fecha])) {
                $diarios[$fecha] = ['temp' => [], 'hum' => [], 'pres' => [], 'cal' => [], 'ind' => []];
            }
            $diarios[$fecha]['temp'][] = $row['temperatura'];
            $diarios[$fecha]['hum'][] = $row['humedad'];
            $diarios[$fecha]['pres'][] = $row['presion'];
            $diarios[$fecha]['cal'][] = $row['calidad_aire'];
            $diarios[$fecha]['ind'][] = $row['indice'];
        }

        $resultado = [];
        foreach ($diarios as $fecha => $valores) {
            $resultado[$fecha] = [
                'temp' => array_sum($valores['temp']) / count($valores['temp']),
                'hum' => array_sum($valores['hum']) / count($valores['hum']),
                'pres' => array_sum($valores['pres']) / count($valores['pres']),
                'cal' => array_sum($valores['cal']) / count($valores['cal']),
                'ind' => array_sum($valores['ind']) / count($valores['ind']),
            ];
        }
        return $resultado;
    }
}