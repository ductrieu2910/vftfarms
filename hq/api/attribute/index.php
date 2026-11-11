<?php
require_once(ABSPATH.'api/Task.php' );
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
function addTask($appSession, $rel_id, $module_id, $resource, $user_id, $name, $description)
{
	$start_date = date("Y-m-d H:i:s");
	$end_date = date("Y-m-d H:i:s");
	$task = new Task();

	
	return $task->addTask($appSession, "", $name, $start_date, $end_date,0, "", 0, $rel_id, "", "", "", $description, $resource, $module_id, "", $user_id, "");
	
}
function parseValue($appSession, $values, $value)
{
	for($i =0; $i<count($values); $i++)
	{
		$value = $appSession->getTool()->replace($value, "{".$values[$i][0]."}", $values[$i][1]);
	}
	return $value;
}
function createSubReceipt($appSession, $receipt_id, $rel_id, $dt)
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT receipt_no, create_uid, location_id FROM res_receipt WHERE id ='".$receipt_id."'";
	$msg->add("query", $sql);
	
	$dt_receipt = $appSession->getTier()->getTable($msg);
	$receipt_no = "";
	if($dt_receipt->getRowCount()>0)
	{
		$receipt_no = $dt_receipt->getString(0, "receipt_no");
	}
	$sql = "SELECT COUNT(id) FROM res_receipt WHERE parent_id='".$receipt_id."'";
	
	$msg->add("query", $sql);
	$version = $appSession->getTier()->getValue($msg);
	if($version == "")
	{
		$version = "1";
	}else{
		$version = intval($version) + 1;
	}
	$receipt_no = $receipt_no.".". $version;
	
	for($i =0; $i<$dt->getRowCount();$i++)
	{
		if($dt->getString($i, "rel_id") == $rel_id)
		{
			$classification_id = $dt->getString($i, "classification_id");
			$sub_receipt_id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_receipt(id, create_date, write_date, status, company_id, rel_id, res_id, parent_id, receipt_no, receipt_date)VALUES('".$sub_receipt_id."', NOW(), NOW(), 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$receipt_id."', '".$classification_id."', '".$receipt_id."', '".$receipt_no."',  NOW())";
			$msg->add("query", $sql);
			
			$r = $appSession->getTier()->exec($msg);
			createAttribute($appSession, $sub_receipt_id);
			
		}
	}
}
function createAttribute($appSession, $receipt_id)
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT d1.rel_id, d1.res_id FROM res_receipt d1 WHERE d1.id='".$receipt_id."'";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if(count($arr)>0)
	{
		$rel_id = $arr[0][0];
		$res_id = $arr[0][1];
		$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code";
		$sql = $sql. ", d1.category_id";
		$sql = $sql. " FROM res_attribute d1 LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
		$sql = $sql. " LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
		$sql = $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
		$sql = $sql." WHERE d1.status =0 AND d1.rel_id='".$res_id."'";
		$sql = $sql." ORDER BY d1.sequence ASC";
		$msg->add("query", $sql);
		$dt_attribute = $appSession->getTier()->getTable($msg);
		
		$sql = "SELECT id, attribute_id, value";
        $sql = $sql. " FROM res_attribute_line WHERE status =0 AND rel_id='".$receipt_id."'";
		$msg->add("query", $sql);
		$dt_attribute_line = $appSession->getTier()->getTable($msg);
		$values = [
			["NOW", $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime(date("Y-m-d h:i:s")))]
		];
		$sql = "SELECT d1.barcode, d1p.id AS d1id , d2p.id AS d2id, d3p.id AS d3id , d2p.id AS d4id, d5p.id AS d2id, d6p.id AS d6id, d7p.id AS d7id, d50.name AS warehouse_name, d51.name AS warehouse_location_name FROM stock_product d1 LEFT OUTER JOIN purchase_receive_product_local d1p ON(d1.rel_id = d1p.id) LEFT OUTER JOIN stock_product d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN purchase_receive_product_local d2p ON(d2.rel_id = d2p.id) LEFT OUTER JOIN stock_product d3 ON(d2.rel_id = d3.id) LEFT OUTER JOIN purchase_receive_product_local d3p ON(d3.rel_id = d3p.id) LEFT OUTER JOIN stock_product d4 ON(d3.rel_id = d4.id) LEFT OUTER JOIN purchase_receive_product_local d4p ON(d4.rel_id = d4p.id) LEFT OUTER JOIN stock_product d5 ON(d4.rel_id = d5.id) LEFT OUTER JOIN purchase_receive_product_local d5p ON(d5.rel_id = d5p.id) LEFT OUTER JOIN stock_product d6 ON(d5.rel_id = d6.id) LEFT OUTER JOIN purchase_receive_product_local d6p ON(d6.rel_id = d6p.id) LEFT OUTER JOIN stock_product d7 ON(d6.rel_id = d7.id) LEFT OUTER JOIN purchase_receive_product_local d7p ON(d7.rel_id = d7p.id)  LEFT OUTER JOIN stock_warehouse d50 ON(d1.warehouse_id = d50.id) LEFT OUTER JOIN stock_warehouse d51 ON(d1.location_id = d51.id) WHERE d1.id ='".$rel_id."'";
		$msg->add("query", $sql);
	
		$dt_stock_product = $appSession->getTier()->getTable($msg);
		$receive_product_id = "";
		if($dt_stock_product->getRowCount()>0)
		{
			$values[count($values)] = ["barcode", $dt_stock_product->getString(0, "barcode")];
			$values[count($values)] = ["warehouse_name", $dt_stock_product->getString(0, "warehouse_name")];
			$values[count($values)] = ["warehouse_location_name", $dt_stock_product->getString(0, "warehouse_location_name")];
			if($dt_stock_product->getString(0, "d1id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d1id");
			}else if($dt_stock_product->getString(0, "d2id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d2id");
			}else if($dt_stock_product->getString(0, "d3id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d3id");
			}else if($dt_stock_product->getString(0, "d4id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d4id");
			}else if($dt_stock_product->getString(0, "d5id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d5id");
			}else if($dt_stock_product->getString(0, "d6id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d6id");
			}else if($dt_stock_product->getString(0, "d7id") != "")
			{
				$receive_product_id = $dt_stock_product->getString(0, "d7id");
			}
		}else{
			$sql = "SELECT d1.quantity AS receive_quantity, d2.receipt_no AS receive_receipt_no, d2.receipt_date AS receive_receipt_date, d3.code AS supplier_code, d3.name AS supplier_name, d3.address AS supplier_address, d3.phone AS supplier_phone, d3.email AS supplier_email, d3.contact_name AS supplier_contact_name, d3.contact_mobile AS supplier_contact_mobile, d3.email AS supplier_contact_email, d4.code AS product_code, d4.name AS product_name, d5.name AS unit_name, d1.unit_price AS receive_unit_price, d6.name AS type_name, d7.name AS attribute_name, d9.quantity AS stock_quantity, d9.barcode AS stock_barcode, d10.name AS stock_warehouse_name FROM purchase_receive_product_local d1 LEFT OUTER JOIN purchase_receive_local d2 ON(d1.receive_id = d2.id) LEFT OUTER JOIN supplier d3 ON(d2.supplier_id = d3.id) LEFT OUTER JOIN product d4 ON(d1.product_id = d4.id) LEFT OUTER JOIN product_unit d5 ON(d1.unit_id = d5.id) LEFT OUTER JOIN product_type d6 ON(d1.type_id = d6.id) LEFT OUTER JOIN attribute d7 ON(d1.attribute_id = d7.id) LEFT OUTER JOIN stock_product d9 ON(d1.id = d9.rel_id AND d9.status =0) LEFT OUTER JOIN stock_warehouse d10 ON(d9.warehouse_id = d10.id) WHERE (d1.id ='".$receive_product_id."' OR d1.id='".$rel_id."')";
			
			$msg->add("query", $sql);
			$dt_stock_product = $appSession->getTier()->getTable($msg);
			if($dt_stock_product->getRowCount()>0)
			{
				$values[count($values)] = ["receive_quantity", $dt_stock_product->getString(0, "receive_quantity")];
				$values[count($values)] = ["receive_receipt_no", $dt_stock_product->getString(0, "receive_receipt_no")];
				$values[count($values)] = ["receive_receipt_date", $dt_stock_product->getString(0, "receive_receipt_date")];
				$values[count($values)] = ["supplier_code", $dt_stock_product->getString(0, "supplier_code")];
				$values[count($values)] = ["supplier_name", $dt_stock_product->getString(0, "supplier_name")];
				$values[count($values)] = ["supplier_address", $dt_stock_product->getString(0, "supplier_address")];
				$values[count($values)] = ["supplier_phone", $dt_stock_product->getString(0, "supplier_phone")];
				$values[count($values)] = ["supplier_email", $dt_stock_product->getString(0, "supplier_email")];
				$values[count($values)] = ["supplier_contact_name", $dt_stock_product->getString(0, "supplier_contact_name")];
				$values[count($values)] = ["supplier_contact_mobile", $dt_stock_product->getString(0, "supplier_contact_mobile")];
				$values[count($values)] = ["supplier_contact_email", $dt_stock_product->getString(0, "supplier_contact_email")];
				$values[count($values)] = ["product_code", $dt_stock_product->getString(0, "product_code")];
				$values[count($values)] = ["product_name", $dt_stock_product->getString(0, "product_name")];
				$values[count($values)] = ["unit_name", $dt_stock_product->getString(0, "unit_name")];
				$values[count($values)] = ["receive_unit_price", $dt_stock_product->getString(0, "receive_unit_price")];
				$values[count($values)] = ["type_name", $dt_stock_product->getString(0, "type_name")];
				$values[count($values)] = ["attribute_name", $dt_stock_product->getString(0, "attribute_name")];
				$values[count($values)] = ["stock_quantity", $dt_stock_product->getString(0, "stock_quantity")];
				$values[count($values)] = ["stock_barcode", $dt_stock_product->getString(0, "stock_barcode")];
				$values[count($values)] = ["stock_warehouse_name", $dt_stock_product->getString(0, "stock_warehouse_name")];
			}
		}
		
		$sql = "";
		for($i=0; $i<$dt_attribute->getRowCount(); $i++)
		{
			$hasItem = false;
			$line_id = "";
			$attribute_id = $dt_attribute->getString($i, "id");
			for($j=0; $j<$dt_attribute_line->getRowCount(); $j++){
				if($dt_attribute_line->getString($j, "attribute_id") == $attribute_id){
				  $line_id = $dt_attribute_line->getString($j, "id");
				  break;
				}
			}
			
			$value = $dt_attribute->getString($i, "value");
			
			
			if($line_id == "")
			{
				$description ="";
				if($appSession->getTool()->indexOf($value, "{") != -1)
				{
					$description = parseValue($appSession, $values, $value);
				}
				$builder = $appSession->getTier()->createBuilder("res_attribute_line");
				$builder->add("id", $appSession->getTool()->getId());
				$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("rel_id", $receipt_id);
				$builder->add("attribute_id", $dt_attribute->getString($i, "id"));
				$builder->add("description", $description);
				$builder->add("status", 0);
				if($sql != "")
				{
					$sql = $sql.";";
				}
				$sql = $sql."".$appSession->getTier()->getInsert($builder);
			}else{
				/*
				$description ="";
				if($appSession->getTool()->indexOf($value, "{") != -1)
				{
					$description = parseValue($appSession, $values, $value);
					if($sql != "")
					{
						$sql = $sql.";";
					}
					$sql = $sql."UPDATE res_attribute_line SET description='".$description."' WHERE id ='".$line_id."'";
				}*/
			}
		}
		if($sql != "")
		{
			$msg->add("query", $sql);
			$r =$appSession->getTier()->exec($msg);
		}
				  
		
	}
}
if($ac == "done")
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
	$module_id = "79b9c2fa-63a1-44aa-9deb-dfb8f3e982e4";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$datas = $appSession->getTool()->split($data, ';');
	$sql = "";
	for($i=0; $i<count($datas); $i++)
	{
		$index = $appSession->getTool()->indexOf($datas[$i], '=');
		if($index != -1)
		{
			$line_id =$appSession->getTool()->subString($datas[$i], 0, $index);
			$value = $appSession->getTool()->subString($datas[$i],  $index + 1);
			if($sql != "")
			{
				$sql = $sql."; ";
			}
			$sql = $sql."UPDATE res_attribute_line SET description='".$appSession->getTool()->replace($value, "'", "''")."' WHERE id='".$line_id."'";
		}
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	$receipt_description = "";
	$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d2.name, d1.res_id, d1.status";
    $sql= $sql.", d3.name AS location_name, d4.version, d2.description";
    $sql= $sql." FROM res_receipt d1";
    $sql= $sql." LEFT OUTER JOIN product_classification d2 ON(d1.res_id = d2.id)";
    $sql= $sql." LEFT OUTER JOIN mrp_workcenter_line_flow d3 ON(d1.location_id = d3.id)";
	$sql = $sql." LEFT OUTER JOIN res_version d4 ON(d1.id = d4.rel_id)";
    $sql= $sql." WHERE d1.id ='".$id."'";
	
	$msg->add("query", $sql);
	
	$dt_receipt = $appSession->getTier()->getTable($msg);
	if($dt_receipt->getRowCount()>0)
	{
		$res_id = $dt_receipt->getString(0, "res_id");
		$version = $dt_receipt->getString(0, "version");
		$receipt_no = $dt_receipt->getString(0, "receipt_no");
		$receipt_description = $dt_receipt->getString(0, "description");
		if($version == "")
		{
			$version = "0";
		}
		$classification_name = $dt_receipt->getString(0, "name");
		$location_name = $dt_receipt->getString(0, "location_name");
		$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code, '' AS description, '' AS line_id";
		$sql= $sql." FROM res_attribute d1 LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
		$sql= $sql." LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
		$sql= $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
		$sql= $sql." WHERE d1.status =0 AND d1.rel_id='".$res_id."'";
		$sql= $sql." ORDER BY d1.sequence ASC";
		
		
		
		$msg->add("query", $sql);
		$dt_attribute = $appSession->getTier()->getTable($msg);
		
		$sql = "SELECT d1.id, d1.rel_id, d1.oporator, d1.parameter, d1.expression, d1.is_or FROM res_condition d1 LEFT OUTER JOIN res_attribute d2 ON(d1.rel_id = d2.id) WHERE d2.rel_id='".$res_id."' AND d1.status = 0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$dt_condition = $appSession->getTier()->getTable($msg);
	
	
		
		
		$sql = "SELECT d1.id, d1.rel_id, d1.type, d1.name, d1.description FROM res_status_notification d1 LEFT OUTER JOIN hr_employee d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN hr_line_department d3 ON(d1.status_id = d3.id) LEFT OUTER JOIN res_attribute d4 ON(d1.rel_id = d4.id) WHERE d4.rel_id='".$res_id."' AND d1.status =0 AND d4.status =0";
		$sql = $sql." ORDER BY d1.create_date ASC";
		
		$msg->add("query", $sql);
		$dt_notification = $appSession->getTier()->getTable($msg);
	
		
		
		$sql = "SELECT d1.id, d1.rel_id, d1.res_id, d2.name, d3.code, d3.first_name, d3.last_name, d3.middle_name, d1.unit_id FROM res_rel d1 LEFT OUTER JOIN hr_line_department d2 ON(d1.res_id = d2.id)  LEFT OUTER JOIN hr_employee d3 ON(d1.res_id = d3.id)  LEFT OUTER JOIN res_status_notification d4 ON(d1.rel_id = d4.id) LEFT OUTER JOIN res_attribute d5 ON(d4.rel_id = d5.id) WHERE d5.rel_id='".$res_id."'  AND d1.status =0 AND d5.status =0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$dt_resource = $appSession->getTier()->getTable($msg);
		$sql = "SELECT d1.id, d4.rel_id, d4.type, d4.description, d4.name AS notification_name, d1.res_id, d2.name, d3.code, d3.first_name, d3.last_name, d3.middle_name, d1.unit_id FROM res_rel d1 LEFT OUTER JOIN hr_line_department d2 ON(d1.res_id = d2.id)  LEFT OUTER JOIN hr_employee d3 ON(d1.res_id = d3.id)  LEFT OUTER JOIN res_status_notification d4 ON(d1.rel_id = d4.id) LEFT OUTER JOIN res_condition d5 ON(d4.rel_id = d5.id) LEFT OUTER JOIN res_attribute d6 ON(d5.rel_id = d6.id) WHERE d6.rel_id='".$res_id."'  AND d1.status =0 AND d5.status =0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$dt_condition_resource = $appSession->getTier()->getTable($msg);
		
		
		$sql = "SELECT d4.id AS classification_id, m.rel_id FROM res_rel m LEFT OUTER JOIN res_rel d1 ON(m.rel_id = d1.id) LEFT OUTER JOIN res_status_notification d4 ON(d1.rel_id = d4.id) LEFT OUTER JOIN res_attribute d5 ON(d4.rel_id = d5.id) WHERE d5.rel_id='".$res_id."'  AND d1.status =0 AND d5.status =0 AND m.status =0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$dt_sub_classification = $appSession->getTier()->getTable($msg);
	
		
		$sql = "SELECT d1.id, d1.attribute_id, d1.description, d2.code";
		$sql = $sql." FROM res_attribute_line d1 LEFT OUTER JOIN res_attribute d2 ON(d1.attribute_id = d2.id) WHERE d1.status =0 AND d1.rel_id='".$id."'";
		$msg->add("query", $sql);
		$dt_attribute_line = $appSession->getTier()->getTable($msg);
		
		
		$taskList = [];
		for($i =0; $i<$dt_attribute->getRowCount(); $i++)
		{
			for($j =0; $j<$dt_attribute_line->getRowCount(); $j++)
			{
				if($dt_attribute_line->getString($j, "attribute_id") == $dt_attribute->getString($i, "id"))
				{
					$value = $dt_attribute->getString($i, "value");
					if($appSession->getTool()->indexOf($value, "{") != -1)
					{
						
						for($n =0; $n<$dt_attribute_line->getRowCount(); $n++)
						{
							$description = $dt_attribute_line->getString($n, "description");
							$index = $appSession->getTool()->indexOf($description, '=');
							if($index != -1)
							{
								$description = $appSession->getTool()->subString($description, 0, $index);
							}
							if($dt_attribute->getString($i, "type_code") == "number" && $description== "")
							{
								$description = "0";
							}
							$description = trim($description);
							$value = $appSession->getTool()->replace($value, "{".$dt_attribute_line->getString($n, "code")."}", $description);
						}
						
						if($appSession->getTool()->indexOf($value, "{") == -1 && $value != "")
						{
							
							$description= $appSession->getTool()->eval($value);
							
							$dt_attribute_line->setString($description, $j, "description");
							$sql = "UPDATE res_attribute_line SET description='".$description."' WHERE id ='".$dt_attribute_line->getString($j, "id")."'";
							$msg->add("query", $sql);
							$r = $appSession->getTier()->exec($msg);
						}
						
					}
					
					$description = $dt_attribute_line->getString($j, "description");
					$index = $appSession->getTool()->indexOf($description, '=');
					if($index != -1)
					{
						$description = $appSession->getTool()->subString($description, 0, $index);
						
					}
					//if($dt_attribute->getString($i, "type_code") == "number" && $description== "")
					//{
					//	$description = "0";
					//}
					$description = trim($description);
					$dt_attribute->setString($description, $i, "description");
					$dt_attribute->setString($dt_attribute_line->getString($j, "id"), $i, "line_id");
					
					
					
					
				}
			}
			for($k =0; $k<$dt_attribute->getRowCount(); $k++)
			{
				if($dt_attribute->getString($k, "parent_id") == $dt_attribute->getString($i, "id"))
				{
					$value = $dt_attribute->getString($k, "value");
					for($j =0; $j<$dt_attribute_line->getRowCount(); $j++)
					{
						if($dt_attribute_line->getString($j, "attribute_id") == $dt_attribute->getString($i, "id"))
						{
							
							$description = $dt_attribute_line->getString($j, "description");
						
							$index = $appSession->getTool()->indexOf($description, '=');
							
							if($index != -1)
							{
								$description = $appSession->getTool()->subString($description, 0, $index);
							}
							
							if("[".$description == "[".$value && $value != "")
							{
								
								for($jj=0; $jj<$dt_notification->getRowCount(); $jj++)
								{
									if($dt_notification->getString($jj, "rel_id") == $dt_attribute->getString($k, "id"))
									{
										$name = $receipt_no.". ".$dt_notification->getString($jj, "name");
										$description = $dt_notification->getString($jj, "description");
										for($n =0; $n<$dt_attribute->getRowCount(); $n++)
										{
											$name = $appSession->getTool()->replace($name, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
											$description = $appSession->getTool()->replace($description, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
										}
										
										$resource = "";
										for($n =0; $n<$dt_resource->getRowCount(); $n++)
										{
											
											if($dt_resource->getString($n, "rel_id") == $dt_notification->getString($jj, "id"))
											{
												
												
												if($dt_resource->getString($n, "name") != "")
												{
													if($resource != "")
													{
														$resource = $resource.";";
													}
													$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "name").",hr_line_department";
												}
												if($dt_resource->getString($n, "code") != "")
												{
													if($resource != "")
													{
														$resource = $resource.";";
													}
													$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "code").". ".$dt_resource->getString($n, "first_name")." ".$dt_resource->getString($n, "last_name")." ".$dt_resource->getString($n, "middle_name").",hr_employee";
												}
												if( $dt_resource->getString($n, "unit_id") == "" && $dt_resource->getString($n, "unit_id") == $version){
													createSubReceipt($appSession, $id, $dt_resource->getString($n, "id"), $dt_sub_classification);
												}
											}
										}
										
										
										if($resource  != "")
										{
										
											$hasResource = false;
											for($t =0; $t<count($taskList); $t++)
											{
												if($taskList[$t][2] == $resource)
												{
													$taskList[$t][0] = $taskList[$t][0]."; ".$name;
													$taskList[$t][1] = $taskList[$t][1]."; ".$description;
													$hasResource = true;
												}
											}
											if($hasResource == false)
											{
												$taskList[count($taskList)] = [$name, $description, $resource];
											
											}
										}
										
									}
										
								}
							}
						}
					}
					
				}
			}
			
			
		}
		
		
		for($i =0; $i<$dt_attribute->getRowCount(); $i++)
		{
			
			$attribute_id = $dt_attribute->getString($i, "id");
			$line_id = $dt_attribute->getString($i, "line_id");
			$code =  $dt_attribute->getString($i, "code");
			
			
			
			$conditionList = [];
			for($j =0; $j<$dt_condition->getRowCount(); $j++)
			{
				if($dt_condition->getString($j, "rel_id") == $attribute_id)
				{
					
					if($dt_attribute->getString($i, "description") == "")
					{
						//continue;
					}
					$conditionList[count($conditionList)] = [$dt_condition->getString($j, "oporator"), $dt_condition->getString($j, "parameter"), $dt_condition->getString($j, "expression"), $dt_condition->getString($j, "is_or"), "", "", $dt_condition->getString($j, "id")];
					
				}
			}
			
			for($j =0; $j<count($conditionList); $j++)
			{
				$expression = $conditionList[$j][2];
				if($expression == "")
				{
					$expression = "{".$dt_attribute->getString($i, "code")."}";
				}
				for($n =0; $n<$dt_attribute->getRowCount(); $n++)
				{
					$description = $dt_attribute->getString($n, "description");
					$description = $appSession->getTool()->replace($description, ",", "");
					if($appSession->getTool()->isNumber($description))
					{
						$expression = $appSession->getTool()->replace($expression, "{".$dt_attribute->getString($n, "code")."}", $description);
					}else{
						$expression = $appSession->getTool()->replace($expression, "{".$dt_attribute->getString($n, "code")."}", "");
					}
					
				}
				
				$conditionList[$j][2] = $expression;
				
				if($expression != "")
				{
					$conditionList[$j][4] = $appSession->getTool()->eval($expression);
				}
				
				
			}
			
			
		
			if(count($conditionList)>0)
			{
				
				for($j =0; $j<count($conditionList); $j++)
				{
					
					if($conditionList[$j][0] == "=")
					{
						
						if($appSession->getTool()->toDouble($conditionList[$j][4]) == $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
						
					}else if($conditionList[$j][0] == ">=")
					{
						if($appSession->getTool()->toDouble($conditionList[$j][4]) >= $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
					}else if($conditionList[$j][0] == ">")
					{
						if($appSession->getTool()->toDouble($conditionList[$j][4]) > $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
					}else if($conditionList[$j][0] == "<=")
					{
					
						
						if($appSession->getTool()->toDouble($conditionList[$j][4])<= $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
						
						
					}else if($conditionList[$j][0] == "<")
					{
						
						if($appSession->getTool()->toDouble($conditionList[$j][4])< $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
					}else if($conditionList[$j][0] == "!=")
					{
						
						if($appSession->getTool()->toDouble($conditionList[$j][4]) != $appSession->getTool()->toDouble($conditionList[$j][1]))
						{
							$conditionList[$j][5] = "1";
						}else{
							$conditionList[$j][5] = "0";
						}
					}
					
					
					for($jj=0; $jj<$dt_condition_resource->getRowCount(); $jj++)
					{
						if($dt_condition_resource->getString($jj, "rel_id") == $conditionList[$j][6])
						{
							if(($conditionList[$j][5] == "0" && $dt_condition_resource->getString($jj, "type") == "false")|| ($conditionList[$j][5] == "1" && $dt_condition_resource->getString($jj, "type") == "true"))
							{
								
					
								$name = $receipt_no.". ".$dt_notification->getString($jj, "notification_name");
								$description = $dt_condition_resource->getString($jj, "description");
								$resource = "";
								if($dt_condition_resource->getString($jj, "name") != "")
								{
									
									$resource = $dt_condition_resource->getString($jj, "res_id").",".$dt_condition_resource->getString($jj, "name").",hr_line_department";
								}
								if($dt_condition_resource->getString($jj, "code") != "")
								{
								
									$resource = $dt_condition_resource->getString($jj, "res_id").",".$dt_condition_resource->getString($jj, "code").". ".$dt_condition_resource->getString($jj, "first_name")." ".$dt_condition_resource->getString($n, "last_name")." ".$dt_condition_resource->getString($jj, "middle_name").",hr_employee";
								}
								
								if($resource != "")
								{
									$hasResource = false;
									for($t =0; $t<count($taskList); $t++)
									{
										if($taskList[$t][2] == $resource)
										{
											if($taskList[$t][0] != "")
											{
												$taskList[$t][0] = $taskList[$t][0]." + ".$name;
											}else{
												$taskList[$t][0] = $name;
											}
											if($taskList[$t][1] != "")
											{
												$taskList[$t][1] = $taskList[$t][1]." + ".$description;
											}else{
												$taskList[$t][1] = $description;
											}
											
											$hasResource = true;
										}
									}
									if($hasResource == false)
									{
										$taskList[count($taskList)] = [$name, $description, $resource];
									}
								}
							}
						}
						
					}
					
					for($jj=0; $jj<$dt_notification->getRowCount(); $jj++)
					{
						if($dt_notification->getString($jj, "rel_id") == $conditionList[$j][6])
						{
							$name = $receipt_no.". ".$dt_notification->getString($jj, "name");
							$description = $dt_notification->getString($jj, "description");
							for($n =0; $n<$dt_attribute->getRowCount(); $n++)
							{
								$name = $appSession->getTool()->replace($name, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
								$description = $appSession->getTool()->replace($description, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
							}
							for($n =0; $n<count($conditionList); $n++)
							{
								$name = $appSession->getTool()->replace($name, "{operator".$n."}", $conditionList[$n][0]);
								$name = $appSession->getTool()->replace($name, "{value".$n."}", $conditionList[$n][1]);
								$name = $appSession->getTool()->replace($name, "{result".$n."}", $conditionList[$n][4]);
								
								$description = $appSession->getTool()->replace($description, "{operator".$n."}", $conditionList[$n][0]);
								$description = $appSession->getTool()->replace($description, "{value".$n."}", $conditionList[$n][1]);
								$description = $appSession->getTool()->replace($description, "{result".$n."}", $conditionList[$n][4]);
								
								$name = $appSession->getTool()->replace($name, "{operator}", $conditionList[$n][0]);
								$name = $appSession->getTool()->replace($name, "{value}", $conditionList[$n][1]);
								$name = $appSession->getTool()->replace($name, "{result}", $conditionList[$n][4]);
								
								$description = $appSession->getTool()->replace($description, "{operator}", $conditionList[$n][0]);
								$description = $appSession->getTool()->replace($description, "{value}", $conditionList[$n][1]);
								$description = $appSession->getTool()->replace($description, "{result}", $conditionList[$n][4]);
							}
							$resource = "";
							for($n =0; $n<$dt_resource->getRowCount(); $n++)
							{
								if($dt_resource->getString($n, "rel_id") == $dt_notification->getString($jj, "id") && ($dt_resource->getString($n, "unit_id") == "" || $dt_resource->getString($n, "unit_id") == $version))
								{
									if($dt_resource->getString($n, "name") != "")
									{
										if($resource != "")
										{
											$resource = $resource.";";
										}
										$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "name").",hr_line_department";
									}
									if($dt_resource->getString($n, "code") != "")
									{
										if($resource != "")
										{
											$resource = $resource.";";
										}
										$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "code").". ".$dt_resource->getString($n, "first_name")." ".$dt_resource->getString($n, "last_name")." ".$dt_resource->getString($n, "middle_name").",hr_employee";
									}
									createSubReceipt($appSession, $id, $dt_resource->getString($n, "id"), $dt_sub_classification);
								}
							}
							
							
							if($resource  != "")
							{
								if($dt_notification->getString($jj, "type") == "false" && $conditionList[$j][5] == "0")
								{
									$hasResource = false;
									for($t =0; $t<count($taskList); $t++)
									{
										if($taskList[$t][2] == $resource)
										{
											if($taskList[$t][0] != "")
											{
												$taskList[$t][0] = $taskList[$t][0]." + ".$name;
											}else{
												$taskList[$t][0] = $name;
											}
											if($taskList[$t][1] != "")
											{
												$taskList[$t][1] = $taskList[$t][1]." + ".$description;
											}else{
												$taskList[$t][1] = $description;
											}
											
											$hasResource = true;
										}
									}
									if($hasResource == false)
									{
										$taskList[count($taskList)] = [$name, $description, $resource];
									}
									
									
								}else if($dt_notification->getString($jj, "type") == "true" && $conditionList[$j][5] == "1")
								{
									$hasResource = false;
									for($t =0; $t<count($taskList); $t++)
									{
										if($taskList[$t][2] == $resource)
										{
											if($taskList[$t][0] != "")
											{
												$taskList[$t][0] = $taskList[$t][0]." + ".$name;
											}else{
												$taskList[$t][0] = $name;
											}
											if($taskList[$t][1] != "")
											{
												$taskList[$t][1] = $taskList[$t][1]." + ".$description;
											}else{
												$taskList[$t][1] = $description;
											}
											
											$hasResource = true;
										}
									}
									if($hasResource == false)
									{
										$taskList[count($taskList)] = [$name, $description, $resource];
									}
									
								}
							}
							
						}
							
					}
					
				}
				$result_result = "";
				$result_condition = "";
				$isTrue = false;
				for($j =0; $j<count($conditionList); $j++)
				{
					if($result_condition != "")
					{
						
							if($conditionList[$j][3] == "1")
							{
								$result_condition = $result_condition."\tOr\t";
							}else{
								$result_condition = $result_condition."\tAnd\t";
							}
						
						
					}
					$result_condition = $result_condition.$conditionList[$j][4]."\t".$conditionList[$j][0]."\t".$conditionList[$j][1];
					if($conditionList[$j][5] == "0"){
						$result_result = "0";
					}
					if($conditionList[$j][5] == "1"){
						$result_result = "1";
						$isTrue = true;
					}
				}
				if($appSession->getTool()->indexOf($result_condition, "And") != -1)
				{
					for($j =0; $j<count($conditionList); $j++)
					{
						if($conditionList[$j][5] == "0"){
							$result_result = "0";
						}
					}
				}
				if($appSession->getTool()->indexOf($result_condition, "Or") != -1)
				{
					for($j =0; $j<count($conditionList); $j++)
					{
						if($conditionList[$j][5] == "1"){
							$result_result = "1";
							$isTrue = true;
						}
					}
				}
				

				if($line_id != "")
				{
					$sql = "SELECT id FROM res_attribute_line_result WHERE line_id ='".$line_id."'";
					$msg->add("query", $sql);
					
					$result_id = $appSession->getTier()->getValue($msg);
					if($result_id == "")
					{
						$sql = "INSERT INTO res_attribute_line_result(id, create_date, write_date, status, company_id, condition, result, line_id)VALUES('".$appSession->getTool()->getId()."', NOW(), NOW(), 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$appSession->getTool()->replace($result_condition, "'", "''")."', '".$result_result."', '".$line_id."')";
						$msg->add("query", $sql);
						$r = $appSession->getTier()->exec($msg);
					}else{
						$sql = "UPDATE res_attribute_line_result SET status =0, condition='".$appSession->getTool()->replace($result_condition, "'", "''")."', result='". $result_result."' WHERE id ='".$result_id."'";
						$msg->add("query", $sql);
						
						$r = $appSession->getTier()->exec($msg);
					}
					
				}
				
				
				for($j=0; $j<$dt_notification->getRowCount(); $j++)
				{
					if($dt_notification->getString($j, "rel_id") == $attribute_id)
					{
						$name = $receipt_no.". ".$dt_notification->getString($j, "name");
						$description = $dt_notification->getString($j, "description");
						for($n =0; $n<$dt_attribute->getRowCount(); $n++)
						{
							$name = $appSession->getTool()->replace($name, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
							$description = $appSession->getTool()->replace($description, "{".$dt_attribute->getString($n, "code")."}", $dt_attribute->getString($n, "description"));
						}
						for($n =0; $n<count($conditionList); $n++)
						{
							$name = $appSession->getTool()->replace($name, "{operator".$n."}", $conditionList[$n][0]);
							$name = $appSession->getTool()->replace($name, "{value".$n."}", $conditionList[$n][1]);
							$name = $appSession->getTool()->replace($name, "{result".$n."}", $conditionList[$n][4]);
							
							$description = $appSession->getTool()->replace($description, "{operator".$n."}", $conditionList[$n][0]);
							$description = $appSession->getTool()->replace($description, "{value".$n."}", $conditionList[$n][1]);
							$description = $appSession->getTool()->replace($description, "{result".$n."}", $conditionList[$n][4]);
							
							$name = $appSession->getTool()->replace($name, "{operator}", $conditionList[$n][0]);
							$name = $appSession->getTool()->replace($name, "{value}", $conditionList[$n][1]);
							$name = $appSession->getTool()->replace($name, "{result}", $conditionList[$n][4]);
							
							$description = $appSession->getTool()->replace($description, "{operator}", $conditionList[$n][0]);
							$description = $appSession->getTool()->replace($description, "{value}", $conditionList[$n][1]);
							$description = $appSession->getTool()->replace($description, "{result}", $conditionList[$n][4]);
						}
						$resource = "";
						for($n =0; $n<$dt_resource->getRowCount(); $n++)
						{
							if($dt_resource->getString($n, "rel_id") == $dt_notification->getString($j, "id") && ($dt_resource->getString($n, "unit_id") == "" || $dt_resource->getString($n, "unit_id") == $version))
							{
								if($dt_resource->getString($n, "name") != "")
								{
									if($resource != "")
									{
										$resource = $resource.";";
									}
									$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "name").",hr_line_department";
								}
								if($dt_resource->getString($n, "code") != "")
								{
									if($resource != "")
									{
										$resource = $resource.";";
									}
									$resource = $resource.",".$dt_resource->getString($n, "res_id").",".$dt_resource->getString($n, "code").". ".$dt_resource->getString($n, "first_name")." ".$dt_resource->getString($n, "last_name")." ".$dt_resource->getString($n, "middle_name").",hr_employee";
								}
								createSubReceipt($appSession, $id, $dt_resource->getString($n, "id"), $dt_sub_classification);
							}
						}
						if($line_id != "")
						{
							$sql = "SELECT id FROM res_meta WHERE rel_id ='".$line_id."'";
							$msg->add("query", $sql);
							$result_id = $appSession->getTier()->getValue($msg);
							$result_type = "";
							if($dt_notification->getString($j, "type") == "false" && $isTrue == false)
							{
								$result_type = "0";
							}else if($dt_notification->getString($j, "type") == "true" && $isTrue == true)
							{
								$result_type = "0";
							}
							if($result_id == "")
							{
								
								$sql = "INSERT INTO res_meta(id, create_date, write_date, status, company_id, description, rel_id, create_uid)VALUES('".$appSession->getTool()->getId()."', NOW(), NOW(), 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$appSession->getTool()->replace($description, "'", "''")."', '".$line_id."', '".$result_type."')";
								$msg->add("query", $sql);
								
								$r = $appSession->getTier()->exec($msg);
							}else{
								$sql = "UPDATE res_meta SET status =0, description='".$appSession->getTool()->replace($description, "'", "''")."', create_uid='".$result_type."' WHERE id ='".$result_id."'";
								$msg->add("query", $sql);
								$r = $appSession->getTier()->exec($msg);
								
							}
						}
					
						if($resource  != "")
						{
							if($dt_notification->getString($j, "type") == "false" && $isTrue == false)
							{
								$hasResource = false;
								for($t =0; $t<count($taskList); $t++)
								{
									if($taskList[$t][2] == $resource)
									{
										if($taskList[$t][0] != "")
										{
											$taskList[$t][0] = $taskList[$t][0]." + ".$name;
										}else{
											$taskList[$t][0] = $name;
										}
										if($taskList[$t][1] != "")
										{
											$taskList[$t][1] = $taskList[$t][1]." + ".$description;
										}else{
											$taskList[$t][1] = $description;
										}
										
										$hasResource = true;
									}
								}
								if($hasResource == false)
								{
									$taskList[count($taskList)] = [$name, $description, $resource];
								}
								
								
							}else if($dt_notification->getString($j, "type") == "true" && $isTrue == true)
							{
								$hasResource = false;
								for($t =0; $t<count($taskList); $t++)
								{
									if($taskList[$t][2] == $resource)
									{
										if($taskList[$t][0] != "")
										{
											$taskList[$t][0] = $taskList[$t][0]." + ".$name;
										}else{
											$taskList[$t][0] = $name;
										}
										if($taskList[$t][1] != "")
										{
											$taskList[$t][1] = $taskList[$t][1]." + ".$description;
										}else{
											$taskList[$t][1] = $description;
										}
										
										$hasResource = true;
									}
								}
								if($hasResource == false)
								{
									$taskList[count($taskList)] = [$name, $description, $resource];
								}
								
							}
						}
						
					}
						
				}
			}
		}
		if($receipt_description != "")
		{
			for($n =0; $n<$dt_attribute_line->getRowCount(); $n++)
			{
				$description = $dt_attribute_line->getString($n, "description");
				$index = $appSession->getTool()->indexOf($description, '=');
				if($index != -1)
				{
					$description = $appSession->getTool()->subString($description, $index + 1);
				}
				
				$description = trim($description);
				$receipt_description = $appSession->getTool()->replace($receipt_description, "{".$dt_attribute_line->getString($n, "code")."}", $description);
			}
			
		}
		
		for($i =0; $i<count($taskList); $i++)
		{
			$name = $taskList[$i][0];
			if($name == "")
			{
				$name= $classification_name." from ".$location_name;
			}
			$response = addTask($appSession, $id, $module_id, $taskList[$i][2], $user_id, $name, $taskList[$i][1]);
		}
		
		$sql = "UPDATE res_receipt SET status =2, description = '".$appSession->getTool()->replace($receipt_description, "'", "''")."' WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		echo $id;
	}
	
}else if($ac == "renew")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$reset_data = "";
	if(isset($_REQUEST['reset_data']))
	{
		$reset_data = $_REQUEST['reset_data'];
	}
	
	$sql = "SELECT receipt_no, rel_id, res_id, company_id FROM  res_receipt WHERE id ='".$id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0)
	{
		$rel_id = $dt->getString(0, "rel_id");
		$res_id =  $dt->getString(0, "res_id");
		$receipt_no =  $dt->getString(0, "receipt_no");
		$company_id =  $dt->getString(0, "company_id");
		$new_id = $appSession->getTool()->getId();
		
		$msg->add("query", $sql);
		$sql = "SELECT id, attribute_id, description";
		$sql = $sql." FROM res_attribute_line WHERE status =0 AND rel_id='".$id."'";
		if($reset_data == "1")
		{
			$sql = $sql." AND 1=0";
		}
		$msg->add("query", $sql);
		$dt_attribute_line = $appSession->getTier()->getTable($msg);
		
		$sql = "SELECT d1.condition, d1.result, d1.line_id";
		$sql = $sql." FROM res_attribute_line_result d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.line_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$id."'";
		if($reset_data == "1")
		{
			$sql = $sql." AND 1=0";
		}
		$msg->add("query", $sql);
		$dt_attribute_line_result = $appSession->getTier()->getTable($msg);
		
		
		$sql = "SELECT version, rel_id FROM res_version WHERE rel_id='".$id."'";
		$msg->add("query", $sql);
		$version =  1;
		$version_rel_id = $id;
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			
			$version = $arr[0][0];
			$version = intval($version) + 1;
			$version_rel_id = $arr[0][1];
		}
		
		$builder = $appSession->getTier()->createBuilder("res_version");
		$builder->add("id", $new_id);
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $version_rel_id);
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("rel_id", $new_id);
		$builder->add("version", $version);
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
		
		
		$builder = $appSession->getTier()->createBuilder("res_receipt");
		$builder->add("id", $new_id);
		$builder->add("parent_id",  $id);
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid",  $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("rel_id", $rel_id);
		$builder->add("res_id", $res_id);
		$builder->add("company_id", $company_id);
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status", 0);
		$sql = $sql.";".$appSession->getTier()->getInsert($builder);
		
		for($i =0; $i<$dt_attribute_line->getRowCount(); $i++)
		{
			$line_id = $appSession->getTool()->getId();
			$builder = $appSession->getTier()->createBuilder("res_attribute_line");
			$builder->add("id", $line_id);
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("create_uid", $id);
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("rel_id", $new_id);
			$builder->add("attribute_id", $dt_attribute_line->getString($i, "attribute_id"));
			$builder->add("description", $dt_attribute_line->getString($i, "description"));
			$builder->add("status", 0);
			$sql = $sql.";".$appSession->getTier()->getInsert($builder);
			for($n =0; $n<$dt_attribute_line_result->getRowCount(); $n++)
			{
				if($dt_attribute_line->getString($i, "id") == $dt_attribute_line_result->getString($n, "line_id"))
				{
					$result_condition  = $dt_attribute_line_result->getString($n, "condition");
					$result_result  = $dt_attribute_line_result->getString($n, "result");
					$sql = $sql.";INSERT INTO res_attribute_line_result(id, create_date, write_date, status, company_id, condition, result, line_id)VALUES('".$appSession->getTool()->getId()."', NOW(), NOW(), 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$appSession->getTool()->replace($result_condition, "'", "''")."', '".$result_result."', '".$line_id."')";
					
				}
			}
		}
		$sql = $sql.";UPDATE res_receipt SET status =3, write_date=NOW() WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		echo $new_id;
		
	}
	
}else if($ac == "create")
{
	$receipt_id = '';
	if(isset($_REQUEST['receipt_id']))
	{
		$receipt_id = $_REQUEST['receipt_id'];
	}
	createAttribute($appSession, $receipt_id);
	echo "OK";
	
}else if($ac == "save")
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
	$module_id = "79b9c2fa-63a1-44aa-9deb-dfb8f3e982e4";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}

	$datas = $appSession->getTool()->split($data, ';');
	$sql = "";
	for($i=0; $i<count($datas); $i++)
	{
		$index = $appSession->getTool()->indexOf($datas[$i], '=');
		if($index != -1)
		{
			$line_id =$appSession->getTool()->subString($datas[$i], 0, $index);
			$value = $appSession->getTool()->subString($datas[$i],  $index + 1);
			if($sql != "")
			{
				$sql = $sql."; ";
			}
			$sql = $sql."UPDATE res_attribute_line SET description='".$appSession->getTool()->replace($value, "'", "''")."' WHERE id='".$line_id."'";
		
		}
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	echo "OK";
	
}
?>