<?php
date_default_timezone_set("Asia/Bangkok");
$content = file_get_contents("php://input");
if(isset($_REQUEST['data']))
{
	$content = $_REQUEST['data'];
	$content = urldecode($content);
}

if(isset($_POST['data']))
{
	$content = $_POST['data'];
	$content = urldecode($content);
}
$path = ABSPATH."log/camara/".date("H-i-s").".php";
file_put_contents($path, $content);

//$content ='{"keycode":"","date":"'.date("Y-m-d h:i:s").'","personTitle":"","temp":0,"action_type":"update","detected_image_url":"https://s3-hn-2.cloud.cmctelecom.vn/hanet-camera-image/face/upload/C21283M375/2023/02/16/234dc136-adbc-11ed-a25a-32045f285822.jpg","placeID":"11427","deviceID":"C21282M729","deviceName":"C21282M729","personName":"Nguyễn Văn Vàng","aliasID":"SC30040","data_type":"log","personID":"2215737062072516608","id":"e9ee928c-91bf-48bd-8d1c-10c06d72b826","time":1676525905000,"personType":"0","placeName":"46 Cây Keo","hash":"067f1dc71108e03b2122b9dbfb483895","mask":"-1"}';

$obj = json_decode($content);

$code = "";
$name = "";
$date = "";
$user_id = "";
if(isset($obj->{'personID'}))
{
	$code = $obj->{'personID'};
}

if(isset($obj->{'aliasID'}) && $obj->{'aliasID'} != "")
{
	$code = $obj->{'aliasID'};
}

$deviceID = "";
if( isset($obj->{'deviceID'}))
{
	$deviceID = $obj->{'deviceID'};
}
if($code == "")
{
	$code = $deviceID;
}
if(isset($obj->{'personName'}))
{
	$name = $obj->{'personName'};
}
if($name== "" && isset($obj->{'personName'}))
{
	$name = $obj->{'personName'};
}
if(isset($obj->{'date'}))
{
	$date = $obj->{'date'};
}
if($date == "")
{
	$date = date("Y-m-d H:i:s");
}
$id = "";

if($code != "")
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT id, company_id FROM hr_timesheet_device WHERE code='".$deviceID."'";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$device_id = "";
	$company_id = COMPANY_ID;
	
	if(count($values)>0)
	{
		$device_id = $values[0][0];
		$company_id = $values[0][1];
	}
	$sql = "SELECT id, first_name FROM hr_employee WHERE code='".str_replace("'", "''", $code)."' AND status =0";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	
	
	if(count($values)>0)
	{
		if($name == "")
		{
			$name= $values[0][1];
		}
		$employee_id = $values[0][0];
		$status = "VÀO";
		$today = date("Y-m-d"); 
		$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND create_date>='".$today." 04:00:00' AND create_date<='".$today." 23:59:59' ORDER BY create_date DESC";
		
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
				exit();
			}
		}
		
		$sql = "SELECT id, create_date, NOW() AS current_date FROM hr_timesheet_resource WHERE status =0 AND code='".$code."' AND write_uid ='' AND create_date>='".$today." 04:00:00' AND create_date<='".$today." 23:59:59' ORDER BY create_date DESC";
		
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
		}else{
			$create_uid = "IN";
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
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
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
		
		$path_doc = "";
		$document_id = "";
		$path_ext = "";
		$employee_code = $code;
		$employee_name = $name;
		
		
		if(isset($obj->{'detected_image_url'}))
		{
			$detected_image_url = $obj->{'detected_image_url'};
		
			$path = DOC_PATH;
			$dir = "";
			$current_date =  date('Y-m-d');
			if($current_date != "")
			{
				$arr_date = explode("-", $current_date);
				if(count($arr_date)>2)
				{
					$dir = $arr_date[0]."/". + $arr_date[1]."/". + $arr_date[2];
				}
			}
			$path = $path."/".$dir;
			if(is_dir($path) == false)
			{
				if (!mkdir($path, 0777, true)) {
					die('Failed to create folders...');
				}
			}
			
			$path = $path."/".$id;
			
			$ch = curl_init($detected_image_url);
			$fp = fopen($path, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$name = pathinfo($detected_image_url, PATHINFO_FILENAME); 
			$extension = pathinfo($detected_image_url, PATHINFO_EXTENSION);
			
			$sql = "INSERT INTO document(";
			$sql = $sql."id, name, type, rel_id, status, create_uid, create_date, write_date";
			$sql = $sql.", path, ext, company_id, write_uid";
			$sql = $sql.") VALUES(";
			$sql = $sql."'".$id."', '".str_replace("'", "''", $name)."', 'file', '".$id."', 0";
			$sql = $sql.", '', NOW(), NOW()";
			$sql = $sql.", '".$dir."', '".$extension."','".$company_id."', '')";
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
			$path_doc = $dir;
			$path_ext = $extension;
			$document_id = $id;
		}
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
						if($lines[$v] == $line_id)
						{
							$hasItem = true;
							break;
						}
					}
					if($hasItem == false && count($lines)>0)
					{
						$line_id = $lines[0][0];
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
					$sql = $sql.", NOW()";
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
			}else if($write_uid == "OUT")
			{
				$sql = "UPDATE mrp_workcenter_line_log SET end_date=NOW(), write_date=NOW() WHERE status =0 AND rel_id='".$employee_id."' AND end_date IS NULL AND start_date>='".$today." 04:00:00' AND start_date<='".$today." 23:59:59'";
				$msg->add("query", $sql);
				$r = $appSession->getTier()->exec($msg);
				
				$sql = "UPDATE hr_payroll_line SET end_date=NOW() WHERE status =0 AND employee_id='".$employee_id."' AND end_date IS NULL AND start_date>='".$today." 04:00:00' AND start_date<='".$today." 23:59:59'";
				$msg->add("query", $sql);
				
				$r = $appSession->getTier()->exec($msg);
			
			}
			if(count($to)>0){
				$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=mrp_workcenter_line_log&log_type=employee&line_id=".$line_id."&id=".$log_id);
			}
			
		}
		if($deviceID != "")
		{
			$to[0] = ['id' => $deviceID, 'type' => 'company'];
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=timesheet_resource_add&resource_id=".$id."&employee_code=".$employee_code."&employee_name=".$employee_name."&path=".$path_doc."&ext=".$path_ext."&document_id=".$document_id."&date=".$date."&status=".$status);
		}
		
		
		$WiseEyeOn39Plus = "117.2.81.52,1455";
		//$WiseEyeOn39Plus = "";
		if($WiseEyeOn39Plus != "")
		{
			$database = "WiseEyeOn39Plus_demo";
			$user = "sa";
			$password = "VIENSON2021NEW@123";
			$conn = odbc_connect("Driver={SQL Server};Server=$WiseEyeOn39Plus;Database=$database;", $user, $password);
			$date_small = $date;
			$arr = explode(' ', $date);
			if(count($arr)>0)
			{
				$date_small = $arr[0];
			}
			if( $conn ) {
				 $sql = "SELECT d1.code FROM product_product_code d1 LEFT OUTER JOIN hr_employee d2 ON(d1.product_id = d2.id) WHERE d1.status =0 AND d2.code='".$code."'";
				 $msg->add("query", $sql);
				 $employee_code = $appSession->getTier()->getValue($msg);
				 if($employee_code != ""){
					 $sql = "INSERT INTO CheckInOut(UserEnrollNumber, TimeStr, TimeDate, OriginType, NewType, Source, MachineNo, WorkCode)VALUES(".$employee_code.", '".$date."', '".$date_small."', 'I', '', 'FP', 5, 0)";
					$trans_result= odbc_exec($conn,$sql);
					if(!$trans_result) {
						//echo "error?";
					} else {
						//echo "connected";
					}
				 }
			}


		}
		
	}else
	{
		$name = "NGUOI LA";
		$employee_id = "";
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
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", '".$date."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$user_id."'";
		$sql = $sql.", 'UNKNOWN'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".$company_id."'";
		$sql = $sql.", '".$device_id."'";
		$sql = $sql.")";
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
		$path_doc = "";
		$document_id = "";
		$path_ext = "";
		$employee_code = "UNKNOWN";
		$employee_name = $name;
		
		
		if(isset($obj->{'detected_image_url'}))
		{
			$detected_image_url = $obj->{'detected_image_url'};
		
			$path = DOC_PATH;
			$dir = "";
			$current_date =  date('Y-m-d');
			if($current_date != "")
			{
				$arr_date = explode("-", $current_date);
				if(count($arr_date)>2)
				{
					$dir = $arr_date[0]."/". + $arr_date[1]."/". + $arr_date[2];
				}
			}
			$path = $path."/".$dir;
			if(is_dir($path) == false)
			{
				if (!mkdir($path, 0777, true)) {
					die('Failed to create folders...');
				}
			}
			
			$path = $path."/".$id;
			
			$ch = curl_init($detected_image_url);
			$fp = fopen($path, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$name = pathinfo($detected_image_url, PATHINFO_FILENAME); 
			$extension = pathinfo($detected_image_url, PATHINFO_EXTENSION);
			
			$sql = "INSERT INTO document(";
			$sql = $sql."id, name, type, rel_id, status, create_uid, create_date, write_date";
			$sql = $sql.", path, ext, company_id, write_uid";
			$sql = $sql.") VALUES(";
			$sql = $sql."'".$id."', '".str_replace("'", "''", $name)."', 'file', '".$id."', 0";
			$sql = $sql.", '', NOW(), NOW()";
			$sql = $sql.", '".$dir."', '".$extension."','".$company_id."', '')";
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
			$path_doc = $dir;
			$path_ext = $extension;
			$document_id = $id;
		}
		$to =[];
		
		if($deviceID != "")
		{
			$to[0] = ['id' => $deviceID, 'type' => 'company'];
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=timesheet_resource_add&resource_id=".$id."&employee_code=".$employee_code."&employee_name=".$employee_name."&path=".$path_doc."&ext=".$path_ext."&document_id=".$document_id."&date=".$date."&status=");
		}
	}
	
	
}
echo $id;

?>