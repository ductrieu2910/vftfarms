<?php
$content = "";
if(isset($_POST['data']))
{
	$content = $_POST['data'];
	$content = urldecode($content);
}
if(isset($_REQUEST['data']))
{
	$content = $_REQUEST['data'];
	
}
$device_name = "";
if(isset($_POST['device_name']))
{
	$device_id = $_POST['device_name'];
}
if(isset($_REQUEST['device_name']))
{
	$device_name = $_REQUEST['device_name'];
}


$datas = $appSession->getTool()->split($content, ";");
$msg = $appSession->getTier()->createMessage();
$company_id = $appSession->getConfig()->getProperty("company_id");
$sql = "SELECT id, company_id FROM hr_timesheet_device WHERE (code='".$device_name."' OR name='".$device_name."')";
	
$msg->add("query", $sql);
$values = $appSession->getTier()->getArray($msg);
$device_id = "";
if(count($values)>0)
{
	$device_id = $values[0][0];
	$company_id = $values[0][1];
}
	
$path = ABSPATH."log/".$device_id."_upload.txt";
file_put_contents($path, $content);

for($i = 0; $i<count($datas); $i++)
{
	$items = $appSession->getTool()->split($datas[$i], ",");
	if(count($items)>1)
	{
		$code = $items[0];
		$date = $items[1];
	
		if($code == "")
		{
			continue;
		}
		$sql = "SELECT d2.code, d2.name, d2.id FROM product_product_code d1 LEFT OUTER JOIN hr_employee d2 ON(d1.product_id = d2.id) WHERE d1.status =0 AND d1.code='".$code."'";
		$msg->add("query", $sql);
		
		$arr = $appSession->getTier()->getArray($msg);
		$code = "";
		$name = "";
		$employee_id = "";
		if(count($arr)>0)
		{
			$code = $arr[0][0];
			$name = $arr[0][1];
			$employee_id = $arr[0][2];
		}
		if($code == "")
		{
			continue;
		}
		
		$create_uid = "";
		$write_uid = "";
		$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND create_date='".$date."'";
		
		
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		if(count($values)>0)
		{
			continue;
		}
		
		$arr = explode(' ', $date);
		$date_small = $date;
		$date_time = $date;
		if(count($arr)>0)
		{
			$date_small = $arr[0];
			$date_time = $arr[1];
		}
		$status = "VÀO";
		$today = $date_small; 
		$hour = 0;
		$arr = explode(':', $date_time);
		if(count($arr)>0)
		{
			$hour = intval($arr[0]);
		}
		if($hour>0){
			$hour = $hour -1;
		}
		$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND create_date>='".$date_small." ".$hour.":00:00' AND create_date<='".$date."' ORDER BY create_date DESC LIMIT 1";
		
		$msg->add("query", $sql);
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
				continue;
			}
		}
		
		$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND write_uid ='' AND create_date>='".$date_small." 04:00:00' AND create_date<='".$date."' ORDER BY create_date DESC LIMIT 1";
		
		$msg->add("query", $sql);
		$write_uid = "";
		$create_uid = "";
		$values = $appSession->getTier()->getArray($msg);
		if(count($values) == 0 && $hour<=3)
		{
			$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND write_uid ='' AND create_date>='".date('Y-m-d',strtotime('-1 hour',strtotime($date_small)))." 04:00:00' AND create_date<='".$date."' ORDER BY create_date DESC LIMIT 1";
			$msg->add("query", $sql);
			$values = $appSession->getTier()->getArray($msg);
		}
		
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
				continue;
			}
			$sql = "UPDATE hr_timesheet_resource SET write_uid='OUT' WHERE id='".$values[0][0]."'";
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
			
			$status = "RA";
			$write_uid = "OUT";
			
			$sql = "UPDATE mrp_workcenter_line_log SET end_date=NOW(), write_date=NOW() WHERE status =0 AND rel_id='".$employee_id."' AND end_date IS NULL AND start_date>='".$today." 04:00:00' AND start_date<='".$today." 23:59:59'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			
			$sql = "UPDATE hr_payroll_line SET end_date=NOW() WHERE status =0 AND employee_id='".$employee_id."' AND end_date IS NULL AND start_date>='".$today." 04:00:00' AND start_date<='".$today." 23:59:59'";
			$msg->add("query", $sql);
			
			$r = $appSession->getTier()->exec($msg);
			
		}else{
			$create_uid = "IN";
			$sql = "UPDATE mrp_workcenter_line_log SET end_date=NOW(), write_date=NOW() WHERE status =0 AND rel_id='".$employee_id."' AND end_date IS NULL AND start_date>='".$today." 04:00:00' AND start_date<='".$date."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
		}
		
	
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO hr_timesheet_resource(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", create_uid";
		$sql = $sql.", code";
		$sql = $sql.", name";
		$sql = $sql.", company_id";
		$sql = $sql.", device_id";
		$sql = $sql.", write_uid";
	
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", '".$date."'";
		$sql = $sql.", '".$date."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$create_uid."'";
		$sql = $sql.", '".str_replace("'", "''", $code)."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".$company_id."'";
		$sql = $sql.", '".$device_id."'";
		$sql = $sql.", '".$write_uid."'";

		$sql = $sql.")";
		
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
		
		$to =[];
		if($employee_id != "")
		{
			if($create_uid == "IN")
			{
				$sql = "SELECT line_id FROM mrp_workcenter_line_log WHERE rel_id='".$employee_id."' AND status =0 AND type ='EMPLOYEE' ORDER BY start_date DESC LIMIT 1";
				$msg->add("query", $sql);
				
				$line_id = $appSession->getTier()->getValue($msg);
				if($line_id == ""){
					$sql = "SELECT d3.line_id FROM hr_employee_rel d1 LEFT OUTER JOIN hr_line_department d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN hr_line_department dp ON(d2.parent_id = dp.id) LEFT OUTER JOIN mrp_workcenter_line_rel d3 ON(d2.id = d3.rel_id OR dp.id =d3.rel_id) LEFT OUTER JOIN mrp_workcenter_line_flow d4 ON(d3.line_id = d4.id) WHERE d1.employee_id='".$employee_id."' AND d1.status =0 AND d2.status =0 AND d3.status =0 AND d4.status=0 AND d3.line_id != ''";
					$msg->add("query", $sql);
					$line_id = $appSession->getTier()->getValue($msg);
				}else{
					$sql = "SELECT d3.line_id FROM hr_employee_rel d1 LEFT OUTER JOIN hr_line_department d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN hr_line_department dp ON(d2.parent_id = dp.id) LEFT OUTER JOIN mrp_workcenter_line_rel d3 ON(d2.id = d3.rel_id OR dp.id =d3.rel_id) LEFT OUTER JOIN mrp_workcenter_line_flow d4 ON(d3.line_id = d4.id) WHERE d1.employee_id='".$employee_id."' AND d1.status =0 AND d2.status =0 AND d3.status =0 AND d4.status=0 AND d3.line_id != ''";
					$msg->add("query", $sql);
					$lines = $appSession->getTier()->getArray($msg);
					$hasItem = false;
					for($v =0; $v<count($lines); $v++)
					{
						if($lines[$v][0] == $line_id)
						{
							$hasItem = true;
							break;
						}
					}
					if($hasItem == false && count($lines)>0)
					{
						//$line_id = $lines[0][0];
					}
				}
				
				
				if($line_id != "")
				{
					$log_id = $appSession->getTool()->getId();
					$sql = "INSERT INTO mrp_workcenter_line_log(";
					$sql = $sql."id";
					$sql = $sql.", create_date";
					$sql = $sql.", write_date";
					$sql = $sql.", status";
					$sql = $sql.", create_uid";
					$sql = $sql.", start_date";
					$sql = $sql.", line_id";
					$sql = $sql.", company_id";
					$sql = $sql.", session_id";
					$sql = $sql.", rel_id";
					$sql = $sql.", type";
					$sql = $sql." )VALUES(";
					$sql = $sql."'".$log_id."'";
					$sql = $sql.", '".$date."'";
					$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
					$sql = $sql.", 0";
					$sql = $sql.", '".$user_id."'";
					$sql = $sql.", '".$date."'";
					$sql = $sql.", '".$line_id."'";
					$sql = $sql.", '".$company_id."'";
					$sql = $sql.", '".$id."'";
					$sql = $sql.", '".$employee_id."'";
					$sql = $sql.", 'EMPLOYEE'";
					$sql = $sql.")";
					$msg->add("query", $sql);
					$r = $appSession->getTier()->exec($msg);
					$sql = "SELECT payroll_id FROM hr_payroll_line WHERE employee_id='".$employee_id."' AND status =0 ORDER BY start_date DESC LIMIT 1";
					$msg->add("query", $sql);
					$payroll_id = $appSession->getTier()->getValue($msg);
					if($payroll_id != "")
					{
						$sql = "INSERT INTO hr_payroll_line(";
						$sql = $sql."id";
						$sql = $sql.", create_date";
						$sql = $sql.", write_date";
						$sql = $sql.", status";
						$sql = $sql.", create_uid";
						$sql = $sql.", start_date";
						$sql = $sql.", payroll_id";
						$sql = $sql.", company_id";
						$sql = $sql.", employee_id";
						$sql = $sql.", rel_id";
						$sql = $sql." )VALUES(";
						$sql = $sql."'".$appSession->getTool()->getId()."'";
						$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
						$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
						$sql = $sql.", 0";
						$sql = $sql.", '".$user_id."'";
						$sql = $sql.", NOW()";
						$sql = $sql.", '".$payroll_id."'";
						$sql = $sql.", '".$company_id."'";
						$sql = $sql.", '".$employee_id."'";
						$sql = $sql.", '".$log_id."'";
						$sql = $sql.")";
						$msg->add("query", $sql);
						$r = $appSession->getTier()->exec($msg);
					}
					
					
					
				
					$to[0] = ['id' => 'b026ee7f-b457-4c99-bef9-8d51007ec20f', 'type' => 'company'];
				}
			}
			if(count($to)>0){
				$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=mrp_workcenter_line_log&log_type=employee&line_id=".$line_id."&id=".$log_id);
			}
			
		}
		
		
	}
}
echo "OK";
?>