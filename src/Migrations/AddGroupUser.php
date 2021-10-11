<?php

namespace Anwarqasem\CiAuth\Migrations;

use CodeIgniter\Database\Migration;

class AddGroupUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id'  => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => TRUE,
            ],
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => TRUE,
            ],
        ]);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'NO ACTION', 'CASCADE');
        $this->forge->addForeignKey('group_id', 'groups', 'id', 'NO ACTION', 'CASCADE');
        $this->forge->addKey('id', TRUE);
        $this->forge->createTable('group_user');
    }

    public function down()
    {
        $this->forge->dropTable('group_user');
    }
}
