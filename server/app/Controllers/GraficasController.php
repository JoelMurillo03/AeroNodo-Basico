<?php

namespace App\Controllers;

use App\Models\SensorModel;

class GraficasController extends BaseController
{
    public function index()
    {
        $model = new SensorModel();
        $limite = 100; // podemos mostrar más datos

        $registros = $model->orderBy('fecha', 'ASC')->limit($limite)->findAll();

        // Preparar datos para gráficas
        $data = [];
        $data['fechas'] = array_map(function($row) {
            return $row['fecha_local'] ?? $row['fecha'];
        }, $registros);

        $data['temperaturas'] = array_column($registros, 'temperatura');
        $data['humedades'] = array_column($registros, 'humedad');
        $data['presiones'] = array_column($registros, 'presion');
        $data['calidades'] = array_column($registros, 'calidad_aire');
        $data['indices'] = array_column($registros, 'indice');

        // Datos para gráfica de barras (promedios diarios o por hora)
        $data['promedios_diarios'] = $this->calcularPromediosDiarios($registros);

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