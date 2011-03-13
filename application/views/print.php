<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head><title></title>
<script type="text/javascript">
function doPrint() {
	window.print();
}
</script>
</head>
<body onload="doPrint()">
<table width="700" border="1" style="border: 1px solid #000; border-collapse: collapse" cellspacing="0" cellpadding="3">
  <tr>
    <th>User</th>
    <th>Date</th>
    <th>Time</th>
    <th>ID</th>
    <th>Panel</th>
  </tr>
<?foreach($records as $record):?>
<?
	$date = $record->rdate;
	$year = substr($date, 0, 4);
	$month = substr($date, 4, 2);
	$day = substr($date, 6, 2);

	$time = str_pad($record->rtime, 6, '0', STR_PAD_LEFT);
	$hour = substr($time, 0, 2);
	$min = substr($time, 2, 2);
	$sec = substr($time, 4, 2);
?>
  <tr>
  <td><?=$record->LastName . ', ' . $record->FirstName;?></td>
  <td><?=$month.'/'.$day.'/'.$year;?></td>
  <td><?=$hour.':'.$min.':'.$sec;?></td>
  <td><?=$record->CardNumber;?></td>
  <td><?=$record->panel;?></td>
  </tr>
<?endforeach;?>
</table>
</body>
</html>
