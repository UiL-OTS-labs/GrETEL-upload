<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Treebank extends REST_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns all public Treebanks (=public and processed)
	 * @return JSON response.
	 */
	public function index_get()
	{
		$this->response($this->treebank_model->get_public_treebanks());
	}
	
	/**
	 * Returns the components of a Treebank, given its title.
	 * @param  string $title The title of the Treebank.
	 * @return JSON response.
	 */
	public function show_get($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);

		if (!$treebank)
		{
			$this->response();
		}

		$this->response($this->component_model->get_components_by_treebank($treebank->id));
	}
	
	/**
	 * Returns the metadata of a Treebank, given its title.
	 * @param  string $title The title of the Treebank.
	 * @return JSON response.
	 */
	public function metadata_get($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);

		if (!$treebank)
		{
			$this->response();
		}

		$this->response($this->metadata_model->get_metadata_by_treebank($treebank->id));
	}
	
	/**
	 * Returns all Treebanks for a User.
	 * TODO: limit access to current User.
	 * @param  interger $user_id The ID of the User.
	 * @return JSON response.
	 */
	public function user_get($user_id)
	{
		$this->response($this->treebank_model->get_treebanks_by_user($user_id));
	}
}
