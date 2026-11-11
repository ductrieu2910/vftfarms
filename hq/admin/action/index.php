<?php

if($appSession->getUserInfo()->getId() == "")
{
	echo "Access deny";
	exit;
}

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}
$msg = $appSession->getTier()->createMessage();
if($ac == "add")
{
	
	
	
	$p = '';
	if(isset($_REQUEST['p']))
	{
		$p = $_REQUEST['p'];
	}
	$table_name = "";
	$arr = $appSession->getTool()->split($p, '&');
	for($i=0; $i<count($arr); $i++)
	{
		$item = $arr[$i];
		$index = $appSession->getTool()->indexOf($item, '=');
		if($index != -1)
		{
			$key = $appSession->getTool()->substring($item, 0, $index);
			$value = $appSession->getTool()->substring($item, $index + 1);
			if($key == "table_name")
			{
				$table_name = $value;
			}
		}
	}
	

	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO ".$table_name."(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	for($i=0; $i<count($arr); $i++)
	{
		$item = $arr[$i];
		$index = $appSession->getTool()->indexOf($item, '=');
		if($index != -1)
		{
			$key = $appSession->getTool()->substring($item, 0, $index);
			if($key != "table_name")
			{
				$sql = $sql.", ".$key;
			}
		}
	}
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	for($i=0; $i<count($arr); $i++)
	{
		$item = $arr[$i];
		$index = $appSession->getTool()->indexOf($item, '=');
		if($index != -1)
		{
			
			$value = $appSession->getTool()->substring($item, $index + 1);
			if($key != "table_name")
			{
				$sql = $sql.", '".$appSession->getTool()->replace($value, "'", "''")."'";
			}
		}
	}
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "save")
{
	
	
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
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$value = '';
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	if($type == "n")
	{
		if($value == "")
		{
			$value = "0";
		}
		
	}
	$sql = "UPDATE ".$table_name." SET ".$name." ='".str_replace("'", "''", $value). "'";
	$sql = $sql.", write_date=".$appSession->getTier()->getDateString().", write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "del")
{
	
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ".$table_name." SET status =1, write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."' WHERE id ='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($sql);
	echo "OK";
}else if($ac == "updateRel")
{
	
	
	$table_name = '';
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$name_value = '';
	if(isset($_REQUEST['name_value']))
	{
		$name_value = $_REQUEST['name_value'];
	}
	
	$rel_value = '';
	if(isset($_REQUEST['rel_value']))
	{
		$rel_value = $_REQUEST['rel_value'];
	}
	
	$column = '';
	if(isset($_REQUEST['column']))
	{
		$column = $_REQUEST['column'];
	}
	$column_value = '';
	if(isset($_REQUEST['column_value']))
	{
		$column_value = $_REQUEST['column_value'];
	}
	
	$sql = "SELECT id FROM ".$table_name." WHERE ".$name."='".$name_value."' AND ".$rel_id."='".$rel_value."'";
	$msg->add("query", $sql);
	$id = $appSession->getTier()->getValue($msg);
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO ".$table_name."(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}
	$sql = "UPDATE ".$table_name." SET ".$column." ='".str_replace("'", "''", $column_value). "'";
	$sql = $sql.", write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$name."='".$name_value."', ".$rel_id."='".$rel_value."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	
	
	echo $id;
}else if($ac == "postStatus")
{
	$msg = $appSession->getTier()->createMessage();
	
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$status_id = '';
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$sql = "UPDATE ir_module_status_log SET status =2";
	$sql = $sql.", write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE rel_id='".$rel_id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	$sql = "SELECT next_id FROM ir_module_status WHERE id='".$status_id."' AND status =0";
	$msg->add("query", $sql);
	$value = $appSession->getTier()->getValue($msg);
	if($value != "")
	{
		$status_id = $value;
	}
	
	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO ir_module_status_log(";
	$sql = $sql."id";
	$sql = $sql.", create_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_uid";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", rel_id";
	$sql = $sql.", status_id";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$status_id."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "recent_module")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT id FROM res_user_setting WHERE user_id='".$appSession->getUserInfo()->getId()."' AND k ='".$id."' AND type='recent_module'";
	
	$msg->add("query", $sql);
	$log_id = $appSession->getTier()->getValue($msg);
	if($log_id != "")
	{
		$sql ="UPDATE res_user_setting SET status =0, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$log_id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		echo "Ok";
		exit();
	}
	
	
	$log_id = $appSession->getTool()->getId();
	$sql = "INSERT INTO res_user_setting(";
	$sql = $sql."id";
	$sql = $sql.", create_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_uid";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", user_id";
	$sql = $sql.", k";
	$sql = $sql.", type";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$log_id."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", '".$id."'";
	$sql = $sql.", 'recent_module'";
	$sql = $sql.")";
	$msg->add("query", $sql);

	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}

?>