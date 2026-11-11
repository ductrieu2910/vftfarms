<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "addAttributeLine")
{
	
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$payroll_id = '';
	if(isset($_REQUEST['payroll_id']))
	{
		$payroll_id = $_REQUEST['payroll_id'];
	}
	$start_date = '';
	if(isset($_REQUEST['start_date']))
	{
		$start_date = $_REQUEST['start_date'];
	}
	$user_id = $appSession->getUserInfo()->getId();
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = $appSession->getConfig()->getProperty("company_id");
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$sql = "UPDATE hr_payroll_line SET end_date=NOW() WHERE rel_id='".$rel_id."' AND end_date IS NULL";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	
	$sql = "SELECT rel_id FROM mrp_workcenter_line_log WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$employee_id = $appSession->getTier()->getValue($msg);
	
	
	$builder = $appSession->getTier()->createBuilder("hr_payroll_line");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("write_uid", $user_id);
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');

	
	if($start_date != "")
	{
		$builder->add("start_date", $start_date);
	}else{
		$builder->add("start_date", $appSession->getTier()->getDateString(), 'f');
	}
	if($end_date != "")
	{
		$builder->add("end_date", $end_date);
	}
	$builder->add("rel_id", $rel_id);
	$builder->add("employee_id", $employee_id);
	$builder->add("payroll_id", $payroll_id);
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $user_id);
	$builder->add("status", 0);
	$builder->add("company_id", $company_id);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "findQuota")
{
	
	$salary_id = '';
	if(isset($_REQUEST['salary_id']))
	{
		$salary_id = $_REQUEST['salary_id'];
	}
	$employee_id = '';
	if(isset($_REQUEST['employee_id']))
	{
		$employee_id = $_REQUEST['employee_id'];
	}
	$payroll_id = '';
	if(isset($_REQUEST['payroll_id']))
	{
		$payroll_id = $_REQUEST['payroll_id'];
	}
	
	
	
	$sql = "SELECT d1.amount FROM hr_payroll_line d1 WHERE d1.status =0 AND d1.rel_id='".$salary_id."' AND d1.employee_id='".$employee_id."' AND d1.payroll_id='".$payroll_id."'";

	$msg->add("query", $sql);
	$a = $appSession->getTier()->getValue($msg);
	if($a == "")
	{
		$a = 0;
	}
	$sql = "SELECT SUM(d1.amount) FROM hr_payroll_quota d1 WHERE d1.status =0 AND d1.payroll_id='".$payroll_id."' AND d1.rel_id='".$employee_id."'";
	$msg->add("query", $sql);
	$b = $appSession->getTier()->getValue($msg);
	if($b == "")
	{
		$b = 0;
	}
	if($b>0){
		$c = floatval($b) - floatval($a);
		echo $c;
	}
	
	
}
?>