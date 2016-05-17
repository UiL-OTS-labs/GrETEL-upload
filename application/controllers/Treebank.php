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
	 * @param  string $title The title of the Treebank.
	 * @return Loads the detail view.
	 */
	public function show($title)
	{
		$treebank = $this->treebank_model->get_treebank_by_title($title);

		// If this Treebank is private, only allow access to its owner.
		if (!$treebank->public && $treebank->user_id != $this->session->userdata('user_id'))
		{
			show_error(lang('not_authorized'), 403);
		}

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

		// Only allow the owner to change accessibility of a Treebank
		if ($treebank->user_id != $this->session->userdata('user_id'))
		{
			show_error(lang('not_authorized'), 403);
		}

		$t = array('public' => !$treebank->public);
		$this->treebank_model->update_treebank($treebank_id, $t);
		redirect($this->agent->referrer(), 'refresh');
	}

	/**
	 * Deletes a Treebank from both BaseX as well as the database.
	 * @param  integer $treebank_id The ID of the Treebank.
	 * @return Redirects to the previous page.
	 */
	public function delete($treebank_id)
	{
		$treebank = $this->treebank_model->get_treebank_by_id($treebank_id);

		// Only allow the owner to delete a Treebank
		if ($treebank->user_id != $this->session->userdata('user_id'))
		{
			show_error(lang('not_authorized'), 403);
		}

		// Delete the treebank from BaseX
		$components = $this->component_model->get_components_by_treebank($treebank_id);
		foreach ($components as $component)
		{
			$this->basex->delete($component->basex_db);
		}
		$this->basex->delete(strtoupper($treebank->title . '_ID'));
		
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
		// Only allow the owner to delete a Treebank
		if ($user_id != $this->session->userdata('user_id'))
		{
			show_error(lang('not_authorized'), 403);
		}

		$data['page_title'] = lang('my_treebanks');
		$data['treebanks'] = $this->treebank_model->get_treebanks_by_user($user_id);

		$this->load->view('header', $data);
		$this->load->view('treebank_list', $data);
		$this->load->view('footer');
	}
}
