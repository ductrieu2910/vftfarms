<?php
function findValue($values, $id)
{
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][0] == $id)
		{
			return $values[$i][1];
		}
	}
	return "";
}
	
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$module_id = "";
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
$root_module_id = "";
if(isset($_REQUEST['root_module_id']))
{
	$root_module_id = $_REQUEST['root_module_id'];
}

$msg = $appSession->getTier()->createMessage();

function rowAdded($appSession, $module_id, $root_module_id, $id)
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT d5.data FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module d4 ON(d1.module_id = d4.id) LEFT OUTER JOIN ir_module_model_attribute d5 ON(d1.id = d5.module_model_id) WHERE d1.status =0 AND d2.name='action' AND d3.name='row_added' AND d5.data != ''";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql. " AND d1.module_id='".$module_id."'";
	}
	$msg->add("query", $sql);
	$action_id = $appSession->getTier()->getValue($msg);
	if($action_id != "")
	{
		$sql = "SELECT d1.id, d1.resource FROM res_resource d1 WHERE d1.rel_id='".$action_id."' AND d1.status =0";
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			$resource_id = $arr[0][0];
			$resource = $arr[0][1];
			$path = ABSPATH."log/".$resource_id.".php";
			
			$myfile = fopen($path, "w");
			fwrite($myfile, $resource);
			fclose($myfile);
			include($path);
			
		}
	}
}
function rowUpdated($appSession, $module_id, $root_module_id, $id)
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT d5.data FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module d4 ON(d1.module_id = d4.id) LEFT OUTER JOIN ir_module_model_attribute d5 ON(d1.id = d5.module_model_id) WHERE d1.status =0 AND d2.name='action' AND d3.name='row_updated' AND d5.data != ''";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql. " AND d1.module_id='".$module_id."'";
	}
	$msg->add("query", $sql);
	$action_id = $appSession->getTier()->getValue($msg);
	if($action_id != "")
	{
		$sql = "SELECT d1.id, d1.resource FROM res_resource d1 WHERE d1.rel_id='".$action_id."' AND d1.status =0";
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			$resource_id = $arr[0][0];
			$resource = $arr[0][1];
			$path = ABSPATH."log/".$resource_id.".php";
			
			$myfile = fopen($path, "w");
			fwrite($myfile, $resource);
			fclose($myfile);
			include($path);
			
		}
	}
}
function rowDeleted($appSession, $module_id, $root_module_id, $id)
{
	
}
if($ac == "save")
{
	
	$error = "";
	
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
	}
	
	
	$datas = [];
	$data_lines = explode("`", $params);
	
	for($i =0; $i<count($data_lines); $i++)
	{
		$items = explode('~', $data_lines[$i]);
		
		
		if(count($items)>3 && $items[0] != "")
		{
			$name = $items[0];
			$value = str_replace("[007E]", "~", $items[1]);
			$unique = $items[2];
			$sequence = $items[3];
			$type = $items[4];
			
			if($unique== "1")
			{
				$sql = "SELECT id FROM ".$table_name." WHERE id !='".$id."' AND ".$name."='".str_replace("'", "''", $value)."' AND status =0 AND company_id='".$appSession->getUserInfo()->getCompanyId()."'";
				
				
				$msg->add("query", $sql);
				$result = $appSession->getTier()->getArray($msg);
				$numrows = count($result);
				if($numrows > 0)
				{
					if($error != "")
					{
						$error = $error.';';
					}
					$error = $error.$name.",error,unique";
					break;
				}
				
			}
			
			if($sequence != "" && $value == "")
			{
				$d = $appSession->getTool()->now();
				$ww = date("W");
				
				$list = $appSession->getTool()->split($sequence,  ',');
				$v = '';
				$sq = '';
				$padding_left = 0;
				$padding_right = 0;
				$padding = '';
				for($j=0; $j<count($list); $j++)
				{
					if($list[$j] != "")
					{
						$l = $appSession->getTool()->split($list[$j], '=');
						
						if(count($l)>1)
						{
							if($l[0] == "value")
							{
								$v = $l[1];
							}else if($l[0] == "padding-left")
							{
								$padding_left = $l[1];
							}else if($l[0] == "padding-right")
							{
								$padding_right = $l[1];
							}
							else if($l[0] == "padding")
							{
								$padding = $l[1];
							}else if($l[0] == "sequence")
							{
								$sq = $l[1];
							}
						}
					}
					
				}
				
				if($sq == '')
				{
					$sql = "SELECT MAX(".$name.") AS value FROM ".$table_name." WHERE status =0";
					
					
					$msg->add("query", $sql);
					$result = $appSession->getTier()->getArray($msg);
					
					$numrows = count($result);
					if($numrows > 0)
					{
						$row = $result[0];
						$value = $row[0];
						if($value != "")
						{
							$value = $appSession->getTool()->next_value($value);
						}
						
					}
					if($value == ""){
						$value = "1";
					}
				}else
				{
					$sq = $appSession->getTool()->replace($sq, "{yy}", $d->getYY());
					$sq = $appSession->getTool()->replace($sq, "{yyyy}", $d->getYears());
					$sq = $appSession->getTool()->replace($sq, "{YY}", $d->getYY());
					$sq = $appSession->getTool()->replace($sq, "{YYYY}", $d->getYears());
					$sq = $appSession->getTool()->replace($sq, "{mm}", $d->getMonths());
					$sq = $appSession->getTool()->replace($sq, "{MM}", $d->getMonths());
					$sq = $appSession->getTool()->replace($sq, "{dd}", $d->getDays());
					$sq = $appSession->getTool()->replace($sq, "{DD}", $d->getDays());
					$sq = $appSession->getTool()->replace($sq, "{h}", $d->getHours());
					$sq = $appSession->getTool()->replace($sq, "{m}", $d->getMinutes());
					$sq = $appSession->getTool()->replace($sq, "{s}", $d->getSeconds());
					$sq = $appSession->getTool()->replace($sq, "{mi}", $d->getMilliseconds());
					$sq = $appSession->getTool()->replace($sq, "{ww}", $ww);
					for($m =0; $m<count($data_lines); $m++)
					{
						
						$items1 = explode('~', $data_lines[$m]);
						if(count($items1)>3)
						{
							$name1 = $items1[0];
							$value1 = $items1[1];
						}
						$sq = $appSession->getTool()->replace($sq, "{".$name1."}", $value1);
						
					}
					$value = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("root_company_id"), $sq);
				}
				
				
				
						
				if($padding == "")
				{
					$padding = "0";
				}
				for($n=0; $n<$padding_left; $n++)
				{
					if($appSession->getTool()->lenght($value)<$padding_left)
					{
						$value = $padding.$value;
					}
				}
				
				for($n=0; $n<$padding_right; $n++)
				{
					if($appSession->getTool()->lenght($value)<$padding_right)
					{
						$value = $value.$padding;
					}
				}
				
				$v = $appSession->getTool()->replace($v, "{sequence}", $value);
				
				$v = $appSession->getTool()->replace($v, "{yy}", $d->getYY());
				$v = $appSession->getTool()->replace($v, "{yyyy}", $d->getYears());
				$v = $appSession->getTool()->replace($v, "{mm}", $d->getMonths());
				$v = $appSession->getTool()->replace($v, "{dd}", $d->getDays());
				$v = $appSession->getTool()->replace($v, "{h}", $d->getHours());
				$v = $appSession->getTool()->replace($v, "{m}", $d->getMinutes());
				$v = $appSession->getTool()->replace($v, "{s}", $d->getSeconds());
				$v = $appSession->getTool()->replace($v, "{mi}", $d->getMilliseconds());
				$v = $appSession->getTool()->replace($v, "{ww}", $ww);
				for($m =0; $m<count($data_lines); $m++)
				{
					
					$items1 = explode('~', $data_lines[$m]);
					if(count($items1)>3)
					{
						$name1 = $items1[0];
						$value1 = $items1[1];
					}
					$v = $appSession->getTool()->replace($v, "{".$name1."}", $value1);
					
				}
					
				$value = $v;
				while(true)
				{
					$index = $appSession->getTool()->indexOf($value, '[');
					$index1 = $appSession->getTool()->indexOf($value, ']');
					if($index1 >$index)
					{
						$key = $appSession->getTool()->substring($value, $index+1, $index1-1 );
						$arr = $appSession->getTool()->split($key, ';');
						$key_value = "";
						if(count($arr) == 3)
						{
							$key_value = $appSession->getTier()->getValue("SELECT ".$arr[0]." FROM ".$arr[1]." WHERE id='".$arr[2]."'");
						}
						$value = $appSession->getTool()->replace($value, "[".$key."]", $key_value);
						
					}else{
						break;
					}
				}
				
				if(strlen($value)>=36)
				{
					$value = "";
				}
			}
			$datas[count($datas)] =[$name, $value, $type];
			
			
		}
		
	}
	
	
	if($error != "")
	{
		echo $error;
		exit;
	}
	
	$sql = "SELECT *, NOW() AS current_date FROM ".$table_name." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getTable($msg);
	$numrows = $result->getRowCount();
	$isAdded = true;
	if($numrows == 0)
	{
		$sql = "INSERT INTO ".$table_name."(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_uid";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		
	}else
	{
		$isAdded = false;
		$row = $result->getRow(0);
		$data = "";
		for($i =0; $i<count($datas); $i++)
		{
			$name = $datas[$i][0];
			if($name != "id")
			{
				if($row->getString($name) != $datas[$i][1])
				{
					if($data != "")
					{
						$data = $data.", ";
					}
					
					$data = $data.$items[0].":".$row->getString($name)." > ".$datas[$i][1];
					
				}
				
			}
		}
		if($data != "")
		{
			$data = "id: ".$row->getString("id")."\t".$data;
			$current_date = $row->getString("current_date");
			$path = ABSPATH."disk/log_data";
			if($current_date != "")
			{
				$firstIndex = $appSession->getTool()->indexOf($current_date, " ");
				if($firstIndex != -1)
				{
					$arr_date =  $appSession->getTool()->split(substr($current_date, 0, $firstIndex), "-");
					if(count($arr_date)>2)
					{
						$path = $path."/".$arr_date[0]."/". + $arr_date[1]."/". + $arr_date[2];
					}
				}
			}
			
			
			if(is_dir($path) == false)
			{
				if (!mkdir($path, 0777, true)) {
					die('Failed to create folders...');
				}
			}
			$file_name = $path."/".$table_name.".log";
			
			$file = NULL;
			if(file_exists($file_name))
			{
				$file = fopen($file_name, "a");
			}else
			{
				$file = fopen($file_name, "w");
			}
			fwrite($file, $current_date."\t\t"."user_id=".$appSession->getUserInfo()->getId()."\t\t".$data."\r\n");
			fclose($file);
		}
		
		
	}
	
	if($error == "")
	{
		
		$sql = "UPDATE ".$table_name." SET write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";
		
		for($i =0; $i<count($datas); $i++)
		{
			$name = $datas[$i][0];
			if($name != "id")
			{
				
				if($datas[$i][2] == "date" && $datas[$i][1] == "")
				{
					$sql = $sql.", ".$name."=NULL";
				}else if($datas[$i][2] == "datetime" && $datas[$i][1] == "")
				{
					$sql = $sql.", ".$name."=NULL";
				}else if(($datas[$i][2] == "float" || $datas[$i][2] == "integer" || $datas[$i][2] == "currency") && $datas[$i][1] == "")
				{
					$sql = $sql.", ".$name."=0";
					
					
				}else if($datas[$i][2] == "password" )
				{
					if($datas[$i][1] != "")
					{
						$pass = $datas[$i][1];
						
						$s = $appSession->getTool()->toHash("sha256", "[".$id."]".$pass);
						$len = strlen($pass);
						for($i = 0; $i<$len; $i++)
						{
							$s = $s.chr($i + 48);
						}
						$pass = $appSession->getTool()->toHash("md5", $s);
						$sql = $sql.", ".$name."='".str_replace("'", "''", $pass)."'";
					}
				}
				else{
					$sql = $sql.", ".$name."='".str_replace("'", "''", $datas[$i][1])."'";
				}
			}
		}
		
		$sql = $sql." WHERE id ='".$id."'";
		
		$msg->add("query", $sql);
		echo $sql;
		$result = $appSession->getTier()->exec($msg);
		if($isAdded)
		{
			//rowAdded($appSession, $module_id, $root_module_id, $id);
		}else{
			//rowUpdated($appSession, $module_id, $root_module_id, $id);
		}
	}
	if($error != "")
	{
		echo $error;
	}else
	{
		
		if(SERVICE_URL != "")
		{
			
			if($appSession->getConfig()->getProperty("db_id") != "" && $appSession->getConfig()->getProperty("db_id") != "master")
			{
				//$data = "session_id=".$appSession->getConfig()->getProperty("session_id")."&db_id=".$appSession->getConfig()->getProperty("db_id")."&id=".$id."&type=atabase.hq.changed&table_name=".$table_name;
				//$appSession->getTool()->postMessage($appSession, $
				//$appSession->getTool()->httpPost(SERVICE_URL."data_changed", $data);
			}
			
		}
		echo "OK:".$id;
	}
	
	
}else if($ac == "delLine")
{
	$msg = $appSession->getTier()->createMessage();
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$table_name = '';
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$items = explode(",", $id);
	for($i =0; $i<count($items); $i++)
	{
		$id = $items[$i];
		$sql = "UPDATE ".$table_name." SET status =1, write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."' WHERE id='".$id."'";
		
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		rowDeleted($appSession, $module_id, $root_module_id, $id);
	}
	
	echo 'OK';
}else if($ac == "action")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$value = '';
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$sql = "SELECT resource FROM res_resource WHERE rel_id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	if($numrows>0)
	{
		$row = $result[0];
		$resource = "<?php ".$row["resource"]." ?>";
		$path = ABSPATH."disk/".$id.".php";
		$myfile = fopen($path, "w") or die("Unable to open file!");
		fwrite($myfile, $resource);
		fclose($myfile);
		require_once($path);
	}
	
}else if($ac == "multipleRel")
{
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	
	$table_name = '';
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$column_src = '';
	if(isset($_REQUEST['column_src']))
	{
		$column_src = $_REQUEST['column_src'];
	}
	$column_rel = '';
	if(isset($_REQUEST['column_rel']))
	{
		$column_rel = $_REQUEST['column_rel'];
	}
	$src_value = '';
	if(isset($_REQUEST['src_value']))
	{
		$src_value = $_REQUEST['src_value'];
	}
	$data = [];
	if(isset($_REQUEST['ids']))
	{
		$data = explode(',', $_REQUEST['ids']);
	}
	
	$sql = "UPDATE ".$table_name." SET status =1, write_date=NOW() WHERE ".$column_src."='".$src_value."'";
	$ids = "";
	for($i=0; $i<count($data); $i++)
	{
		if($data[$i] != "")
		{
			if($ids != "")
			{
				$ids = $ids." AND ";
			}
			$ids = $ids." ".$column_rel." !='".$data[$i]."'";
		}
	}
	if($ids != "")
	{
		$sql = $sql. " AND ".$ids;
	}
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	if($ids != "")
	{
		$ids = "";
		$sql = "UPDATE ".$table_name." SET status =0, write_date=NOW() WHERE ".$column_src."='".$src_value."' AND status =1";
		for($i=0; $i<count($data); $i++)
		{
			if($data[$i] != "")
			{
				if($ids != "")
				{
					$ids = $ids." OR ";
				}
				$ids = $ids." ".$column_rel." ='".$data[$i]."'";
			}
		}
		$sql = $sql. " AND ( ".$ids .")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		
	}
	
	
	$sql = "SELECT ".$column_rel." FROM ".$table_name." WHERE ".$column_src."='".$src_value."' AND status =0";
	
	$msg->add("query", $sql);
	$result_selected = $appSession->getTier()->getArray( $msg);
	$numrows_selected = count($result_selected);	
	$arr_selected = [];
	for($i =0; $i<$numrows_selected; $i++)
	{
		$row = $result_selected[$i];
		$arr_selected[count($arr_selected)] = $row[0];
	}
	for($i=0; $i<count($data); $i++)
	{
		if($data[$i] != "")
		{
			$hasItem = false;
			for($j=0; $j<count($arr_selected); $j++)
			{
				if($data[$i]== $arr_selected[$j])
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem  == false)
			{
				$sql = "INSERT INTO ".$table_name."(";
				$sql = $sql."id";
				$sql = $sql.", create_uid";
				$sql = $sql.", create_date";
				$sql = $sql.", write_uid";
				$sql = $sql.", write_date";
				$sql = $sql.", company_id";
				$sql = $sql.", status";
				$sql = $sql.", ".$column_src;
				$sql = $sql.", ".$column_rel;
				$sql = $sql." )VALUES(";
				$sql = $sql."'".$appSession->getTool()->getId()."'";
				$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
				$sql = $sql.", ".$appSession->getTier()->getDateString();
				$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
				$sql = $sql.", ".$appSession->getTier()->getDateString();
				$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
				$sql = $sql.", 0";
				$sql = $sql.", '".$src_value."'";
				$sql = $sql.", '".$data[$i]."'";
				$sql = $sql.")";
				$msg->add("query", $sql);
				$result = $appSession->getTier()->exec($msg);
			}
		}
	}
	echo "OK";
	
	
}else if($ac == "table_calendar_events")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$root_module_id = "";
	if(isset($_REQUEST['root_module_id']))
	{
		$root_module_id = $_REQUEST['root_module_id'];
	}
	$module_model_id = "";
	if(isset($_REQUEST['module_model_id']))
	{
		$module_model_id = $_REQUEST['module_model_id'];
	}
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	$fdate = '';
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$tdate = '';
	if(isset($_REQUEST['tdate']))
	{
		$tdate = $_REQUEST['tdate'];
	}
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());

	$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0";


	$msg->add("query", $sql);

	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$values = [];
	for($i =0; $i<$numrows; $i++)
	{
		$row =$result[$i];
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$values[$i] = $arr;
		
	}



$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";


$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
$start_date = "";
$end_date = "";
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$arr[3] = $row[3];
	$models[$i] = $arr;
	if($row[3] == "columns")
	{
		$columns_model_id = $row[0];
	}
	else if($row[3] == "start_date")
	{
		$start_date = findValue($values, $row[0]);
	}else if($row[3] == "end_date")
	{
		$end_date = findValue($values, $row[0]);
	}
	
				
}
$columns = [];
$table_name = "";
$data_id = "";
$action_ids = "";
for($i =0; $i<count($models); $i++)
{
	if($models[$i][1] == $columns_model_id)
	{
		$caption = "";
		$name = "";
		$type = "";
		$align = "";
		$forecolor = "";
		$backcolor = "";
		$width = "";
		$format = "";
		$total = "";
		for($j =0; $j<count($models); $j++)
		{
			if($models[$j][1] == $models[$i][0])
			{
				$value = findValue($values, $models[$j][0]);
				if($models[$j][3] == 'caption')
				{
					$caption = $value;
				}
				else if($models[$j][3] == 'name')
				{
					$name = $value;
				}else if($models[$j][3] == 'type')
				{
					$type = $value;
					if($type == "status")
					{
						$hasStatus = 1;
					}
				}else if($models[$j][3] == 'align')
				{
					$align = $value;
				}else if($models[$j][3] == 'forecolor')
				{
					$forecolor = $value;
				}else if($models[$j][3] == 'backcolor')
				{
					$backcolor = $value;
				}else if($models[$j][3] == 'width')
				{
					$width = $value;
				}else if($models[$j][3] == 'format')
				{
					$format = $value;
				}else if($models[$j][3] == 'total')
				{
					$total = $value;
				}
			}
		}
		$arr = [];
		$arr[0] = $caption;
		$arr[1] = $name;
		$arr[2] = $type;
		
		$arr[3] = $width;
		$arr[4] = $align;
		$arr[5] = $forecolor;
		$arr[6] = $backcolor;
		$arr[7] = $format;
		$arr[8] = $total;
		$arr[9] = $name;
		$columns[count($columns)] = $arr;
	}
	if($models[$i][3] == "src" || $models[$i][2] == "data")
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}
	if($models[$i][3] == "action" || $models[$i][2] == "action")
	{
		$ac_id = findValue($values, $models[$i][0]);
		if($ac_id != "")
		{
			if($action_ids != "")
			{
				$action_ids = " OR ";
			}
			$action_ids = $action_ids." d2.id='".$ac_id."'";
		}
		
	}
}


$column = "";
$name = "";
$p = 0;
$ps = 20;
$search = "";
$sort = "";
$rel_id = "";
$filters = "";
$arr_params = explode("&", $params);
for($i =0; $i<count($arr_params); $i++)
{
	$arr = explode("=", $arr_params[$i]);
	if(count($arr)>1)
	{
		if($arr[0] == "search")
		{
			$search = $arr[1];
			
		}else if($arr[0] == "sort")
		{
			$sort = $arr[1];
		}
		else if($arr[0] == "p")
		{
			$p = $arr[1];
		}else if($arr[0] == "ps")
		{
			$ps = $arr[1];
		}else if($arr[0] == "rel_id")
		{
			$rel_id = $arr[1];
			
		}else if($arr[0] == "filters")
		{
			$filters = $arr[1];
			
		}
	}
}

if($data_id == "")
{
	echo "Data is not support";
}else
{
	
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
	

	$condition = "";
	$group_by = "";
	$order_by = "";
	$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$data_id."' AND status =0";
	$msg->add("query", $sql);

	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	if($numrows_data_sql>0)
	{
		$row1 = $result_data_sql[0];
		$condition = $row1[0];
		$group_by = $row1[1];
		$order_by = $row1[2];
	}
	$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$data_id."' ORDER BY create_date ASC";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);
	$sql_params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$sql_params[$m] = $arr;
		
	}
	if($module_id != $root_module_id)
	{
		$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$root_module_id."' ORDER BY create_date ASC";
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
		$msg->add("query", $sql);
		$result_data_sql = $appSession->getTier()->getArray($msg);
		$numrows_data_sql = count($result_data_sql);
		for($m =0; $m<$numrows_data_sql; $m++)
		{
			$row1 = $result_data_sql[$m];	
			$hasItem = 0;
			for($n = 0; $n<count($sql_params); $n++)
			{
				if($sql_params[$n][0] == $row1[0])
				{
					$hasItem = 1;
					$sql_params[$n][0] = $row1[0];
					$sql_params[$n][1] = $row1[1];
					$sql_params[$n][2] = $row1[2];
				}
			}
			if($hasItem == 0)
			{
				$arr = [];
				$arr[0] = $row1[0];
				$arr[1] = $row1[1];
				$arr[2] = $row1[2];
				$sql_params[$m] = $arr;
			}
			
			
		}
	}
	
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.data, d1.relationship, d2.order_by FROM ir_data_line  d1 LEFT OUTER JOIN ir_data d2 ON(d1.data_id = d2.id) WHERE d1.data_id ='".$data_id."' AND d1.status =0";
				
	$lines = [];
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$arr[3] = $row1[3];
		$arr[4] = $row1[4];
		$arr[5] = $row1[5];
		$lines[$m] = $arr;
		
		if($table_name == "" && $row1[1] == "")
		{
			$table_name = $row1[2];
		}
		
	}
	if($filters != "")
	{
		if($appSession->getTool()->indexOf($filters, "assigned") != -1)
		{
			$arr = [];
			
			$conditions = explode(',', $filters);
			
			for($i =0; $i<count($conditions); $i++)
			{
				if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") == -1)
				{
					
					if($condition != "")
					{
						$condition = $condition.",";
					}
					$condition = $condition.$conditions[$i];
				}
			}
			$condition = $appSession->getTool()->replace($condition, ":", "=");
			
			
		}else
		{
			if($condition != "")
			{
				$condition = $condition.",";
			}
			$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
		}
		
	}
	$conditions = $appSession->getTier()->condition_array($condition);

	
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
	
	
	
	if($condition == "")
	{
		$sql = $sql." WHERE ".$table_name.".status=0";
	}else{
		$sql = $sql." AND ".$table_name.".status=0";
	}
	$sql = $sql." AND (".$table_name.".".$start_date.">='".$fdate."' AND ".$table_name.".".$end_date."<='".$tdate."')";
	
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	if($appSession->getTool()->indexOf($filters, "assigned") != -1)
	{
		$conditions = explode(',', $filters);
		$condition = "";
		$values = "";
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
			{
				
				$index = $appSession->getTool()->indexOf( $conditions[$i], "v:");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($conditions[$i], $index + 2);
				}
				
				$index = $appSession->getTool()->indexOf($values, ";");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($values, 0, $index);
				}
				
				$arr = $appSession->getTool()->split($values, "~");
				$values = "";
				for($j=0; $j<count($arr); $j++)
				{
					if($values != "")
					{
						$values = $values.",";
					}
					$values = $values."'".$arr[$j]."'";
				}
				
			}
		}
		
		$sql = str_replace("{user_id}", $values, $sql);
	}else{
		$sql = str_replace("{user_id}", "'".$appSession->getUserInfo()->getId()."'", $sql);
	}
	if($appSession->getTool()->indexOf($filters, "assigned") != -1)
	{
		$conditions = explode(',', $filters);
		$condition = "";
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
			{
				$values = "";
				$index = $appSession->getTool()->indexOf( $conditions[$i], "v:");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($conditions[$i], $index + 2);
				}
				
				$index = $appSession->getTool()->indexOf($values, ";");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($values, 0, $index);
				}
				
				$arr = $appSession->getTool()->split($values, "~");
				$values = "";
				for($j=0; $j<count($arr); $j++)
				{
					if($values != "")
					{
						$values = $values." or ";
					}
					$values = $values." resource_id='".$arr[$i]."'";
				}
				
				$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND (".$values.") AND type='".$table_name."')", $sql);
			}
		}
	}else{
		$sql = str_replace("{assigned}", "select d1.rel_id from res_assign d1 left outer join res_assign_status d2 ON(d1.status_id = d2.id) where d1.status =0 AND (d1.resource_id='".$appSession->getUserInfo()->getId()."' or d1.resource_id='".$appSession->getUserInfo()->getCompanyId()."') AND d1.type='".$table_name."' AND (d1.status_id = '' OR d1.status_id IS NULL OR d2.hidden=0)", $sql);
	}
	$sql = str_replace("{company}", "'".$appSession->getUserInfo()->getCompanyId()."'", $sql);
	$sql = str_replace("{root_company}", "'".$appSession->getConfig()->getProperty("root_company_id")."'", $sql);
	$arr = explode(',', $appSession->getConfig()->getProperty("child_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{child_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("parent_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{parent_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("company_ids"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}

	
	$sql = str_replace("{companies}", $c_company_id, $sql);
	
	if($sort == ""&& count($lines)>0)
	{
		$sort = $lines[0][5];
	}
	$searchs = [];
	for($c =0; $c<count($columns); $c++)
	{
		if($columns[$c][1] != ""  && $columns[$c][2] == "")
		{
			$searchs[count($searchs)] = str_replace("__", ".", $columns[$c][1]);
		}
	}
	
	$search = trim($search);
	if(count($searchs)>0 && $search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
	}
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	
	
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	
}
?>
[
	<?php

	for($i=0; $i<$dt->getRowCount(); $i++)
	{
		if($i>0)
		{
			echo ",";
		}
		$row = $dt->getRow($i);
		$id = $row->getString($table_name."__id");
		$start = $row->getString($table_name."__".$start_date);
		$end = $row->getString($table_name."__".$end_date);
		$title = "";
		$start = $appSession->getTool()->replace($start, " ", "T");
		$end = $appSession->getTool()->replace($end, " ", "T");
		for($c =0; $c<count($columns); $c++)
		{
			
			$catpion = $columns[$c][0];
			$name = $columns[$c][1];
			
			$type = $columns[$c][2];
			if($name != "")
			{
				$name = $row->getString($name);
				if($type == "date" && $name != "")
				{
					
					if($format != "")
					{
						$name = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($name), $format);
					}else{
						$name = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($name));
					}
					
				}else if($type == "float" && $name != "")
				{
					$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name));
					
				}
				else if($type == "int" && $name != "")
				{
					
					$name = $appSession->getFormats()->getINT()->format($appSession->getTool()->toInt($name));
				}else if($type == "currency")
				{
					if($name == "")
					{
						$name = 0;
					}
					
					$currency_id = $appSession->getConfig()->getProperty("currency_id");
					for($c1 =0; $c1<count($columns); $c1++)
					{
						$name1 = $columns[$c1][1];
						
						if($name1 == "currency_id")
						{
							$currency_id = $row->getString($name1);
							break;
						}
					}
					$name = $appSession->getCurrency()->format($currency_id, $appSession->getTool()->toDouble($name));
				}else if($type == "percent" && $name != "")
				{
					$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name)) ."%";
				}
				if($title != "")
				{
					$title = $title.", ";
				}
				$name = $appSession->getTool()->replace($name, "\"", "&quot;");
				$title = $title.$caption.": ".$name;
			}
				
		}
	?>
    {
	  "id": "<?php echo $id;?>",
      "title": "<?php echo $title;?>",
      "start": "<?php echo $start;?>",
      "end": "<?php echo $end;?>",
	  "start_name": "<?php echo $start_date;?>",
	  "end_name": "<?php echo $end_date;?>",
	  "name": "<?php echo $table_name;?>"
    }
	<?php
	}
	?>
  ]
<?php
}else if($ac == "table_calendar_update")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$fdate = '';
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$tdate = '';
	if(isset($_REQUEST['tdate']))
	{
		$tdate = $_REQUEST['tdate'];
	}
	$start_date = '';
	if(isset($_REQUEST['start_date']))
	{
		$start_date = $_REQUEST['start_date'];
	}
	$end_date = '';
	if(isset($_REQUEST['end_date']))
	{
		$end_date = $_REQUEST['end_date'];
	}
	$table_name = '';
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$sql = "UPDATE ".$table_name." SET write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$start_date."='".$fdate."'";
	$sql = $sql.", ".$end_date."='".$tdate."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}
?>