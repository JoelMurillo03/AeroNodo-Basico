<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlertasTable extends Migration
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
            'lectura_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'nivel' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'valor_detectado' => [
                'type'    => 'FLOAT',
                'null'    => false,
            ],
            'fecha' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('lectura_id', 'lecturas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('alertas');
    }

    public function down()
    {
        $this->forge->dropTable('alertas');
    }
}