<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Treebank extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns all public Treebanks (=public and processed)
	 * @return Loads the list view.
	 */
	public function index()
	{
		$data['page_title'] = lang('public_treebanks');
		$data['treebanks'] = $this->treebank_model->get_public_treebanks();

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}

	/**
	 * Returns details for a Treebank. 
	 * TODO: if private, only allow access to this user
	 * @param  string $title The title of the Treebank.
	 * @return Loads the detail view.
	 */
	public function show($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);

		$data['page_title'] = sprintf(lang('treebank_detail'), $title);
		$data['treebank'] = $treebank;
		$data['components'] = $this->component_model->get_components_by_treebank($treebank->id);
		$data['total_sentences'] = $this->component_model->get_sum($treebank->id, 'nr_sentences');
		$data['total_words'] = $this->component_model->get_sum($treebank->id, 'nr_words');

		$this->load->view('header', $data);
		$this->load->view('treebank_detail', $data);
		$this->load->view('footer');
	}

	/**
	 * Alters the accessibility of a Treebank (public <-> private).
	 * @param  integer $treebank_id The ID of the Treebank.
	 * @return Redirects to the previous page.
	 */
	public function change_access($treebank_id)
	{
		$treebank = $this->treebank_model->get_treebank_by_id($treebank_id);
		$t = array('public' => !$treebank->public);
		$this->treebank_model->update_treebank($treebank_id, $t);
		redirect($this->agent->referrer(), 'refresh');
	}

	/**
	 * Deletes a Treebank from both BaseX as well as the database.
	 * TODO: also delete the uploaded files.
	 * TODO: only allow admins or the user of the file to delete.
	 * @param  integer $treebank_id The ID of the Treebank.
	 * @return Redirects to the previous page.
	 */
	public function delete($treebank_id)
	{
		$treebank = $this->treebank_model->get_treebank_by_id($treebank_id);

		// Delete the treebank from BaseX
		$components = $this->component_model->get_components_by_treebank($treebank_id);
		foreach ($components as $component)
		{
			$this->delete_from_basex($component->basex_db);
		}
		$this->delete_from_basex(strtoupper($treebank->title . '_ID'));
		
		// Delete the treebank from the database
		$treebank = $this->treebank_model->delete_treebank($treebank_id);

		// Return to the previous page
		$this->session->set_flashdata('message', lang('treebank_deleted'));
		redirect($this->agent->referrer(), 'refresh');
	}

	/**
	 * Returns all Treebanks of the current User.
	 * TODO: only allow current user or admins access.
	 * @param  integer $user_id The ID of the User.
	 * @return Loads the list view.
	 */
	public function user($user_id)
	{
		$data['page_title'] = lang('my_treebanks');
		$data['treebanks'] = $this->treebank_model->get_treebanks_by_user($user_id);

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}

	/**
	 * Deletes a database from BaseX.
	 * @param  string $db The database.
	 * @return Nothing.
	 */
	private function delete_from_basex($db)
	{
		try
		{
			// Create session
			$session = new BaseXSession(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PWD);

			// Delete database
			$session->send(sprintf("DROP DB %s", $db));
			echo $session->info();

			// Close session
			$session->close();
		} 
		catch (Exception $e) 
		{
			// Print exception
			echo $e->getMessage();
		}
	}
}
