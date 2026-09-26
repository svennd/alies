<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Document_settings extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'name' => ['type' => 'VARCHAR', 'constraint' => 64],
            'payload' => ['type' => 'MEDIUMTEXT'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->dbforge->add_key('name', true);
        $this->dbforge->create_table('document_settings', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->dbforge->drop_table('document_settings', true);
    }
}
