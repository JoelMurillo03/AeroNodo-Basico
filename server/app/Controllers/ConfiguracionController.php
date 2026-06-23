<?php

namespace App\Controllers;

use App\Models\ConfiguracionModel;

class ConfiguracionController extends BaseController
{
    public function index()
    {
        $model = new ConfiguracionModel();
        $configs = $model->findAll();

        // Organizar por clave para la vista
        $data['configs'] = [];
        foreach ($configs as $cfg) {
            $data['configs'][$cfg['clave']] = $cfg;
        }

        return view('configuracion', $data);
    }

    public function guardar()
    {
        $model = new ConfiguracionModel();

        // Recibir datos del formulario
        $campos = [
            'umbral_calidad_moderado',
            'umbral_calidad_critico',
            'umbral_temp_minima',
            'umbral_temp_maxima',
            'umbral_hum_minima',
            'umbral_hum_maxima',
            'intervalo_actualizacion'
        ];

        foreach ($campos as $campo) {
            $valor = $this->request->getPost($campo);
            if ($valor !== null) {
                $model->setValor($campo, $valor);
            }
        }

        return redirect()->to('/configuracion')->with('mensaje', 'Configuración guardada correctamente');
    }

    /**
     * Endpoint para obtener configuraciones (para usar desde JS)
     */
    public function getConfigs()
    {
        $model = new ConfiguracionModel();
        return $this->response->setJSON($model->getAllAsArray());
    }
}