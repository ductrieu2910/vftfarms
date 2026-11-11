<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "BOM")
{
	$production_id = '';
	if(isset($_REQUEST['production_id']))
	{
		$production_id = $_REQUEST['production_id'];
	}
	$quantity = 1;
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	$planning_id = '';
	if(isset($_REQUEST['planning_id']))
	{
		$planning_id = $_REQUEST['planning_id'];
	}
	
	
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity * d2.quantity * (SELECT quantity FROM mrp_Planning WHERE id='".$planning_id."' LIMIT 1)) AS quantity FROM mrp_routing d1 LEFT OUTER JOIN mrp_bom_rel d2 ON(d1.id = d2.rel_id AND d2.status = 0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom d3 ON(d2.bom_id = d3.id AND d3.status =0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON(d3.id = d4.bom_id AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d1.production_id='".$production_id."' AND d1.status =0";
	$sql = $sql." GROUP BY d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code, d5.name , d6.name, d7.name , d8.name ORDER BY d5.name ASC";
	$msg->add("query", $sql);
	
	$dt_bom = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity  * (SELECT quantity FROM mrp_Planning WHERE id='".$planning_id."' LIMIT 1)) AS quantity FROM mrp_routing d1";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON((d1.id = d4.bom_id OR d1.id = d4.routing_id) AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d1.production_id='".$production_id."' AND d1.status =0";
	$sql = $sql." GROUP BY d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code, d5.name , d6.name, d7.name , d8.name ORDER BY d5.name ASC";
	$msg->add("query", $sql);
	$dt_material = $appSession->getTier()->getTable($msg);
	
	for($i =0; $i<$dt_material->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_bom->getRowCount(); $j++)
		{
			if($dt_material->getString($i, "product_id") == $dt_bom->getString($j, "product_id") && $dt_material->getString($i, "unit_id") == $dt_bom->getString($j, "unit_id") && $dt_material->getString($i, "attribute_id") == $dt_bom->getString($j, "attribute_id") && $dt_material->getString($i, "type_id") == $dt_bom->getString($j, "type_id"))
			{
				$hasItem = true;
				$quantity = $dt_bom->getFloat($j, "quantity") + $dt_material->getFloat($i, "quantity");
				$dt_bom->setValue($quantity, $j, "quantity");
				break;
			}			
		}
		if($hasItem == false)
		{
			$dt_bom->addArray($dt_material->getDataRow($i));
		}
	}
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity * d2.quantity * (SELECT quantity FROM mrp_Planning WHERE id='".$planning_id."' LIMIT 1)) AS quantity, 0.0 AS inventory, 0.0 AS AS proccess, 0.0 AS request FROM mrp_bom_rel d2";
	$sql = $sql." LEFT OUTER JOIN mrp_bom d3 ON(d2.bom_id = d3.id AND d3.status =0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON(d3.id = d4.bom_id AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d2.rel_id='".$production_id."' AND d2.status =0";
	$sql = $sql." GROUP BY d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code, d5.name , d6.name, d7.name , d8.name ORDER BY d5.name ASC";
	$msg->add("query", $sql);
	$dt_bom_production = $appSession->getTier()->getTable($msg);
	if($dt_bom->getRowCount() == 0)
	{
		$dt_bom = $dt_bom_production;
	}else{
		for($i =0; $i<$dt_bom_production->getRowCount(); $i++)
		{
			$hasItem = false;
			for($j =0; $j<$dt_bom->getRowCount(); $j++)
			{
				if($dt_bom_production->getString($i, "product_id") == $dt_bom->getString($j, "product_id") && $dt_bom_production->getString($i, "unit_id") == $dt_bom->getString($j, "unit_id") && $dt_bom_production->getString($i, "attribute_id") == $dt_bom->getString($j, "attribute_id") && $dt_bom_production->getString($i, "type_id") == $dt_bom->getString($j, "type_id"))
				{
					$hasItem = true;
					$quantity = $dt_bom->getFloat($j, "quantity") + $dt_bom_production->getFloat($i, "quantity");
					$dt_bom->setValue($quantity, $j, "quantity");
					break;
				}			
			}
			if($hasItem == false)
			{
				$dt_bom->addArray($dt_bom_production->getDataRow($i));
			}


		}
	}
	$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, SUM(d1.quantity) AS quantity FROM purchase_request_product d1 LEFT OUTER JOIN purchase_request d2 ON(d1.request_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$planning_id."' AND d1.status =0 AND d2.status =0 GROUP BY d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id";
	$msg->add("query", $sql);
	$dt_request = $appSession->getTier()->getTable($msg);
	
	for($i =0; $i<$dt_bom->getRowCount(); $i++)
	{
		$quantity = $dt_bom->getFloat($i, "quantity");
		$request = 0;
		for($j =0; $j<$dt_request->getRowCount(); $j++)
		{
			if($dt_request->getString($j, "product_id") == $dt_bom->getString($i, "product_id") && $dt_request->getString($j, "unit_id") == $dt_bom->getString($i, "unit_id") && $dt_request->getString($j, "attribute_id") == $dt_bom->getString($i, "attribute_id") && $dt_request->getString($j, "type_id") == $dt_bom->getString($i, "type_id"))
			{
				$request = $request + $dt_request->getFloat($j, "quantity");
			}			
		}
		
		$inventory = 0;
		$proccess = 0;
		
		$qty = $quantity - $request - $inventory - $proccess;
		$dt_bom->setValue($inventory, $j, "inventory");
		$dt_bom->setValue($proccess, $j, "proccess");
		$dt_bom->setValue($qty, $j, "request");
		
		
	}
	echo $appSession->getTool()->respTable($dt);
	
	
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
	
	$sql = "UPDATE stock SET proccess=2, write_date=".$appSession->getTier()->getDateString();
    $sql = $sql. ", write_uid='".$user_id."'";
    $sql = $sql. " WHERE id='".$id."'";
    $sql = $sql. "; UPDATE project_task SET status_id='done', proccess=2, write_date=NOW() WHERE rel_id='".$task_id."'";
	if($task_id != "")
	{
		 $sql = $sql. "; UPDATE res_assign SET status_id='done', proccess=2, write_date=NOW() WHERE rel_id='".$task_id."'";
	}
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}

?>