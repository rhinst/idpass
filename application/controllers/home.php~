<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function printList() {
		$this->load->model('History');
		$criteria = $this->session->userdata('criteria');
		$data['records'] = $this->History->getList($criteria);
		$this->load->view('print', $data);
	}

	function export() {
		$this->load->model('History');
		$criteria = $this->session->userdata('criteria');
		$this->History->exportList($criteria);
	}

	function runReport() {
		$this->load->model('History');
		$this->session->set_userdata('criteria', $_POST);
		$records = $this->History->getList($_POST);
		$totalCount = count($records);
		print json_encode(Array('totalCount' => $totalCount, 'records' => $records));
	}

	function getUserList() {
		$this->load->model('Card');
		$users = $this->Card->getList();
		print json_encode(Array('users' => $users));
	}

	function getPanelList() {
		$this->load->model('Reader');
		$panels = $this->Reader->getList();
		print json_encode(Array('panels' => $panels));
	}

	function index()
	{
		$this->load->view('home');
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */
