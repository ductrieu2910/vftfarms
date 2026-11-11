<?php
$msg = $appSession->getTier()->createMessage();
$code = "CB10026";

//$sql = "UPDATE hr_timesheet_resource SET create_date=NOW() WHERE id='54bb8aec-4e24-4c62-f759-ecc8cfac7b81'";
//$msg->add("query", $sql);
//$appSession->getTier()->exec($msg);
	
$today = date("Y-m-d"); 
$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND write_uid ='' AND create_date>='".$today." 04:00:00' AND create_date<='".$today." 23:59:59'";

$msg->add("query", $sql);
$write_uid = "";
$create_uid = "";
$values = $appSession->getTier()->getArray($msg);
if(count($values)>0)
{
	
	$time = $values[0][1];
	$index = $appSession->getTool()->indexOf($time, ".");
	if($index != -1)
	{
		$time = $appSession->getTool()->subString($time, 0, $index);
	}
	$create_date = strtotime($time);
	
	$time = $values[0][2];
	$index = $appSession->getTool()->indexOf($time, ".");
	if($index != -1)
	{
		$time = $appSession->getTool()->subString($time, 0, $index);
	}
	
	$now = strtotime($time);
	
	$secs = $now - $create_date;
	$minutes = floor($secs /60); 
	if($minutes<=2)
	{
		exit();
	}
	
	
	$sql = "UPDATE hr_timesheet_resource SET write_uid='OUT' WHERE id='".$values[0][0]."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	$status = "RA";
	$write_uid = "OUT";
}

?>