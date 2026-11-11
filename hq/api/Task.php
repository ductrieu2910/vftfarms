<?php
class Task
{
	
	public function getName($appSession, $id, $table_name)
	{
		$msg = $appSession->getTier()->createMessage();
		$name = "";
		$start_date = date("Y-m-d H:i:s");
		$end_date = date("Y-m-d H:i:s");
		if($table_name == "")
		{
			$sql = "SELECT d1.start_date, d1.end_date, d1.receipt_no AS planning_code, d3.receipt_no AS order_no, d5.code AS product_code, d5.name AS product_name, d4.code AS customer_code, d4.name AS customer_name FROM mrp_planning d1 LEFT OUTER JOIN sale_order_product d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN sale_order d3 ON(d2.order_id = d3.id) LEFT OUTER JOIN customer d4 ON(d3.customer_id = d4.id) LEFT OUTER JOIN product d5 ON(d2.product_id = d5.id) WHERE d1.id='".$id."'";
			$msg->add("query", $sql);
			
			$dt = $appSession->getTier()->getTable($msg);
			if($dt->getRowCount()>0)
			{
				$start_date = $dt->getString(0, "start_date");
				$end_date = $dt->getString(0, "end_date");
				$name = "#: ".$dt->getString(0, "planning_code")."/".$dt->getString(0, "order_no")." - ".$dt->getString(0, "product_code").". ".$dt->getString(0, "product_name")."(".$dt->getString(0, "customer_code").". ".$dt->getString(0, "customer_name").")";
			}
		}
		return [$start_date, $end_date, $name];
	}
	public function addResource($appSession, $title, $rel_id, $resource, $module_id)
	{
		
		$msg = $appSession->getTier()->createMessage();
		$arr = $appSession->getTool()->split($resource, ';');
		
		for($i =0; $i<count($arr); $i++)
		{
			$items = $appSession->getTool()->split($arr[$i], ',');
			
			if(count($items)>=3)
			{
				$assign_id = $items[0];
				$resource_id = $items[1];
				$resource_name = "";
				if(count($items)>2)
				{
					$resource_name = $items[2];
				}
				$resource_type = "";
				if(count($items)>2)
				{
					$resource_type = $items[2];
				}
				
				$resource_job_title_id = "";
				if(count($items)>4)
				{
					$resource_job_title_id = $items[4];
				}
				$resource_job_id = "";
				if(count($items)>5)
				{
					$resource_job_id = $items[5];
				}
				$resource_position_id = "";
				if(count($items)>6)
				{
					$resource_position_id = $items[6];
				}
				$sql = "SELECT id FROM res_assign WHERE rel_id='".$rel_id."' AND resource_id='".$resource_id."' AND status =0";
				$msg->add("query", $sql);
				
				$assign_id = $appSession->getTier()->getValue($msg);
				if($assign_id == "")
				{
					$assign_id = $appSession->getTool()->getId();
					$builder = $appSession->getTier()->createBuilder("res_assign");
					$builder->add("id", $assign_id);
					$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("name", $resource_name);
					$builder->add("resource_id", $resource_id);
					$builder->add("module_id", $module_id);
					$builder->add("type", $resource_type);
					$builder->add("proccess", 0);
					$builder->add("job_title_id", $resource_job_title_id);
					$builder->add("job_id", $resource_job_id);
					$builder->add("position_id", $resource_position_id);
					$builder->add("rel_id", $rel_id);
					$builder->add("status", 0);
					$sql = $appSession->getTier()->getInsert($builder);
				
					$msg->add("query", $sql);
					$r = $appSession->getTier()->exec($msg);
					
					$users =[];
					if($resource_type == "hr_line_department")
					{
						$sql = "SELECT DISTINCT d1.user_id from res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN hr_employee_rel d3 ON(d2.id = d3.employee_id) WHERE d1.status =0 AND d2.status =0 AND (d2.terminated=0 OR d2.terminated IS NULL) AND d3.rel_id ='".$resource_id."'";
						$msg->add("query", $sql);
						
						
						$users = $appSession->getTier()->getArray($msg);
						
					}else if($resource_type == "res_company")
					{
						$sql = "SELECT DISTINCT d1.user_id from res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND (d2.terminated=0 OR d2.terminated IS NULL) AND d1.company_id ='".$resource_id."'";
						$msg->add("query", $sql);
						$users = $appSession->getTier()->getArray($msg);
						
					}else if($resource_type == "hr_department")
					{
						$sql = "SELECT DISTINCT d1.user_id from res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND (d2.terminated=0 OR d2.terminated IS NULL) AND d2.department_id ='".$resource_id."'";
						$msg->add("query", $sql);
						$users = $appSession->getTier()->getArray($msg);
						
					}else if($resource_type == "hr_employee")
					{
						$sql = "SELECT DISTINCT d1.user_id from res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND (d2.terminated=0 OR d2.terminated IS NULL) AND d2.id ='".$resource_id."'";
						$msg->add("query", $sql);
						$users = $appSession->getTier()->getArray($msg);
						
					}else if($resource_type == "user"|| $resource_type == "res_user")
					{
						$sql = "SELECT DISTINCT d1.user_id from res_user_company d1 WHERE d1.status =0 AND d1.user_id ='".$resource_id."'";
						$msg->add("query", $sql);
						$users = $appSession->getTier()->getArray($msg);
						
					}
				
					if(count($users)>0)
					{
						$sql = "";
						$to = [];
						for($j =0; $j<count($users); $j++)
						{
							if($sql != "")
							{
								$sql = $sql."; ";
							}
							$sql = $sql." INSERT INTO res_notification(id, write_uid, create_date, status,company_id, description,seen, rel_id, notification_id, type, create_uid, write_date) VALUES('".$appSession->getTool()->getId()."', '".$appSession->getUserInfo()->getId()."', ".$appSession->getTier()->getDateString().", 0, '".$appSession->getUserInfo()->getCompanyId()."', '".$appSession->getTool()->replace($title, "'", "''")."', 0, '".$rel_id."', '8e92b44e-ec28-44b3-e31f-dee5792253a1', 'res_assign', '".$users[$j][0]."', ".$appSession->getTier()->getDateString().")";
							$to[count($to)]= ['id' => $users[$j][0], 'type' => 'user'];
						}
						if($sql != "")
						{
							
							$msg->add("query", $sql);
							$r = $appSession->getTier()->exec($msg);
							$r = $appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification");
						}
					}
					
				}
			}	
		}
	}
	public function addTask($appSession, $id, $name, $start_date, $end_date, $proccess, $parent_id, $sequence, $rel_id, $proccess_id, $priority_id, $status_id, $description, $resource, $module_id, $category_id, $user_id, $location_id)
	{
		
		$msg = $appSession->getTier()->createMessage();
		$builder = $appSession->getTier()->createBuilder("project_task");
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("name", $name);
		$builder->add("description", $description);
		$builder->add("start_date", $start_date);
		$builder->add("end_date", $end_date);
		if($proccess == "")
		{
			$proccess = "0";
		}
		$builder->add("parent_id", $parent_id);
		$builder->add("sequence", $sequence);
		$builder->add("location_id", $location_id);
		
		$builder->add("proccess_id", $proccess_id);
		$builder->add("priority_id", $priority_id);
		$builder->add("category_id", $category_id);
		$builder->add("proccess", $proccess);
		
		//$builder->add("user_id", $appSession->getConfig()->getProperty("user_id"));
		$sql = "SELECT id FROM project_task WHERE id='".$id."'";
		
		
		$msg->add("query", $sql);
		$check_id = $appSession->getTier()->getValue($msg);
		if($check_id == "")
		{
			$builder->add("resource_id", $module_id);
			$builder->add("rel_id", $rel_id);
			$builder->add("create_uid", $user_id);
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status", 0);
			$builder->add("receipt_no", $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "project_task"));
			$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
			if($id == "")
			{
				$id = $appSession->getTool()->getId();
			}
			$builder->add("id", $id);
			$sql = $appSession->getTier()->getInsert($builder);
			$msg->add("query", $sql);
			
			$appSession->getTier()->exec($msg);
			if($status_id != "")
			{
				$sql = "INSERT INTO project_status_rel(id, create_uid, rel_id, status_id, status, proccess)VALUES('".$appSession->getTool()->getId()."', '".$user_id."', '".$id."', '".$status_id."', 0, ".$proccess.")";
				$msg->add("query", $sql);
				//$appSession->getTier()->exec($msg);
			}
			$this->addResource($appSession, $name, $id, $resource, $module_id);
		}else{
			$builder->add("id", $id);
			$sql = $appSession->getTier()->getUpdate($builder);
			$msg->add("query", $sql);
			
			$appSession->getTier()->exec($msg);
			
			$this->addResource($appSession, $name, $id, $resource, $module_id);
		}
		
		
		return $id;
	}

	function removeAssign($appSession, $id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT create_uid FROM res_notification WHERE rel_id='".$id."'";
		$msg->add("query", $sql);
		$users = $appSession->getTier()->getArray($msg);
		$to = [];
		for($j =0; $j<count($users); $j++)
		{
			$to[count($to)]= ['id' => $users[$j][0], 'type' => 'user'];
		}
		
		$sql = "UPDATE res_assign SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'; UPDATE res_notification SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE rel_id='".$id."'";
		
		$msg->add("query", $sql);
		echo $appSession->getTier()->exec($msg);
		if(count($to)>0)
		{
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification");
		}
		
	}
	function updateAssignProccess($appSession, $assign_id, $proccess)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "UPDATE res_assign SET proccess=".$proccess.", write_date=".$appSession->getTier()->getDateString()." WHERE id='".$assign_id."';UPDATE res_assign_status_log SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE assign_id='".$assign_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		$sql = "SELECT d2.user_id FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) WHERE d1.id='".$assign_id."'";
		$msg->add("query", $sql);
		
		$user_id = $appSession->getTier()->getValue($msg);
		if($user_id != "")
		{
			$to = [["id" => $user_id, "type" => "user"]];
			
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_assign&session_id=".$appSession->getSessionId());
		}
	}
	function updateAssignStatus($appSession, $assign_id, $status_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "UPDATE res_assign_status_log SET status =1 WHERE assign_id='".$assign_id."' AND status =0";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		if($status_id != "")
		{
			$sql = "SELECT id FROM res_assign_status_log WHERE assign_id='".$assign_id."'";
			$msg->add("query", $sql);
			$log_id = $appSession->getTier()->getValue($msg);
			if($log_id != "")
			{
				$sql = "UPDATE res_assign_status_log SET status =2, status_id='".$status_id."' WHERE id='".$log_id."'";
				$msg->add("query", $sql);
				$r = $appSession->getTier()->exec($msg);
			}else{
				$builder = $appSession->getTier()->createBuilder("res_assign_status_log");
				$id = $appSession->getTool()->getId();
				$builder->add("id", $id);
				$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("status_id", $status_id);
				$builder->add("assign_id", $assign_id);
				$builder->add("status", 0);
				$sql = $appSession->getTier()->getInsert($builder);
				$msg->add("query", $sql);
				$r = $appSession->getTier()->exec($msg);
			}
		}
		$sql = "SELECT d2.user_id FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) WHERE d1.id='".$assign_id."'";
		$msg->add("query", $sql);
		
		$user_id = $appSession->getTier()->getValue($msg);
		if($user_id != "")
		{
			$to = [["id" => $user_id, "type" => "user"]];
			
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_assign&session_id=".$appSession->getSessionId());
		}
	}
	function updateResAssignStatus($appSession, $assign_id, $status_id, $description, $proccess)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "UPDATE res_assign SET write_date=".$appSession->getTier()->getDateString().", end_work=".$appSession->getTier()->getDateString()." WHERE id='".$assign_id."' AND start_work IS NOT NULL";
		$status = 'todo';
		if($proccess == "2"){
			$status = "done";
		}
		if($proccess == "1"){
			$status = "doing";
		}
		$sql = $sql.";UPDATE res_assign SET proccess=".$proccess.", status_id='".$status."', write_date=".$appSession->getTier()->getDateString().", start_work=".$appSession->getTier()->getDateString()." WHERE id='".$assign_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		
		
		$builder = $appSession->getTier()->createBuilder("res_status_line");
		$id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status_id", $status_id);
		$builder->add("rel_id", $assign_id);
		$builder->add("description", $description);
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		
		$sql = "SELECT d2.user_id, d2.name, d1.rel_id FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) WHERE d1.id='".$assign_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		if(count($values)>0 && $values[0][0] != "")
		{
			$to = [["id" => $values[0][0], "type" => "user"]];
			$title = $values[0][1];
			$sql = "SELECT name FROM res_status WHERE id='".$status_id."'";
			$msg->add("query", $sql);
			
			$title = $title." - ".$appSession->getTier()->getValue($msg);
			
			$sql = "INSERT INTO res_notification(id, write_uid, create_date, status,company_id, description,seen, rel_id, notification_id, type, create_uid, write_date) VALUES('".$appSession->getTool()->getId()."', '".$appSession->getUserInfo()->getId()."', ".$appSession->getTier()->getDateString().", 0, '".$appSession->getUserInfo()->getCompanyId()."', '".$appSession->getTool()->replace($title, "'", "''")."', 0, '".$values[0][2]."', '8e92b44e-ec28-44b3-e31f-dee5792253a1', 'res_assign', '".$values[0][0]."', ".$appSession->getTier()->getDateString().")";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification");
		}
		return $id;
	}
	function updateUserStatus($appSession, $user_id, $rel_id, $status_id, $proccess)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "UPDATE project_task SET status_id='".$status_id."', proccess=".$proccess." WHERE id='".$rel_id."' AND create_uid='".$user_id."'";
		$sql = $sql.";UPDATE res_assign SET proccess=".$proccess.", status_id='".$status_id."', write_date=NOW() WHERE rel_id='".$rel_id."'";
		
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
			
		if($user_id != "")
		{
			$to = [["id" => $user_id, "type" => "user"]];
		
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=project_status_rel&session_id=".$appSession->getSessionId());
		}
		echo "OK";
	}
	function addStatusColumn($appSession, $user_id, $name){
		$msg = $appSession->getTier()->createMessage();
		$builder = $appSession->getTier()->createBuilder("project_status");
		$id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("write_uid", $user_id);
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("name", $name);
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		if($user_id != "")
		{
			$to = [["id" => $user_id, "type" => "user"]];
			
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=project_status&session_id=".$appSession->getSessionId());
		}
	}
	
	
}

?>