<?php

class History extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function exportList($params = Array()) {
		require_once('Spreadsheet/Excel/Writer.php');
		$records = $this->getList($params);

		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->send('export.xls');
		$worksheet =& $workbook->addWorksheet('Report');

		$worksheet->write(0,0,'User');
		$worksheet->write(0,1,'Date');
		$worksheet->write(0,2,'Time');
		$worksheet->write(0,3,'ID');
		$worksheet->write(0,4,'Panel');

		$row = 0;
		foreach($records as $record) {
			$row++;

			$date = $record->rdate;
			$year = substr($date, 0, 4);
			$month = substr($date, 4, 2);
			$day = substr($date, 6, 2);

			$time = str_pad($record->rtime, 6, '0', STR_PAD_LEFT);
			$hour = substr($time, 0, 2);
			$min = substr($time, 2, 2);
			$sec = substr($time, 4, 2);

			$worksheet->write($row,0,$record->LastName . ', ' . $record->FirstName);
			$worksheet->write($row,1,"$month/$day/$year");
			$worksheet->write($row,2,"$hour:$min:$sec");
			$worksheet->write($row,3,$record->CardNumber);
			$worksheet->write($row,4,$record->panel);
		}

		$workbook->close();
		
	}

	function getList($params = Array()) {
		$ci =& get_instance();

		$sql = "SELECT C.FirstName, C.LastName, CLng(C.CardNum) as CardNumber, R.Name as panel, H.RDate as rdate, H.RTime as rtime FROM (History H LEFT OUTER JOIN Cards C ON (C.CardNum=H.CardNumber)) LEFT OUTER JOIN Readers R ON (R.SitePanelId=H.SitePanelId AND CLng(R.Point)=(CLng(H.TransInfo)+1)) WHERE 1=1 AND C.CardNum > 0 ";
		if(isset($params['user']) && strlen($params['user']) > 0) {
			$sql .= " AND (C.CardNum=" . $params['user'] . ")";
		}
		if(isset($params['fromDate']) && strlen($params['fromDate']) > 0) {
			list($month,$day,$year) = explode('/', $params['fromDate']);
			$sql .= " AND (RDate >= " . intval($year.$month.$day) . ")";
		}
		if(isset($params['toDate']) && strlen($params['toDate']) > 0) {
			list($month,$day,$year) = explode('/', $params['toDate']);
			$sql .= " AND (RDate <= " . intval($year.$month.$day) . ")";
		}
		if(isset($params['panel']) && strlen($params['panel']) > 0) {
			list($SitePanelId, $Point) = explode('_', $params['panel']);
			$sql .= " AND (R.SitePanelId = " . intval($SitePanelId) . ") AND (R.Point = " . intval($Point) . ")";
		}
		if(isset($params['type']) && strlen($params['type']) > 0) {
			if($params['type'] == 'morning') {
				$sql .= " AND (RTime < 120000)";
			}
			else {
				$sql .= " AND (RTime >= 120000)";
			}
		}

		switch($params['sortBy']) {
		case 'user':
			$sql .= " ORDER BY C.LastName ASC, C.FirstName ASC";
			break;
		case 'time':
		default:
			$sql .= " ORDER BY H.RDate ASC, H.RTime ASC";
			break;
		}

		$query = $ci->db->query($sql);
		$list = Array();
		foreach($query->result() as $item) {
			$list[] = $item;
		}
		return($list);
	}
};

?>
