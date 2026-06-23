<?php

namespace App\Controllers;

class AcercaDeController extends BaseController
{
    public function index()
    {
        $data = [
            'nombre' => 'AeroNodo Básico',
            'descripcion' => 'Sistema IoT de monitoreo ambiental para espacios cerrados (aulas, hogares, oficinas) basado en ESP32, sensores BME280 y MQ-135.',
            'tecnologias' => [
                'ESP32' => 'Microcontrolador principal',
                'BME280' => 'Sensor de temperatura, humedad y presión',
                'MQ-135' => 'Sensor de calidad del aire',
                'CodeIgniter 4' => 'Framework PHP para el backend',
                'MySQL' => 'Base de datos',
                'Bootstrap 5' => 'Framework CSS',
                'Chart.js' => 'Gráficas interactivas',
                'Vite' => 'Empaquetador de assets'
            ],
            'autor' => 'Joel Antonio Murillo Aguilar',
            'anio' => date('Y'),
            'version' => '1.0.0',
            'diagramas' => [
                'Casos de Uso' => 'casos_de_uso.png',
                'Actividades' => 'actividades.png',
                'Secuencia' => 'secuencia.png',
                'Clases' => 'clases.png',
                'Mapa del Sitio' => 'mapa_sitio.png',
                'Modelo de BD' => 'modelo_bd.png',
                'Esquema Electrónico' => 'esquema_electronico.png'
            ]
        ];

        return view('acerca-de', $data);
    }
}