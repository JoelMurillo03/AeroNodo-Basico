<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LecturasSeeder extends Seeder
{
    public function run()
    {
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $temp = rand(180, 350) / 10;
            $hum  = rand(300, 900) / 10;
            $pres = rand(9800, 10200) / 10;
            $calidad = rand(100, 900);
            $indice  = round($temp * 0.4 + $hum * 0.3 + ($calidad / 1000) * 10, 2);

            $data[] = [
                'temperatura'   => $temp,
                'humedad'       => $hum,
                'presion'       => $pres,
                'calidad_aire'  => $calidad,
                'estado_wifi'   => true,
                'indice'        => $indice,
                'fecha'         => date('Y-m-d H:i:s', strtotime("-$i minutes")),
            ];
        }

        // Insertar un registro inicial en dispositivo
        $this->db->table('dispositivo')->insert([
            'estado_wifi'           => true,
            'intensidad_wifi'       => -50,
            'ultima_actualizacion'  => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('lecturas')->insertBatch($data);
    }
}