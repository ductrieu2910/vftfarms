<?php
require_once(ABSPATH.'api/Task.php' );
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}

if($ac == "addStatusLine")
{
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = '';
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	
	$assign_id = '';
	if(isset($_REQUEST['assign_id']))
	{
		$assign_id = $_REQUEST['assign_id'];
	}
	$status_rel_id = '';
	if(isset($_REQUEST['status_rel_id']))
	{
		$status_rel_id = $_REQUEST['status_rel_id'];
	}
	$status_id = '';
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$task_id = '';
	if(isset($_REQUEST['task_id']))
	{
		$task_id = $_REQUEST['task_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$sql = "SELECT name, description FROM res_status WHERE id ='".$status_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$status_name = "";
	$status_description = "";
	if(count($values)>0)
	{
		$status_name = $values[0][0];
		$status_description = $values[0][1];
	}
	
	$sql = "SELECT d1.create_uid, d1.name, d1.description, d2.name AS resource_name, d3.module_id FROM project_task d1 LEFT OUTER JOIN res_user d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN res_assign d3 ON(d1.id = d3.rel_id AND d3.id ='".$assign_id."') WHERE d1.id ='".$task_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values)>0)
	{
		$line_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_status_line");
		$builder->add("id", $line_id);
		$builder->add("write_uid", $user_id);
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $user_id);
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("description", $description);
		$builder->add("name", $status_name);
		$builder->add("rel_id", $id);
		$builder->add("status_id", $status_id);
		$builder->add("status_rel_id", $status_rel_id);
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		
		if($values[0][0] != "")
		{
			$create_uid = $values[0][0];
			
			$resource_name = $values[0][3];
			$module_id = $values[0][4];
			$resource_type = "user";
			$assign_id = $appSession->getTool()->getId();
			$builder = $appSession->getTier()->createBuilder("res_assign");
			$builder->add("id", $assign_id);
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status_id", $status_id);
			$builder->add("resource_id", $create_uid);
			$builder->add("name", $resource_name);
			$builder->add("module_id", $module_id);
			$builder->add("type", $resource_type);
			$builder->add("proccess", 0);
			$builder->add("job_title_id", "");
			$builder->add("job_id", "");
			$builder->add("position_id", "");
			$builder->add("rel_id", $task_id);
			$builder->add("description", $status_name."/".$description);
			$builder->add("status", 0);
			$sql = $appSession->getTier()->getInsert($builder);
		
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			
			$to = [];
			$title = $values[0][1]." -> ".$status_name." - ".$description;
			$sql = "INSERT INTO res_notification(id, write_uid, create_date, status,company_id, description,seen, rel_id, notification_id, type, create_uid, write_date) VALUES('".$appSession->getTool()->getId()."', '".$appSession->getUserInfo()->getId()."', ".$appSession->getTier()->getDateString().", 0, '".$appSession->getUserInfo()->getCompanyId()."', '".$appSession->getTool()->replace($title, "'", "''")."', 0, '".$task_id."', '8e92b44e-ec28-44b3-e31f-dee5792253a1', 'res_assign', '".$values[0][0]."', ".$appSession->getTier()->getDateString().")";
			
			$to[count($to)]= ['id' => $values[0][0], 'type' => 'user'];
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			$r = $appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification");
		}
		$sql = "SELECT rel_id FROM res_status_rel WHERE status_id='".$status_rel_id."' AND type='ACTION' AND status =0";
		$msg->add("query", $sql);
		
		$values = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($values); $i++)
		{
			$action_id = $values[$i][0];
			$params = "id=".$id;
			
			$appSession->getTool()->httpGet(URL."api/action/?action_id=".$action_id."&data=".$appSession->getTool()->urlEncode($params));
		}
		echo $status_description;
	}

}
?>