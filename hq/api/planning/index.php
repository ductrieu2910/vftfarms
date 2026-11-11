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
	$create_uid = '';
	if(isset($_REQUEST['create_uid']))
	{
		$create_uid = $_REQUEST['create_uid'];
	}
	
	$sql = "SELECT d1.warehouse_id FROM stock_warehouse_rel d1 LEFT OUTER JOIN mrp_workcenter_line_flow d2 ON(d1.rel_id = d2.id AND d2.status =0) LEFT OUTER JOIN mrp_workcenter_line_rel d3 ON(d2.id = d3.line_id AND d3.status =0) LEFT OUTER JOIN hr_line_department d4 ON(d3.rel_id = d4.id AND d4.status =0) LEFT OUTER JOIN hr_employee_rel d5 ON(d4.id = d5.rel_id AND d5.status =0) LEFT OUTER JOIN res_user_company d6 ON(d5.employee_id = d6.id AND d6.status =0) WHERE d6.user_id='".$create_uid."'";
	$msg->add("query", $sql);
	$dt_warehouse = $appSession->getTier()->getTable($msg);

	
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity * d2.quantity) AS quantity, 0.0 AS inventory, 0.0 AS proccess, 0.0 AS request, '' AS description, 0.0 AS planning FROM mrp_routing d1 LEFT OUTER JOIN mrp_bom_rel d2 ON(d1.id = d2.rel_id AND d2.status = 0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom d3 ON(d2.bom_id = d3.id AND d3.status =0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON(d3.id = d4.bom_id AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d1.production_id='".$production_id."' AND d1.status =0 AND d3.status =0 AND d4.status =0 AND d5.status =0";
	$sql = $sql." GROUP BY d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code, d5.name , d6.name, d7.name , d8.name ORDER BY d5.name ASC";
	$msg->add("query", $sql);
	$dt_bom = $appSession->getTier()->getTable($msg);
	
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity) AS quantity, 0.0 AS inventory, 0.0 AS proccess, 0.0 AS request, '' AS description, 0.0 AS planning FROM mrp_routing d1";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON((d1.id = d4.bom_id OR d1.id = d4.routing_id) AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d1.production_id='".$production_id."' AND d1.status =0 AND d4.status =0 AND d5.status =0";
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
	
	$sql = "SELECT d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity) AS quantity, 0.0 AS inventory, 0.0 AS proccess, 0.0 AS request, '' AS description, 0.0 AS planning FROM mrp_bom_rel d2";
	$sql = $sql." LEFT OUTER JOIN mrp_bom d3 ON(d2.bom_id = d3.id AND d3.status =0)";
	$sql = $sql." LEFT OUTER JOIN mrp_bom_line d4 ON(d3.id = d4.bom_id AND d4.status=0)";
	$sql = $sql." LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id)";
	$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id)";
	$sql = $sql." LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id)";
	$sql = $sql." WHERE d2.rel_id='".$production_id."' AND d2.status =0 AND d3.status =0 AND d4.status=0 AND d5.status =0";
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
	
	$product_ids = "";
	$warehouse_ids = "";
	for($i = 0; $i<$dt_warehouse->getRowCount(); $i++)
	{
		if($warehouse_ids != "")
		{
			$warehouse_ids = $warehouse_ids." OR ";
		}
		$warehouse_ids = $warehouse_ids." d1.warehouse_id='".$dt_warehouse->getString($i, "warehouse_id")."'";
	}
	for($i =0; $i<$dt_bom->getRowCount(); $i++)
	{
		if($product_ids != "")
		{
			$product_ids = $product_ids." OR ";
		}
		$product_ids = $product_ids." d1.warehouse_id='".$dt_bom->getString($i, "warehouse_id")."'";
	}
	$sql = "SELECT d1.product_id, d1.unit_id, d1.type_id, d1.attribute_id, SUM(d1.quantity - d1.quantity_out) AS quantity FROM stock_product d1 WHERE d1.status =0";
	if($product_ids != "")
	{
		$sql = $sql." AND (".$product_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	if($warehouse_ids != "")
	{
		$sql = $sql." AND (".$warehouse_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY d1.product_id, d1.unit_id, d1.type_id, d1.attribute_id";
	$msg->add("query", $sql);
	$dt_inventory = $appSession->getTier()->getTable($msg);

	
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
		for($j =0; $j<$dt_inventory->getRowCount(); $j++)
		{
			if($dt_inventory->getString($j, "product_id") == $dt_bom->getString($i, "product_id") && $dt_inventory->getString($j, "unit_id") == $dt_bom->getString($i, "unit_id") && $dt_inventory->getString($j, "attribute_id") == $dt_bom->getString($i, "attribute_id") && $dt_inventory->getString($j, "type_id") == $dt_bom->getString($i, "type_id"))
			{
				$inventory = $inventory + $dt_request->dt_inventory($j, "quantity");
			}			
		}
		
		$proccess = 0;
		
		$qty = $quantity - $request - $inventory - $proccess;
		
		$dt_bom->setValue($inventory, $i, "inventory");
		$dt_bom->setValue($proccess, $i, "proccess");
		$dt_bom->setValue($qty, $i, "request");
		
		
		
	}
	echo $appSession->getTool()->respTable($dt_bom);
	
}else if($ac == "production_bom")
{
	$production_ids = '';
	if(isset($_REQUEST['production_ids']))
	{
		$production_ids = $_REQUEST['production_ids'];
	}
	
	$arr = $appSession->getTool()->split($production_ids, ",");
	$production_ids = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($production_ids != "")
		{
			$production_ids = $production_ids." OR ";
		}
		$production_ids = $production_ids." d1.rel_id='".$arr[$i]."'";
	}
	$sql = "SELECT d1.rel_id AS production_id, d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code AS product_code, d5.name AS product_name , d6.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, SUM(d4.quantity) AS quantity FROM mrp_bom_rel d1 LEFT OUTER JOIN mrp_bom_line d4 ON((d1.bom_id = d4.bom_id OR d1.id = d4.routing_id) AND d4.status=0) LEFT OUTER JOIN product d5 ON(d4.product_id = d5.id) LEFT OUTER JOIN product_unit d6 ON(d4.unit_id = d6.id) LEFT OUTER JOIN attribute d7 ON(d4.attribute_id = d7.id) LEFT OUTER JOIN product_type d8 ON(d4.type_id = d8.id) WHERE d1.status =0 AND d4.status =0 AND d5.status =0 ";
	if($production_ids != "")
	{
		$sql = $sql." AND (".$production_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY d4.product_id, d4.unit_id, d4.attribute_id, d4.type_id, d5.code, d5.name , d6.name, d7.name , d8.name, d1.rel_id ORDER BY d5.name ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);

	echo $appSession->getTool()->respTable($dt);
	
}else if($ac == "productInventory")
{
	$product_ids = '';
	if(isset($_REQUEST['product_ids']))
	{
		$product_ids = $_REQUEST['product_ids'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$sql = "SELECT warehouse_id FROM stock_warehouse_rel WHERE status =0 AND rel_id ='".$location_id."'";
	$msg->add("query", $sql);
	$warehouseList = $appSession->getTier()->getArray($msg);
	$warehouse_ids = "";
	for($i =0; $i<count($warehouseList); $i++)
	{
		if($warehouse_ids != "")
		{
			$warehouse_ids = $warehouse_ids." OR ";
		}
		$warehouse_ids = $warehouse_ids." d1.warehouse_id='".$warehouseList[$i][0]."'";
	}
	
	$arr = $appSession->getTool()->split($product_ids, ",");
	$product_ids = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($product_ids != "")
		{
			$product_ids = $product_ids." OR ";
		}
		$product_ids = $product_ids." d1.product_id='".$arr[$i]."'";
	}
	$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d4.name AS attribute_name, d5.name AS type_name,d7.code AS category_code,  d7.name AS category_name, SUM(d1.quantity-d1.quantity_out) AS quantity FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d1.unit_id = d3.id) LEFT OUTER JOIN attribute d4 ON(d1.attribute_id = d4.id) LEFT OUTER JOIN product_type d5 ON(d1.type_id = d5.id) LEFT OUTER JOIN stock_warehouse d6 ON(d1.warehouse_id = d6.id) LEFT OUTER JOIN stock_warehouse_category d7 ON(d6.category_id = d7.id) WHERE d1.status =0 AND d1.status =0 AND d2.status =0";
	if($product_ids != "")
	{
		$sql = $sql." AND (".$product_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	if($warehouse_ids != "")
	{
		$sql = $sql." AND (".$warehouse_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d2.code, d2.name, d3.name, d4.name, d5.name, d7.code, d7.name";
	$msg->add("query", $sql);
	
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "production_product")
{
	$production_ids = '';
	if(isset($_REQUEST['production_ids']))
	{
		$production_ids = $_REQUEST['production_ids'];
	}
	
	$arr = $appSession->getTool()->split($production_ids, ",");
	$production_ids = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($production_ids != "")
		{
			$production_ids = $production_ids." OR ";
		}
		$production_ids = $production_ids." d1.rel_id='".$arr[$i]."'";
	}
	
	$sql = "SELECT d1.id, d1.rel_id AS production_id, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d1.quantity, d1.percent FROM mrp_production_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d1.unit_id = d3.id)  WHERE d1.status =0";
	if($production_ids != "")
	{
		$sql = $sql." AND (".$production_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$dt_product = $appSession->getTier()->getTable($msg);
	
	$production_ids = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($production_ids != "")
		{
			$production_ids = $production_ids." OR ";
		}
		$production_ids = $production_ids." d1.production_id='".$arr[$i]."'";
	}
	
	$sql = "SELECT d1.id, d1.code, d1.name, d2.name AS category_name, d1.description FROM mrp_routing d1 LEFT OUTER JOIN mrp_routing_category d2 ON(d1.category_id = d2.id)   WHERE d1.status =0";
	if($production_ids != "")
	{
		$sql = $sql." AND (".$production_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$dt_routing = $appSession->getTier()->getTable($msg);
	$sql = "SELECT id, rel_id, routing_id, time_amount, percent FROM mrp_routing_job WHERE status = 0";
	$ids = "";
	for($j =0; $j<$dt_routing->getRowCount(); $j++)
	{
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." routing_id='".$dt_routing->getString($j, "id")."'";
	}
	if($ids != "")
	{
		$sql = $sql." AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	
	$msg->add("query", $sql);
	$dt_job = $appSession->getTier()->getTable($msg);
	
	for($j =0; $j<$dt_product->getRowCount(); $j++)
	{
		$product_id = $dt_product->getString($j, "id");
		
		$time_amount = 0;
		$quantity = 0;
		
		for($n =0; $n<$dt_routing->getRowCount(); $n++)
		{
			
			$routing_id = $dt_routing->getString($n, "id");
			for($k =0; $k<$dt_job->getRowCount(); $k++)
			{
				if($dt_job->getString($k, "routing_id") == $routing_id && $dt_job->getString($k, "rel_id") == $product_id){
					$time_amount = $dt_job->getFloat($k, "time_amount");
					$quantity = $dt_job->getFloat($k, "quantity");
				}
			}
		}
		//$quantity = 0.5;
		//$dt_product->setValue($quantity, $j, "percent");
	}
	echo $appSession->getTool()->respTable($dt_product);
}

?>