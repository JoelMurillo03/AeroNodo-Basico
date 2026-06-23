<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDispositivoTable extends Migration
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
            'estado_wifi' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'intensidad_wifi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'ultima_actualizacion' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('dispositivo');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo');
    }
}