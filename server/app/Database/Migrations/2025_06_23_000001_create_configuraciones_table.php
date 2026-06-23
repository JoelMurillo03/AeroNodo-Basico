<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConfiguracionesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'valor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'descripcion' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('configuraciones');

        // Insertar valores por defecto
        $this->db->table('configuraciones')->insertBatch([
            [
                'clave'       => 'umbral_calidad_moderado',
                'valor'       => '500',
                'descripcion' => 'Límite para considerar calidad del aire como Moderada'
            ],
            [
                'clave'       => 'umbral_calidad_critico',
                'valor'       => '800',
                'descripcion' => 'Límite para considerar calidad del aire como Crítica'
            ],
            [
                'clave'       => 'umbral_temp_minima',
                'valor'       => '15',
                'descripcion' => 'Temperatura mínima para considerar Normal'
            ],
            [
                'clave'       => 'umbral_temp_maxima',
                'valor'       => '30',
                'descripcion' => 'Temperatura máxima para considerar Normal'
            ],
            [
                'clave'       => 'umbral_hum_minima',
                'valor'       => '40',
                'descripcion' => 'Humedad mínima para considerar Normal'
            ],
            [
                'clave'       => 'umbral_hum_maxima',
                'valor'       => '70',
                'descripcion' => 'Humedad máxima para considerar Normal'
            ],
            [
                'clave'       => 'intervalo_actualizacion',
                'valor'       => '5',
                'descripcion' => 'Intervalo de actualización del dashboard en segundos'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('configuraciones');
    }
}