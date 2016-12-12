<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Metadata extends CI_Migration 
{
	public function up()
	{
		// Create table for Metadata
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'auto_increment' => TRUE,
			),
			'treebank_id' => array(
				'type' => 'INT',
			),
			'field' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
			),
			'type' => array(
				'type' => 'ENUM("text", "int", "float", "date", "datetime")',
			),
			'facet' => array(
				'type' => 'ENUM("checkbox", "dropdown", "slider", "date_range")',
			),
			'min_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'max_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('treebank_id');
		$this->dbforge->add_key(array('treebank_id', 'field'));
		$this->dbforge->create_table('metadata', FALSE, array('ENGINE' => 'InnoDB'));

		# Add FOREIGN KEY via SQL
		$this->db->query("ALTER TABLE `metadata`
			ADD FOREIGN KEY (`treebank_id`)
			REFERENCES `treebanks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
	}

	public function down()
	{
		$this->dbforge->drop_table('metadata');
	}
}