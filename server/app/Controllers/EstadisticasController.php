<?php

namespace App\Controllers;

use App\Models\SensorModel;
use HiFolks\Statistics\Stat;
use HiFolks\Statistics\Freq;

class EstadisticasController extends BaseController
{
    // GET /estadisticas/resumen
    public function resumen()
    {
        $model = new SensorModel();
        $data = $model->orderBy('id', 'DESC')->limit(30)->findAll();

        if (empty($data)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'No hay datos para analizar']);
        }

        // Extraer variables
        $temperaturas = array_map('floatval', array_column($data, 'temperatura'));
        $humedades    = array_map('floatval', array_column($data, 'humedad'));
        $presiones    = array_map('floatval', array_column($data, 'presion'));
        $calidades    = array_map('intval', array_column($data, 'calidad_aire'));
        $indices      = array_map('floatval', array_column($data, 'indice'));

        // Calcular resumen para cada variable
        $resumenTemp   = $this->calcularResumen($temperaturas);
        $resumenHum    = $this->calcularResumen($humedades);
        $resumenPres   = $this->calcularResumen($presiones);
        $resumenCal    = $this->calcularResumen($calidades);
        $resumenInd    = $this->calcularResumen($indices);

        // Correlaciones y regresiones (pares)
        $correlaciones = [
            'temperatura_humedad'     => $this->calcularRelacion($temperaturas, $humedades),
            'temperatura_presion'     => $this->calcularRelacion($temperaturas, $presiones),
            'temperatura_calidad'     => $this->calcularRelacion($temperaturas, $calidades),
            'temperatura_indice'      => $this->calcularRelacion($temperaturas, $indices),
            'humedad_presion'         => $this->calcularRelacion($humedades, $presiones),
            'humedad_calidad'         => $this->calcularRelacion($humedades, $calidades),
            'humedad_indice'          => $this->calcularRelacion($humedades, $indices),
            'presion_calidad'         => $this->calcularRelacion($presiones, $calidades),
            'presion_indice'          => $this->calcularRelacion($presiones, $indices),
            'calidad_indice'          => $this->calcularRelacion($calidades, $indices),
        ];

        // Frecuencias
        $frecuencias = [
            'temperatura' => $this->calcularFrecuencia($temperaturas),
            'humedad'     => $this->calcularFrecuencia($humedades),
            'presion'     => $this->calcularFrecuencia($presiones),
            'calidad'     => $this->calcularFrecuencia($calidades),
            'indice'      => $this->calcularFrecuencia($indices),
        ];

        return $this->response->setJSON([
            'temperatura'      => $resumenTemp,
            'humedad'          => $resumenHum,
            'presion'          => $resumenPres,
            'calidad_aire'     => $resumenCal,
            'indice'           => $resumenInd,
            'correlaciones'    => $correlaciones,
            'frecuencias'      => $frecuencias,
        ]);
    }

    // -------------------- Privados --------------------
    private function calcularResumen(array $valores): array
    {
        $count = count($valores);
        if ($count === 0) {
            return [];
        }
        return [
            'cantidad'            => $count,
            'promedio'            => round((float) Stat::mean($valores), 2),
            'mediana'             => round((float) Stat::median($valores), 2),
            'minimo'              => round((float) min($valores), 2),
            'maximo'              => round((float) max($valores), 2),
            'rango'               => round((float) (max($valores) - min($valores)), 2),
            'primer_cuartil'      => round((float) Stat::firstQuartile($valores), 2),
            'tercer_cuartil'      => round((float) Stat::thirdQuartile($valores), 2),
            'desviacion_estandar' => round((float) Stat::stdev($valores), 2),
            'varianza'            => round((float) Stat::variance($valores), 2),
            'valores_extremos'    => Stat::iqrOutliers($valores),
        ];
    }

    private function calcularRelacion(array $x, array $y): array
    {
        if (count($x) < 2 || count($y) < 2) {
            return ['correlacion' => null, 'regresion_lineal' => []];
        }
        try {
            $corr = Stat::correlation($x, $y);
            $reg = Stat::linearRegression($x, $y);
            return [
                'correlacion' => round($corr, 4),
                'regresion_lineal' => array_map('floatval', $reg),
            ];
        } catch (\Exception $e) {
            return ['correlacion' => null, 'regresion_lineal' => []];
        }
    }

    private function calcularFrecuencia(array $valores): array
    {
        if (empty($valores)) {
            return ['frecuencia' => [], 'frecuencia_relativa' => [], 'frecuencia_rangos' => [], 'frecuencia_clases' => []];
        }
        return [
            'frecuencia'          => Freq::frequencies($valores),
            'frecuencia_relativa' => Freq::relativeFrequencies($valores, 2),
            'frecuencia_rangos'   => Freq::frequencyTableBySize($valores, 10),
            'frecuencia_clases'   => Freq::frequencyTable($valores, 5),
        ];
    }
}