<?php

namespace Anwarqasem\CiAuth\Migrations;

class Groups extends \CodeIgniter\Controller
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'role'        => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'Description' => [
                'type' => 'TEXT',
                'null' => true
            ],
        ]);
        $this->forge->addKey('id', TRUE);
        $this->forge->createTable('groups');
    }

    public function down()
    {
        $this->forge->dropTable('groups');
    }
}
