<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "findQuota")
{
	
	$period_id = '';
	if(isset($_REQUEST['period_id']))
	{
		$period_id = $_REQUEST['period_id'];
	}
	$employee_id = '';
	if(isset($_REQUEST['employee_id']))
	{
		$employee_id = $_REQUEST['employee_id'];
	}
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$unit_id = '';
	if(isset($_REQUEST['unit_id']))
	{
		$unit_id = $_REQUEST['unit_id'];
	}
	
	
	$sql = "SELECT d1.days_allotted, d1.expiration_date FROM hr_leave_quota d1 WHERE d1.status =0 AND d1.leave_category_id='".$category_id."' AND d1.rel_id='".$period_id."'";
	$msg->add("query", $sql);
	$days_allotted = 0;
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		$days_allotted = $days_allotted + $appSession->getTool()->toDouble($values[$i][0]);
	
	}
	$sql = "SELECT d1.days_allotted,  d1.expiration_date FROM hr_leave_quota d1 WHERE d1.status =0 AND d1.leave_category_id='".$category_id."' AND d1.rel_id='".$category_id."' AND d1.employee_id ='".$employee_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		$days_allotted = $days_allotted + $appSession->getTool()->toDouble($values[$i][0]);
	
	}
	
	$sql = "SELECT d1.days_allotted, d1.yearly, NOW() AS date FROM hr_leave_category d1 LEFT OUTER JOIN hr_employee_rel d2 ON(d1.id = d2.rel_id AND d2.status =0) WHERE d1.status =0 AND d1.id='".$category_id."' AND (d2.employee_id != '".$employee_id."' OR d2.id IS NULL)";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][1] == "1")
		{
			$month = date("m",strtotime($values[$i][2]));
			$a = $appSession->getTool()->toDouble($values[$i][0]);
			$a = ($a * $month)/12;
			$days_allotted = $days_allotted + $a;
		}else{
			$days_allotted = $days_allotted + $appSession->getTool()->toDouble($values[$i][0]);
		}
	}
	
	$sql = "SELECT d1.quantity, d1.unit_id FROM hr_leave d1 WHERE d1.status =0 AND d1.category_id='".$category_id."' AND d1.period_id='".$period_id."' AND employee_id='".$employee_id."'";

	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$token = 0;
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][1] == "Minutes")
		{
			$token = $token + (floatval($values[$i][0]) /( 60 * 24));
		}else if($values[$i][1] == "Hours")
		{
			$token = $token + (floatval($values[$i][0]) /(24));
		}else if($values[$i][1] == "Month")
		{
			$token = $token + (floatval($values[$i][0])* 31);
		}else if($values[$i][1] == "Month")
		{
			$token = $token + (floatval($values[$i][0])* 365);
		}else{
			$token = $token + (floatval($values[$i][0]));
		}
	}
	$days_allotted = $days_allotted - $token;
	if($unit_id == "Minutes")
	{
		$days_allotted = $days_allotted * ( 60 * 24);
	}else if($unit_id == "Hours")
	{
		$days_allotted = $days_allotted * 24;
	}else if($unit_id == "Month")
	{
		$days_allotted = $days_allotted /31;
	}else if($unit_id == "Year")
	{
		$days_allotted = $days_allotted /365;
	}
	echo $days_allotted;
	
}
?>