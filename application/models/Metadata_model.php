<?php
class Metadata_model extends CI_Model 
{
	public function __construct()
	{
		$this->load->database();
	}

	public function get_metadata_by_id($metadata_id)
	{
		$this->db->where('id', $metadata_id);
		return $this->db->get('metadata')->row();
	}

	public function get_metadata_by_field($field)
	{
		$this->db->where('field', $field);
		return $this->db->get('metadata')->row();
	}

	public function add_metadata($metadata)
	{
		$this->db->insert('metadata', $metadata);
		return $this->db->insert_id();
	}

	public function update_metadata($metadata_id, $metadata)
	{
		$this->db->where('id', $metadata_id);
		$this->db->update('metadata', $metadata);
	}

	public function update_minmax($metadata_id, $value)
	{
		$metadata = $this->get_metadata_by_id($metadata_id);

		if (in_array($metadata->type, array('int', 'float', 'date', 'datetime')))
		{
			if ($metadata->min_value === NULL || $value < $metadata->min_value)
			{
				$metadata->min_value = $value;
				$this->update_metadata($metadata_id, $metadata);
			}
			else if ($metadata->max_value === NULL || $value > $metadata->max_value)
			{
				$metadata->max_value = $value;
				$this->update_metadata($metadata_id, $metadata);
			}
		}
	}

	/////////////////////////
	// API Calls
	/////////////////////////
	
	public function get_metadata_by_component($component_id)
	{
		$this->db->select(array('field', 'type', 'min_value', 'max_value'));
		$this->db->where('component_id', $component_id);
		return $this->db->get('metadata')->result();
	}
	
	public function get_metadata_by_treebank($treebank_id)
	{
		$this->db->select(array('field', 'type'));
		$this->db->select_min('min_value');
		$this->db->select_max('max_value');
		$this->db->from('metadata');
		$this->db->join('components', 'components.id = metadata.component_id');
		$this->db->where('treebank_id', $treebank_id);
		$this->db->group_by(array('field', 'type'));
		return $this->db->get()->result();
	}
}
