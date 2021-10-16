<?php

namespace Anwarqasem\CiAuth\Database\Migrations;

use CodeIgniter\Database\Migration;

class UsersToken extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id'    => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => TRUE,
            ],
            'token'      => [
                'type'       => 'TEXT'
            ],
            'available'  => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'created_at datetime default current_timestamp',
            'updated_at' => [
                'type' => 'datetime',
                'null' => true
            ],
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true
            ],

        ]);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'NO ACTION', 'CASCADE');
        $this->forge->addKey('id', TRUE);
        $this->forge->createTable('token');
    }

    public function down()
    {
        $this->forge->dropTable('token');
    }
}
