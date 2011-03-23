<?php

class Card extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function getList() {
		$ci =& get_instance();
		$query = $ci->db->query("SELECT CLng(CardNum) as CardNumber, StrConv(FirstName, 3) as FN,StrConv(LastName, 3) as LN FROM Cards ORDER BY LastName ASC,FirstName ASC");
		$list = Array();
		foreach($query->result() as $item) {
			$list[] = $item;
		}
		return($list);
	}
};

?>
