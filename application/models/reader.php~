<?php

class Reader extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function getList() {
		$ci =& get_instance();
		$query = $ci->db->query("SELECT * FROM Readers ORDER BY Name ASC");
		$list = Array();
		foreach($query->result() as $item) {
			$list[] = $item;
		}
		return($list);
	}
};

?>
