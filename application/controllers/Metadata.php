<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Metadata extends CI_Controller
{

	public function __construct()
	{
		$this->allowed_routes = array();  // All routes should be blocked for non-authenticated Users

		parent::__construct();
	}

	/**
	 * Updates the facet for the given metadata field
	 * @param  integer $metadata_id the ID of the Metadata model
	 * @return void                 redirects to the Treebank detail view
	 */
	public function update_facet($metadata_id)
	{
		$metadata = $this->metadata_model->get_metadata_by_id($metadata_id);

		if (!$this->validate_metadata())
		{
			// Show form again with error messages
			redirect('/treebank/detail/' . $metadata->treebank_id, 'refresh');
		}
		else
		{
			// Update the metadata facet
			$this->metadata_model->update_metadata($metadata_id, $this->post_metadata());

			// Show the treebank detail data
			redirect('/treebank/detail/' . $metadata->treebank_id, 'refresh');
		}
	}

	/**
	 * Updates whether the field is shown for the given Metadata field
	 * @param  integer $metadata_id the ID of the Metadata model
	 * @param  boolean $show        whether or not to show the field
	 * @return void                 redirects to the Treebank detail view
	 */
	public function update_shown($metadata_id, $show)
	{
		$metadata = $this->metadata_model->get_metadata_by_id($metadata_id);
		$this->metadata_model->update_metadata($metadata_id, array('show' => $show));

		// Show the treebank detail data
		redirect('/treebank/detail/' . $metadata->treebank_id, 'refresh');
	}

	/////////////////////////
	// Form handling
	/////////////////////////

	/**
	 * Validates the input.
	 * @return boolean whether the validation has succeeded
	 */
	private function validate_metadata()
	{
		$this->form_validation->set_rules('facet', lang('facet'), 'required');

		return $this->form_validation->run();
	}

	/**
	 * Posts the Metadata data.
	 * @return array an array with the fields that will be updated
	 */
	private function post_metadata()
	{
		return array(
			'facet' => $this->input->post('facet'),
		);
	}

}
