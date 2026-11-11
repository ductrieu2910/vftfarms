<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
function createRoutingLine($appSession, $workorder_id, $routing_parent_id, $routing_id, $start, $end, $quantity, $description, $sequence, $code, $name)
{
	$msg = $appSession->getTier()->createMessage();
	
	if($quantity == "")
	{
		$quantity = 1;
	}
	if($sequence == "")
	{
		$sequence = 0;
	}
	
	
	$workorder_routing_id= $appSession->getTool()->getId();	
	$sql = "INSERT INTO mrp_workorder_routing(";
	$sql = $sql."id";
	$sql = $sql.", create_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_uid";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", routing_id";
	$sql = $sql.", workorder_id";
	$sql = $sql.", proccess";
	$sql = $sql.", start_date";
	$sql = $sql.", end_date";
	$sql = $sql.", description";
	$sql = $sql.", parent_id";
	$sql = $sql.", quantity";
	$sql = $sql.", sequence";
	$sql = $sql.", code";
	$sql = $sql.", name";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$workorder_routing_id."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$routing_id."'";
	$sql = $sql.", '".$workorder_id."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$start."'";
	$sql = $sql.", '".$end."'";
	$sql = $sql.", '".str_replace("'", "''", $description)."'";
	$sql = $sql.", '".$routing_parent_id."'";
	$sql = $sql.", ".$quantity;
	$sql = $sql.", ".$sequence;
	$sql = $sql.", '".str_replace("'", "''", $code)."'";
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	
	$rs = $appSession->getTier()->exec($msg);
	
	$sql = "INSERT INTO mrp_workorder_routing_material(id, product_id, unit_id, attribute_id, type_id, quantity, workorder_routing_id, status, company_id) SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, '".$workorder_routing_id."' AS workorder_routing_id, d1.status, d1.company_id FROM mrp_bom_line d1 WHERE d1.status =0 AND d1.bom_id ='".$routing_id."' ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	
	$rs = $appSession->getTier()->exec($msg);
	
	return $workorder_routing_id;
}
function createRouting($appSession, $workorder_id, $dDate, $sdate, $edate, $quantity, $routings, $parent_id, $routing_parent_id)
{

	$msg = $appSession->getTier()->createMessage();
	
	for($i =0; $i<$routings->getRowCount(); $i++)
	{
		if($routings->getString($i, "parent_id") == $parent_id)
		{
			$routing_id = $routings->getString($i, "id");
			$description = $routings->getString($i, "description");
			$routing_quantity = $routings->getString($i, "quantity");
			$sequence =$routings->getString($i, "sequence");
			$code = $routings->getString($i, "code");
			$name = $routings->getString($i, "name");
			$sql = "SELECT quantity, unit_id, start_time, end_time FROM mrp_routing_calendar WHERE rel_id='".$routing_id."' AND status =0 ORDER BY create_date ASC";
			$msg->add("query", $sql);
			
			$calendar = $appSession->getTier()->getArray($msg);
			if(count($calendar) >0)
			{
				for($l =0; $l<count($calendar); $l++)
				{
					$d = $sdate;
					$times =  $calendar[$l][0];
					$unit_id = $calendar[$l][1];
					$start_time = $calendar[$l][2];
					$end_time = $calendar[$l][3];
					
					$add_type = "";
					if($unit_id == "year")
					{
						$add_type = "years";
					}else if($unit_id == "month")
					{
						$add_type = "month";
					}else if($unit_id == "week")
					{
						$add_type = "week";
					}else if($unit_id == "day")
					{
						$add_type = "days";
					}else if($unit_id == "hour")
					{
						$add_type = "hours";
					}else if($unit_id == "minute")
					{
						$add_type = "minutes";
					}else if($unit_id == "second")
					{
						$add_type = "seconds";
					}
					if($appSession->getTool()->indexOf($times, '-') == -1)
					{
						$times = "+".$times;
					}
					if($add_type != "")
					{
						$times = $times." ".$add_type;
						
						$d = strtotime($times, $sdate);
						if($start_time == "")
						{
							$start_time = "00:01";
						}
						if($end_time == "")
						{
							$end_time = "23:59";
						}
						$start = date("Y-m-d", $d)." ".$start_time.":00";
						$end = date("Y-m-d", $d)." ".$end_time.":59";
						
						
						$workorder_routing_id= createRoutingLine($appSession,$workorder_id, $routing_parent_id , $routing_id, $start, $end, $routing_quantity, $description, $sequence, $code, $name) ;
				
						createRouting($appSession, $workorder_id, $dDate, $sdate, $edate, $routing_quantity, $routings, $routing_id, $workorder_routing_id);
					}
					
				}
			

			}else{
				
				$workorder_routing_id= createRoutingLine($appSession,$workorder_id, $routing_parent_id, $routing_id, date("Y-m-d", $sdate)." 01:00:00", date("Y-m-d", $edate)." 23:59:59", $routing_quantity, $description, $sequence, $code, $name) ;

				createRouting($appSession, $workorder_id, $dDate, $sdate, $edate, $routing_quantity, $routings, $routing_id, $workorder_routing_id);
			}
			
			
		}
	}
	
}

if($ac == "createWorkOrderFromPlanning")
{
	$planning_id = '';
	if(isset($_REQUEST['planning_id']))
	{
		$planning_id = $_REQUEST['planning_id'];
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
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "mrp_workorder");
	
	$sql = "SELECT start_date, end_date, quantity, production_id FROM mrp_planning WHERE id='".$planning_id."'";
	$msg->add("query", $sql);
	$dt_planning = $appSession->getTier()->getTable($msg);
	if($dt_planning->getRowCount()>0)
	{
		$start_date = $dt_planning->getString(0, "start_date");
		$end_date = $dt_planning->getString(0, "end_date");
		$quantity = $dt_planning->getString(0, "quantity");
		$production_id = $dt_planning->getString(0, "production_id");
		
		$builder = $appSession->getTier()->createBuilder("mrp_workorder");
		$id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("write_uid", $user_id);
		$builder->add("production_id", $production_id);
		$builder->add("quantity", $quantity);
		$builder->add("proccess", 0);
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		
		if($start_date != "")
		{
			$builder->add("start_date", $start_date);
		}else{
			$builder->add("start_date", $appSession->getTier()->getDateString(), 'f');
		}
		if($end_date != "")
		{
			$builder->add("end_date", $end_date);
		}else{
			$builder->add("end_date", $appSession->getTier()->getDateString(), 'f');
		}
		$builder->add("rel_id", $planning_id);

	
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $user_id);
		$builder->add("status", 0);
		$builder->add("company_id", $company_id);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		$sql = "SELECT d1.id, d1.parent_id, d1.description, d1.quantity, d1.sequence, d1.code, d1.name FROM mrp_routing d1 WHERE d1.status =0 AND d1.production_id='".$production_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
		$sdate= strtotime($start_date);
		$edate= strtotime($end_date);
		
		$msg->add("query", $sql);
		$routings = $appSession->getTier()->getTable($msg);
		createRouting($appSession, $id, $sdate, $sdate, $edate, $quantity, $routings, "", "");
	}
	
	echo $id;
	
}else if($ac == "createWorkOrderFromProduction")
{
	$production_id = '';
	if(isset($_REQUEST['production_id']))
	{
		$production_id = $_REQUEST['production_id'];
	}
	$product_id = '';
	if(isset($_REQUEST['product_id']))
	{
		$product_id = $_REQUEST['product_id'];
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
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "mrp_workorder");
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
	
	
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$quantity = 1;
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	if($start_date == "")
	{
		$start_date = date("Y-m-d h:i:s");
	}
	if($end_date == "")
	{
		$end_date = date("Y-m-d h:i:s");
	}

	
	$builder = $appSession->getTier()->createBuilder("mrp_workorder");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("write_uid", $user_id);
	$builder->add("production_id", $production_id);
	$builder->add("category_id", $category_id);
	$builder->add("product_id", $product_id);
	$builder->add("quantity", $quantity);
	$builder->add("proccess", 0);
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("receipt_no", $receipt_no);
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	
	if($start_date != "")
	{
		$builder->add("start_date", $start_date);
	}else{
		$builder->add("start_date", $appSession->getTier()->getDateString(), 'f');
	}
	if($end_date != "")
	{
		$builder->add("end_date", $end_date);
	}else{
		$builder->add("end_date", $appSession->getTier()->getDateString(), 'f');
	}
	$builder->add("rel_id", $rel_id);
	$builder->add("location_id", $location_id);

	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $user_id);
	$builder->add("status", 0);
	$builder->add("company_id", $company_id);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	if($production_id != ""){
		$sql = "SELECT d1.id, d1.parent_id, d1.description, d1.quantity, d1.sequence, d1.code, d1.name FROM mrp_routing d1 WHERE d1.status =0 AND d1.production_id='".$production_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
		$sdate= strtotime($start_date);
		$edate= strtotime($end_date);
		
		$msg->add("query", $sql);
		$routings = $appSession->getTier()->getTable($msg);
		createRouting($appSession, $id, $sdate, $sdate, $edate, $quantity, $routings, "", "");
	}
	echo $id;
}else if($ac == "addRouting")
{
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
	$workorder_id = '';
	if(isset($_REQUEST['workorder_id']))
	{
		$workorder_id = $_REQUEST['workorder_id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
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
	
	$start_work = '';
	if(isset($_REQUEST['start_work']))
	{
		$start_work = $_REQUEST['start_work'];
	}
	$end_work = '';
	if(isset($_REQUEST['end_work']))
	{
		$end_work = $_REQUEST['end_work'];
	}
	$builder = $appSession->getTier()->createBuilder("mrp_workorder_routing");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("write_uid", $user_id);
	$builder->add("workorder_id", $workorder_id);
	$builder->add("name", $name);
	$builder->add("proccess", 0);
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');

	if($start_work != "")
	{
		$builder->add("start_work", $start_work);
	}else{
		$builder->add("start_work", $appSession->getTier()->getDateString(), 'f');
	}
	if($end_work != "")
	{
		$builder->add("end_work", $end_work);
	}
	if($start_date != "")
	{
		$builder->add("start_date", $start_date);
	}else{
		$builder->add("start_date", $appSession->getTier()->getDateString(), 'f');
	}
	if($end_date != "")
	{
		$builder->add("end_date", $end_work);
	}
	$builder->add("quantity", 1);
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $user_id);
	$builder->add("status", 0);
	$builder->add("company_id", $company_id);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "endWorkOrderRouting")
{
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
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE mrp_workorder_routing SET write_uid='".$user_id."', end_work=NOW() WHERE id='".$id."' AND end_work IS NULL";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "addDeviceToRouting")
{
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
	$asset_id = '';
	if(isset($_REQUEST['asset_id']))
	{
		$asset_id = $_REQUEST['asset_id'];
	}
	$workorder_routing_id = '';
	if(isset($_REQUEST['workorder_routing_id']))
	{
		$workorder_routing_id = $_REQUEST['workorder_routing_id'];
	}
	$builder = $appSession->getTier()->createBuilder("mrp_workorder_routing_device");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("write_uid", $user_id);
	$builder->add("routing_id", $workorder_routing_id);
	$builder->add("asset_id", $asset_id);
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');

	if($start_work != "")
	{
		$builder->add("start_work", $start_work);
	}else{
		$builder->add("start_work", $appSession->getTier()->getDateString(), 'f');
	}
	if($end_work != "")
	{
		$builder->add("end_work", $end_work);
	}
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $user_id);
	$builder->add("status", 0);
	$builder->add("company_id", $company_id);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
	
}else if($ac == "removeDeviceToRouting")
{
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
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE mrp_workorder_routing_device SET status =1, write_uid='".$user_id."' WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "removeWorkOrderRouting")
{
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
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE mrp_workorder_routing SET status =1, write_uid='".$user_id."' WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "close")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$sql = "SELECT id FROM project_task WHERE rel_id='".$id."'";
	$msg->add("query", $sql);
	$task_id = $appSession->getTier()->getValue($msg);
	
	$sql = "UPDATE mrp_workorder SET proccess=2, write_date=".$appSession->getTier()->getDateString();
    $sql = $sql. ", write_uid='".$user_id."'";
    $sql = $sql. " WHERE id='".$id."'";
    $sql = $sql. "; UPDATE project_task SET status_id='done', proccess=2, write_date=NOW() WHERE id='".$task_id."'";
	if($task_id != "")
	{
		 $sql = $sql. "; UPDATE res_assign SET status_id='done', proccess=2, write_date=NOW() WHERE rel_id='".$task_id."'";
	}
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}

?>