<?php

namespace Anwarqasem\CiAuth\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'name'              => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'email'             => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'password'          => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
            ],
            'password_recovery' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'null'       => true
            ],
            'dt_defaults' => [
                'type' => 'tinyint' ,
                'constraint' => '1',
                'default' => false
            ],
            'created_at datetime default current_timestamp',
            'updated_at'        => [
                'type' => 'datetime',
                'null' => true
            ],
            'deleted_at'        => [
                'type' => 'datetime',
                'null' => true
            ],
        ]);

        $this->forge->addKey('id', TRUE);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
