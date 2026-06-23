<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLecturasTable extends Migration
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
            'temperatura' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
            ],
            'humedad' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
            ],
            'presion' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
            ],
            'calidad_aire' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'estado_wifi' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'indice' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'fecha' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('lecturas');
    }

    public function down()
    {
        $this->forge->dropTable('lecturas');
    }
}