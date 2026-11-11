<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "invoice_from_stock_work")
{
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
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$stock_product_id = "";
	if(isset($_REQUEST['stock_product_id']))
	{
		$stock_product_id = $_REQUEST['stock_product_id'];
	}
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d2.receipt_date FROM stock_product d1  LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) WHERE d1.status =0 AND d1.stock_id='".$rel_id."' AND d1.id='".$stock_product_id."'";

	$msg->add("query", $sql);
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	
	$receipt_date = "";
	if($dt_stock_product->getRowCount()>0){
		$receipt_date = $dt_stock_product->getString(0, "receipt_date");
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "SELECT code FROM account_invoice_category WHERE id='".$category_id."'";
		$msg->add("query", $sql);
		$category_code = $appSession->getTier()->getValue($msg);
		$year = date("y");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_invoice".$category_id.".".$year);
		$receipt_no = $appSession->getTool()->paddingLeft($receipt_no, '0', 4);
		$receipt_no = $category_code.".".$year.$receipt_no;
		$builder = $appSession->getTier()->createBuilder("account_invoice");
		$request_id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $stock_product_id);
		$builder->add("category_id", $category_id);
		$builder->add("location_id", $location_id);
		$builder->add("description", $description);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		if($receipt_date != "")
		{
			$builder->add("receipt_date", $receipt_date);
		}else{
			$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		}
		
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else{
		$sql = "UPDATE account_invoice SET receipt_date='".$receipt_date."' WHERE id ='".$id."'";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}
	
	
	
	$sql = "SELECT id FROM stock WHERE status =0 AND rel_id='".$rel_id."'";
	$msg->add("query", $sql);
	$values  = $appSession->getTier()->getArray($msg);
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id, 0.0 AS bom_quantity, 0.0 AS factor, 0.0 material_quantity FROM stock_product d1  LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) WHERE d1.status =0 AND d1.rel_id ='".$stock_product_id."' AND d2.type='WORKINREL'";
	$ids = "";
	for($i =0; $i<count($values); $i++)
	{
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." d1.stock_id='".$values[$i][0]."'";
	}
	if($ids != "")
	{
		$sql = $sql. " AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	
	$msg->add("query", $sql);
	
	

	$dt_stock_product_result = $appSession->getTier()->getTable($msg);
	
	$ids = "";
	for($i =0; $i<$dt_stock_product_result->getRowCount(); $i++)
	{
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." d2.product_id='".$dt_stock_product_result->getString($i, "product_id")."'";
	}
	
	$sql = "SELECT d4.product_id AS material_id, d4.unit_id, d4.attribute_id, d4.type_id, d4.quantity, d2.product_id FROM mrp_bom_rel d1 LEFT OUTER JOIN mrp_production d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN mrp_bom d3 ON(d1.bom_id = d3.id) LEFT OUTER JOIN mrp_bom_line d4 ON(d3.id = d4.bom_id) WHERE d1.status =0 AND d2.status =0 AND d3.status =0 AND d4.status =0";
	
	if($ids != "")
	{
		$sql = $sql. " AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$msg->add("query", $sql);
	$dt_bom = $appSession->getTier()->getTable($msg);
	
	
	
	
	$dt_material = $appSession->getTool()->selectDistinctTable($dt_stock_product_result, ["rel_id"]);
	for($n=0; $n<$dt_stock_product->getRowCount(); $n++)
	{
		$material_id = $dt_stock_product->getString($n, "product_id");
		$sql = "SELECT d2.product_id AS material_id, d5.product_id FROM mrp_production_product d1 LEFT OUTER JOIN mrp_production_product d2 ON(d1.rel_id = d2.rel_id) LEFT OUTER JOIN mrp_bom_line d3 ON(d2.product_id = d3.product_id) LEFT OUTER JOIN mrp_bom_rel d4 ON(d3.bom_id = d4.bom_id) LEFT OUTER JOIN mrp_production d5 ON(d4.rel_id = d5.id) WHERE d1.product_id ='".$material_id."'";
		$msg->add("query", $sql);
		
		
	
		$values = $appSession->getTier()->getArray($msg);
		
		for($i =0; $i<$dt_stock_product_result->getRowCount(); $i++)
		{
			if($dt_stock_product->getString($n, "id") == $dt_stock_product_result->getString($i, "rel_id"))
			{
				$hasBom = false;
				for($j =0; $j<$dt_bom->getRowCount(); $j++)
				{
					if($dt_stock_product->getString($n, "product_id") == $dt_bom->getString($j, "material_id") && $dt_stock_product_result->getString($i, "product_id") == $dt_bom->getString($j, "product_id"))
					{
						$hasBom = true;
					
						$dt_stock_product_result->setValue($dt_bom->getString($j, "quantity"), $i, "bom_quantity");
					}
				}
				if($hasBom == false)
				{
					
					for($jj =0; $jj<count($values); $jj++)
					{
						for($j =0; $j<$dt_bom->getRowCount(); $j++)
						{
							
							if($dt_bom->getString($j, "material_id") == $values[$jj][0] && $dt_bom->getString($j, "product_id") ==  $values[$jj][1] && $dt_stock_product_result->getString($i, "product_id") == $values[$jj][1])
							{
								
								$dt_stock_product_result->setValue($dt_bom->getString($j, "quantity"), $i, "bom_quantity");
							}
						}
					}
				}
			}
			
		}
	}
	for($i =0; $i<$dt_stock_product_result->getRowCount(); $i++)
	{
		$a = $dt_stock_product_result->getFloat($i, "quantity");
		$b = $dt_stock_product_result->getFloat($i, "bom_quantity");
		$c = $a * $b;
		
		$dt_stock_product_result->setValue($c, $i, "factor");
		//echo $dt_stock_product_result->getString($i, "id")." / ".$a." / ".$b . " / ".$c."<br>";
		
	}
	for($i=0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$a = $dt_stock_product->getFloat($i, "quantity");
		
		$stock_product_id  = $dt_stock_product->getString($i, "id");
		$b = 0;
		for($j  =0; $j<$dt_stock_product_result->getRowCount(); $j++)
		{
			if($dt_stock_product_result->getString($j, "rel_id") == $stock_product_id)
			{
				$b = $b + $dt_stock_product_result->getFloat($j, "factor");
			}
		}
		for($j  =0; $j<$dt_stock_product_result->getRowCount(); $j++)
		{
			if($dt_stock_product_result->getString($j, "rel_id") == $stock_product_id)
			{
				$c = $dt_stock_product_result->getFloat($j, "factor");
				if($b != 0 && $c !=0)
				{
					$c = $a/$b*$c;
					
					$dt_stock_product_result->setValue($c, $j, "material_quantity");
				}
				
			}
			
		}
	}

	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id FROM account_invoice_line d1 WHERE d1.status =0 AND invoice_id='".$id."'";
	$msg->add("query", $sql);
	$dt_invoice_line = $appSession->getTier()->getTable($msg);
	
	$builder = $appSession->getTier()->createBuilder("account_invoice_line");
	$sql = "";
	
	for($i =0; $i<$dt_stock_product_result->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_invoice_line->getRowCount(); $j++)
		{
			if($dt_invoice_line->getString($j, "rel_id") == $dt_stock_product_result->getString($i, "id"))
			{
				$hasItem = true;
				if($sql != "")
				{
					$sql = $sql."; ";
				}
				if($location_id == "7f944e7a-8a7a-4c8a-b355-5402935cff97" )
				{
					$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product_result->getFloat($i, "quantity")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
					
					
				}else{
					$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product_result->getFloat($i, "material_quantity")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
				}
				
				
			}
		}
		if($hasItem == false)
		{
			$quantity = $dt_stock_product_result->getString($i, "material_quantity");
			if($location_id == "7f944e7a-8a7a-4c8a-b355-5402935cff97")
			{
				$quantity = $dt_stock_product_result->getString($i, "quantity");
			}
			if($quantity == 0 && $location_id =="62e57171-059f-4e52-a4cc-429eabde2b99")
			{
				$quantity = $dt_stock_product_result->getString($i, "quantity");
			}
			
			if($quantity>0){
				$builder->clear();
				$builder->setName("account_invoice_line");
				$builder->add("id", $appSession->getTool()->getId());
				$builder->add("invoice_id", $id);
				$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("product_id", $dt_stock_product_result->getString($i, "product_id"));
				$builder->add("unit_id", $dt_stock_product_result->getString($i, "unit_id"));
				$builder->add("attribute_id", $dt_stock_product_result->getString($i, "attribute_id"));
				$builder->add("type_id", $dt_stock_product_result->getString($i, "type_id"));
				$builder->add("quantity", $quantity);
				$builder->add("rel_id", $dt_stock_product_result->getString($i, "id"));
				$builder->add("status", 0);
				$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
				if($sql != "")
				{
					$sql = $sql."; ";
				}
				$sql = $sql.$appSession->getTier()->getInsert($builder);
			}
		}
		
		
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
	}
	
	echo $id;
	
}else if($ac == "invoice_from_sale")
{
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
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.unit_price, d1.currency_id FROM sale_product_local d1 WHERE d1.status =0 AND d1.sale_id='".$rel_id."'";
	$msg->add("query", $sql);
	
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	$amount = 0;
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$quantity = $dt_stock_product->getFloat($i, "quantity");
		$unit_price = $dt_stock_product->getFloat($i, "unit_price");
		$amount = $amount + ($quantity * $unit_price);
	
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "SELECT code FROM account_invoice_category WHERE id='".$category_id."'";
		$msg->add("query", $sql);
		$category_code = $appSession->getTier()->getValue($msg);
		$year = date("y");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_invoice".$category_id.".".$year);
		$receipt_no = $appSession->getTool()->paddingLeft($receipt_no, '0', 4);
		$receipt_no = $category_code.".".$year.$receipt_no;
		$builder = $appSession->getTier()->createBuilder("account_invoice");
		$request_id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $rel_id);
		$builder->add("category_id", $category_id);
		$builder->add("location_id", $location_id);
		$builder->add("description", $description);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status", 0);
		$builder->add("amount", $amount);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else{
		$sql = "UPDATE account_invoice SET amount = ".$amount." WHERE id ='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id FROM account_invoice_line d1 WHERE d1.status =0 AND invoice_id='".$id."'";
	$msg->add("query", $sql);
	$dt_invoice_line = $appSession->getTier()->getTable($msg);

	$builder = $appSession->getTier()->createBuilder("account_invoice_line");
	$sql = "";
	
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_invoice_line->getRowCount(); $j++)
		{
			if($dt_invoice_line->getString($j, "rel_id") == $dt_stock_product->getString($i, "id"))
			{
				$hasItem = true;
				if($sql != "")
				{
					$sql = $sql."; ";
				}
			
				$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product->getFloat($i, "quantity").", unit_price=".$dt_stock_product->getFloat($i, "unit_price")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
				
			}
		}
		if($hasItem == true)
		{
			continue;
		}
		$builder->clear();
		$builder->setName("account_invoice_line");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("invoice_id", $id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("currency_id", $dt_stock_product->getString($i, "currency_id"));
		$builder->add("product_id", $dt_stock_product->getString($i, "product_id"));
		$builder->add("unit_id", $dt_stock_product->getString($i, "unit_id"));
		$builder->add("attribute_id", $dt_stock_product->getString($i, "attribute_id"));
		$builder->add("type_id", $dt_stock_product->getString($i, "type_id"));
		$builder->add("quantity", $dt_stock_product->getString($i, "quantity"));
		$builder->add("unit_price", $dt_stock_product->getString($i, "unit_price"));
		$builder->add("rel_id", $dt_stock_product->getString($i, "id"));
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		if($sql != "")
		{
			$sql = $sql."; ";
		}
		$sql = $sql.$appSession->getTier()->getInsert($builder);
	
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
	}
	echo $id;
	
}else if($ac == "invoice_from_purchase_order")
{
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
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.unit_price, d1.currency_id FROM purchase_order_product d1 WHERE d1.status =0 AND d1.order_id='".$rel_id."'";
	$msg->add("query", $sql);
	
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	$amount = 0;
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$quantity = $dt_stock_product->getFloat($i, "quantity");
		$unit_price = $dt_stock_product->getFloat($i, "unit_price");
		$amount = $amount + ($quantity * $unit_price);
	
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "SELECT code FROM account_invoice_category WHERE id='".$category_id."'";
		$msg->add("query", $sql);
		$category_code = $appSession->getTier()->getValue($msg);
		$year = date("y");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_invoice".$category_id.".".$year);
		$receipt_no = $appSession->getTool()->paddingLeft($receipt_no, '0', 4);
		$receipt_no = $category_code.".".$year.$receipt_no;
		$builder = $appSession->getTier()->createBuilder("account_invoice");
		$request_id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $rel_id);
		$builder->add("category_id", $category_id);
		$builder->add("location_id", $location_id);
		$builder->add("description", $description);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status", 0);
		$builder->add("amount", $amount);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else{
		$sql = "UPDATE account_invoice SET amount = ".$amount." WHERE id ='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id FROM account_invoice_line d1 WHERE d1.status =0 AND invoice_id='".$id."'";
	$msg->add("query", $sql);
	$dt_invoice_line = $appSession->getTier()->getTable($msg);

	$builder = $appSession->getTier()->createBuilder("account_invoice_line");
	$sql = "";
	
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_invoice_line->getRowCount(); $j++)
		{
			if($dt_invoice_line->getString($j, "rel_id") == $dt_stock_product->getString($i, "id"))
			{
				$hasItem = true;
				if($sql != "")
				{
					$sql = $sql."; ";
				}
			
				$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product->getFloat($i, "quantity").", unit_price=".$dt_stock_product->getFloat($i, "unit_price")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
				
			}
		}
		if($hasItem == true)
		{
			continue;
		}
		$builder->clear();
		$builder->setName("account_invoice_line");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("invoice_id", $id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("currency_id", $dt_stock_product->getString($i, "currency_id"));
		$builder->add("product_id", $dt_stock_product->getString($i, "product_id"));
		$builder->add("unit_id", $dt_stock_product->getString($i, "unit_id"));
		$builder->add("attribute_id", $dt_stock_product->getString($i, "attribute_id"));
		$builder->add("type_id", $dt_stock_product->getString($i, "type_id"));
		$builder->add("quantity", $dt_stock_product->getString($i, "quantity"));
		$builder->add("unit_price", $dt_stock_product->getString($i, "unit_price"));
		$builder->add("rel_id", $dt_stock_product->getString($i, "id"));
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		if($sql != "")
		{
			$sql = $sql."; ";
		}
		$sql = $sql.$appSession->getTier()->getInsert($builder);
	
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
	}
	echo $id;
}else if($ac == "invoice_from_purchase_receive")
{
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
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.unit_price, d1.currency_id FROM purchase_receive_product_local d1 WHERE d1.status =0 AND d1.receive_id='".$rel_id."'";
	$msg->add("query", $sql);
	
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	$amount = 0;
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$quantity = $dt_stock_product->getFloat($i, "quantity");
		$unit_price = $dt_stock_product->getFloat($i, "unit_price");
		$amount = $amount + ($quantity * $unit_price);
	
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "SELECT code FROM account_invoice_category WHERE id='".$category_id."'";
		$msg->add("query", $sql);
		$category_code = $appSession->getTier()->getValue($msg);
		$year = date("y");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_invoice".$category_id.".".$year);
		$receipt_no = $appSession->getTool()->paddingLeft($receipt_no, '0', 4);
		$receipt_no = $category_code.".".$year.$receipt_no;
		$builder = $appSession->getTier()->createBuilder("account_invoice");
		$request_id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $rel_id);
		$builder->add("category_id", $category_id);
		$builder->add("location_id", $location_id);
		$builder->add("description", $description);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status", 0);
		$builder->add("amount", $amount);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else{
		$sql = "UPDATE account_invoice SET amount = ".$amount." WHERE id ='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id FROM account_invoice_line d1 WHERE d1.status =0 AND invoice_id='".$id."'";
	$msg->add("query", $sql);
	$dt_invoice_line = $appSession->getTier()->getTable($msg);

	$builder = $appSession->getTier()->createBuilder("account_invoice_line");
	$sql = "";
	
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_invoice_line->getRowCount(); $j++)
		{
			if($dt_invoice_line->getString($j, "rel_id") == $dt_stock_product->getString($i, "id"))
			{
				$hasItem = true;
				if($sql != "")
				{
					$sql = $sql."; ";
				}
			
				$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product->getFloat($i, "quantity").", unit_price=".$dt_stock_product->getFloat($i, "unit_price")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
				
			}
		}
		if($hasItem == true)
		{
			continue;
		}
		$builder->clear();
		$builder->setName("account_invoice_line");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("invoice_id", $id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("currency_id", $dt_stock_product->getString($i, "currency_id"));
		$builder->add("product_id", $dt_stock_product->getString($i, "product_id"));
		$builder->add("unit_id", $dt_stock_product->getString($i, "unit_id"));
		$builder->add("attribute_id", $dt_stock_product->getString($i, "attribute_id"));
		$builder->add("type_id", $dt_stock_product->getString($i, "type_id"));
		$builder->add("quantity", $dt_stock_product->getString($i, "quantity"));
		$builder->add("unit_price", $dt_stock_product->getString($i, "unit_price"));
		$builder->add("rel_id", $dt_stock_product->getString($i, "id"));
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		if($sql != "")
		{
			$sql = $sql."; ";
		}
		$sql = $sql.$appSession->getTier()->getInsert($builder);
	
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
	}
	echo $id;
}else if($ac == "invoice_stock_out" || $ac == "invoice_stock_in")
{
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
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$location_id = '';
	if(isset($_REQUEST['location_id']))
	{
		$location_id = $_REQUEST['location_id'];
	}
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.unit_price, d1.currency_id, d2.receipt_date FROM stock_product d1 LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) WHERE d1.status =0 AND d1.stock_id='".$rel_id."'";
	$msg->add("query", $sql);
	
	
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	$amount = 0;
	$receipt_date = "";

	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$quantity = $dt_stock_product->getFloat($i, "quantity");
		$unit_price = $dt_stock_product->getFloat($i, "unit_price");
		$amount = $amount + ($quantity * $unit_price);
		$receipt_date = $dt_stock_product->getString($i, "receipt_date");
	
	}
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "SELECT code FROM account_invoice_category WHERE id='".$category_id."'";
		$msg->add("query", $sql);
		$category_code = $appSession->getTier()->getValue($msg);
		$year = date("y");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_invoice".$category_id.".".$year);
		$receipt_no = $appSession->getTool()->paddingLeft($receipt_no, '0', 4);
		$receipt_no = $category_code.".".$year.$receipt_no;
		$builder = $appSession->getTier()->createBuilder("account_invoice");
		$request_id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $rel_id);
		$builder->add("category_id", $category_id);
		$builder->add("location_id", $location_id);
		$builder->add("description", $description);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		if($receipt_date != "")
		{
			$builder->add("receipt_date", $receipt_date);
		}else{
			$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		}
		
		$builder->add("status", 0);
		$builder->add("amount", $amount);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else{
		$sql = "UPDATE account_invoice SET amount = ".$amount;
		$sql = $sql.", receipt_date='".$receipt_date."'";
		$sql = $sql." WHERE id ='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity, d1.rel_id FROM account_invoice_line d1 WHERE d1.status =0 AND invoice_id='".$id."'";
	$msg->add("query", $sql);
	$dt_invoice_line = $appSession->getTier()->getTable($msg);

	$builder = $appSession->getTier()->createBuilder("account_invoice_line");
	$sql = "";
	
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_invoice_line->getRowCount(); $j++)
		{
			if($dt_invoice_line->getString($j, "rel_id") == $dt_stock_product->getString($i, "id"))
			{
				$hasItem = true;
				if($sql != "")
				{
					$sql = $sql."; ";
				}
			
				$sql = $sql."UPDATE account_invoice_line SET quantity=".$dt_stock_product->getFloat($i, "quantity").", unit_price=".$dt_stock_product->getFloat($i, "unit_price")." WHERE id ='".$dt_invoice_line->getString($j, "id")."'";
				
			}
		}
		if($hasItem == true)
		{
			continue;
		}
		$builder->clear();
		$builder->setName("account_invoice_line");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("invoice_id", $id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("currency_id", $dt_stock_product->getString($i, "currency_id"));
		$builder->add("product_id", $dt_stock_product->getString($i, "product_id"));
		$builder->add("unit_id", $dt_stock_product->getString($i, "unit_id"));
		$builder->add("attribute_id", $dt_stock_product->getString($i, "attribute_id"));
		$builder->add("type_id", $dt_stock_product->getString($i, "type_id"));
		$builder->add("quantity", $dt_stock_product->getString($i, "quantity"));
		$builder->add("unit_price", $dt_stock_product->getString($i, "unit_price"));
		$builder->add("rel_id", $dt_stock_product->getString($i, "id"));
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		if($sql != "")
		{
			$sql = $sql."; ";
		}
		$sql = $sql.$appSession->getTier()->getInsert($builder);
	
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
	}
	echo $id;
}else if($ac == "payment")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d2.name, d1.amount, d3.code, d1.currency_id, d3.code AS currency_code FROM account_payment_line d1";
	$sql = $sql." LEFT OUTER JOIN account_payment d2 ON(d1.payment_id= d2.id) LEFT OUTER JOIN res_currency d3 ON(d1.currency_id = d3.id)";
	$sql = $sql." WHERE d1.rel_id='".$rel_id."' AND d1.status=0 AND d2.status =0";
	$sql = $sql." ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "add_payment_wallet")
{
	$holder_id = "";
	if(isset($_REQUEST['holder_id']))
	{
		$holder_id = $_REQUEST['holder_id'];
	}

	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$customer_id = "";
	if(isset($_REQUEST['customer_id']))
	{
		$customer_id = $_REQUEST['customer_id'];
	}
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$create_uid = "";
	if(isset($_REQUEST['create_uid']))
	{
		$create_uid = $_REQUEST['create_uid'];
	}
	$currency_id = "";
	if(isset($_REQUEST['currency_id']))
	{
		$currency_id = $_REQUEST['currency_id'];
	}
	$amount = "0";
	if(isset($_REQUEST['amount']))
	{
		$amount = $_REQUEST['amount'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "", "account_payment_line");
	$payment_id = $appSession->getTool()->getId();
	$builder = $appSession->getTier()->createBuilder("account_payment_line");
	$builder->add("id", $payment_id);
	$builder->add("rel_id", $rel_id);
	$builder->add("line_id", $rel_id);
	$builder->add("create_uid", $appSession->getUserInfo()->getId());
	$builder->add("write_uid", $appSession->getUserInfo()->getId());
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$builder->add("status", 0);
	$builder->add("payment_id", "9a9404e2-30d9-45c6-c3f6-43930e2d5a6c");
	$builder->add("currency_id", $currency_id);
	$builder->add("receipt_no", $receipt_no);
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("amount", $amount);
	$builder->add("description", $description);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	
	
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "", "wallet");
	$wallet_id = $appSession->getTool()->getId();
	$builder = $appSession->getTier()->createBuilder("wallet");
	$builder->add("id", $wallet_id);
	$builder->add("holder_id", $holder_id);
	$builder->add("create_uid", $appSession->getUserInfo()->getId());
	$builder->add("write_uid", $appSession->getUserInfo()->getId());
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("category_id", "eaf4f9aa-42de-4d19-b957-b3d24580d39d");
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$builder->add("status", 0);
	$builder->add("customer_id", $customer_id);
	$builder->add("currency_id", $currency_id);
	$builder->add("receipt_no", $receipt_no);
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("factor", -1);
	$builder->add("amount", $amount);
	$builder->add("description", $description);
	$builder->add("payment_id", $payment_id);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	
	$sql = "SELECT id FROM wallet_holder WHERE status =0 AND rel_id ='".$create_uid."'";
	
	$msg->add("query", $sql);
	$holder_id =$appSession->getTier()->getValue($msg);
	echo $holder_id;
	if($holder_id != "")
	{
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "", "wallet");
		$wallet_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("wallet");
		$builder->add("id", $wallet_id);
		$builder->add("holder_id", $holder_id);
		$builder->add("create_uid", $appSession->getUserInfo()->getId());
		$builder->add("write_uid", $appSession->getUserInfo()->getId());
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("category_id", "eaf4f9aa-42de-4d19-b957-b3d24580d39d");
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$builder->add("status", 0);
		$builder->add("customer_id", $user_id);
		$builder->add("currency_id", $currency_id);
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("factor", 1);
		$builder->add("amount", $amount);
		$builder->add("description", $description);
		$builder->add("payment_id", $payment_id);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
	echo $payment_id;
	
	
}else if($ac == "removePayment")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE wallet SET status = 1, write_date=NOW() WHERE payment_id='".$id."'";
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
				
	$sql = "UPDATE account_payment_line SET status = 1, write_date=NOW() WHERE id='".$id."'";

	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}

?>