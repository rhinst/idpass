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

			$worksheet->write($row,0,$record->LN . ', ' . $record->FN);
			$worksheet->write($row,1,"$month/$day/$year");
			$worksheet->write($row,2,"$hour:$min:$sec");
			$worksheet->write($row,3,$record->CardNumber);
			$worksheet->write($row,4,$record->panel);
		}

		$workbook->close();
		
	}

	function getList($params = Array()) {

		$ci =& get_instance();


		$from = "(History H LEFT OUTER JOIN Cards C ON (C.CardNum=H.CardNumber)) LEFT OUTER JOIN Readers R ON (R.SitePanelId=H.SitePanelId AND CLng(R.Point)=(CLng(H.TransInfo)+1))";

		if($params['wib'] == '1') {

			$selectFields = Array(
				'DISTINCT(RDate)',
				'StrConv(C.FirstName, 3) as FN',
				'StrConv(C.LastName, 3) as LN',
				'CLng(C.CardNum) as CardNumber',
				'MIN(R.Name) as panel',
				'H.RDate as rdate',
				'1 as unused',
				'MIN(H.RTime) as rtime',
				'1 as sorter'
			);

			$where = "(H.CardNumber > 0) AND (H.RTime<120000)";
			
			if($params['user']) {
				$where .= " AND (H.CardNumber=" . $params['user'] . ")";
			}

			if(isset($params['fromDate']) && strlen($params['fromDate']) > 0) {
				list($month,$day,$year) = explode('/', $params['fromDate']);
				$month = str_pad($month, 2, '0', STR_PAD_LEFT);
				$day = str_pad($day, 2, '0', STR_PAD_LEFT);
				$where .= " AND (H.RDate >= " . intval($year.$month.$day) . ")";
			}
			if(isset($params['toDate']) && strlen($params['toDate']) > 0) {
				list($month,$day,$year) = explode('/', $params['toDate']);
				$where .= " AND (H.RDate <= " . intval($year.$month.$day) . ")";
			}

			$groupBy = "CLng(C.CardNum), H.RDate, StrConv(C.FirstName, 3), StrConv(C.LastName, 3)";

			$sql1 = "SELECT " . implode(',', $selectFields) . " FROM $from WHERE $where GROUP BY $groupBy";

			$endTime = "CSTR(15) & CSTR(29 + (H.RDate MOD 3)) & STRING(2 - LEN(CSTR(H.RDate MOD 60)), '0') & CSTR(H.RDate MOD 60)";

			$selectFields = Array(
				'DISTINCT(RDate)',
				'StrConv(C.FirstName, 3) as FN',
				'StrConv(C.LastName, 3) as LN',
				'CLng(C.CardNum) as CardNumber',
				'MIN(R.Name) as panel',
				'H.RDate as rdate',
				'MIN(H.RTime) as unused',
				 $endTime . ' as rtime',
				 '2 AS sorter'
			);


			$sql2 = "SELECT " . implode(',', $selectFields) . " FROM $from WHERE $where GROUP BY $groupBy";


			$sql = "($sql1) UNION ($sql2)";


			switch($params['sortBy']) {
			case 'user':
				$sql .= " ORDER BY LN ASC, FN ASC, rdate ASC, sorter ASC";
				break;
			case 'time':
				$sql .= " ORDER BY rdate ASC, sorter ASC";
				break;
			default:
				$sql .= " ORDER BY rdate ASC, sorter ASC";
			}
		}
		else {
			$selectFields = Array(
				'StrConv(C.FirstName, 3) as FN', 
				'StrConv(C.LastName, 3) as LN',
				'CLng(C.CardNum) as CardNumber',
				'R.Name as panel',
				'H.RDate as rdate', 
				'H.RTime as rtime',
			);

			$sql = "SELECT " . implode(',', $selectFields) . " FROM $from WHERE 1=1 AND C.CardNum > 0 ";

			if(isset($params['user']) && strlen($params['user']) > 0) {
				$sql .= " AND (C.CardNum=" . $params['user'] . ")";
			}
			if(isset($params['fromDate']) && strlen($params['fromDate']) > 0) {
				list($month,$day,$year) = explode('/', $params['fromDate']);
				$month = str_pad($month, 2, '0', STR_PAD_LEFT);
				$day = str_pad($day, 2, '0', STR_PAD_LEFT);
				$sql .= " AND (H.RDate >= " . intval($year.$month.$day) . ")";
			}
			if(isset($params['toDate']) && strlen($params['toDate']) > 0) {
				list($month,$day,$year) = explode('/', $params['toDate']);
				$sql .= " AND (H.RDate <= " . intval($year.$month.$day) . ")";
			}
			if(isset($params['panel']) && strlen($params['panel']) > 0) {
				list($SitePanelId, $Point) = explode('_', $params['panel']);
				$sql .= " AND (R.SitePanelId = " . intval($SitePanelId) . ") AND (R.Point = " . intval($Point) . ")";
			}
			if(isset($params['type']) && strlen($params['type']) > 0) {
				if($params['type'] == 'morning') {
					$sql .= " AND (H.RTime < 120000)";
				}
				else {
					$sql .= " AND (H.RTime >= 120000)";
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
	
		}


		$fh = fopen("c:/tmp/test.txt", "w");
		fwrite($fh, $sql);
		fclose($fh);

		$query = $ci->db->query($sql);
		$list = Array();
		foreach($query->result() as $item) {
			$list[] = $item;
		}
		return($list);
	}
};

?>
