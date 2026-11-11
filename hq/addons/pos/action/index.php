<?php
validUser($appSession);
require_once(ABSPATH.'api/Sale.php' );
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "addProduct")
{
	$sale_id = '';
	if(isset($_REQUEST['sale_id']))
	{
		$sale_id = $_REQUEST['sale_id'];
	}
	$price_id = '';
	if(isset($_REQUEST['price_id']))
	{
		$price_id = $_REQUEST['price_id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	
	$quantity = '';
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	
	if($parent_id != "")
	{
		$sql = "SELECT quantity FROM sale_product_local WHERE id='".$parent_id."'";
		$msg->add("query", $sql);
		$quantity = $appSession->getTier()->getValue($msg);
	}
	if($quantity == "")
	{
		$quantity = 1;
	}
	$sql = "SELECT d1.product_id, d1.currency_id, d2.unit_id, d1.attribute_id, d1.type_id, d1.unit_price, d1.factor FROM product_price d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) WHERE d1.id='".$price_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values)>0)
	{
		$product_id = $values[0][0];
		$currency_id = $values[0][1];
		$unit_id = $values[0][2];
		$attribute_id = $values[0][3];
		$type_id = $values[0][4];
		$unit_price = $values[0][5];
		$factor = $values[0][6];
		if($factor == "")
		{
			$factor = 1;
		}
		$builder = $appSession->getTier()->createBuilder("sale_product_local");
		$sale_product_id = $appSession->getTool()->getId();
		$builder->add("id", $sale_product_id);
		$builder->add("sale_id", $sale_id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("product_id", $product_id);
		$builder->add("unit_id", $unit_id);
		$builder->add("attribute_id", $attribute_id);
		$builder->add("currency_id", $currency_id);
		$builder->add("quantity", $quantity);
		$builder->add("unit_price", $unit_price);
		$builder->add("factor", $factor);
		$builder->add("type_id", $type_id);
		$builder->add("rel_id", $price_id);
		$builder->add("parent_id", $parent_id);
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
		$sql = "SELECT type_id, factor FROM product_price WHERE type='PRODUCT' AND type_id != '' AND factor != 0 AND product_id='".$product_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		for($j =0; $j<count($values); $j++)
		{
			$builder = $appSession->getTier()->createBuilder("attribute_line");
			$builder->add("id", $appSession->getTool()->getId());
			$builder->add("rel_id", $sale_product_id);
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("type_id", $values[$j][0]);
			$builder->add("factor", $values[$j][1]);
			$builder->add("status", 0);
			$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
			$sql = $appSession->getTier()->getInsert($builder);
			
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
			
		}
		
		echo $sale_product_id ;
	}
}else if($ac == "updateProduct")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$price_id = '';
	if(isset($_REQUEST['price_id']))
	{
		$price_id = $_REQUEST['price_id'];
	}
	
	$sql = "SELECT d1.product_id, d1.currency_id, d2.unit_id, d1.attribute_id, d1.type_id, d1.unit_price, d1.factor FROM product_price d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) WHERE d1.id='".$price_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values)>0)
	{
		$product_id = $values[0][0];
		$currency_id = $values[0][1];
		$unit_id = $values[0][2];
		$attribute_id = $values[0][3];
		$type_id = $values[0][4];
		$unit_price = $values[0][5];
		$factor = $values[0][6];
		if($factor == "")
		{
			$factor = 1;
		}
		
		$sql = "UPDATE sale_product_local SET rel_id='".$price_id."', product_id='".$product_id."'";
		$sql = $sql.", currency_id='".$currency_id."'";
		$sql = $sql.", attribute_id='".$attribute_id."'";
		$sql = $sql.", type_id='".$type_id."'";
		$sql = $sql.", unit_price=".$unit_price;
		$sql = $sql.", factor=".$factor;
		$sql = $sql." WHERE id='".$id."'";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		echo "OK";
		
	}
}else if($ac == "updateProductQuantity")
{
	$quantity = '';
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	function updateChildQuantity($appSession, $parent_id, $quantity)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT id FROM sale_product_local WHERE parent_id='".$parent_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($values); $i++)
		{
			$id = $values[$i][0];
			$sql = "UPDATE sale_product_local SET quantity=".$quantity.", write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			updateChildQuantity($appSession, $id, $quantity);
		}
	}
	$sql = "UPDATE sale_product_local SET quantity=".$quantity.", write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	updateChildQuantity($appSession, $id, $quantity);
	echo "OK";
}else if($ac == "delProduct")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	function delChildQuantity($appSession, $parent_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT id FROM sale_product_local WHERE parent_id='".$parent_id."' AND status =0";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($values); $i++)
		{
			$id = $values[$i][0];
			$sql = "UPDATE sale_product_local SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			delChildQuantity($appSession, $id);
		}
	}
	$sql = "UPDATE sale_product_local SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	delChildQuantity($appSession, $id);
	echo "OK";
}else if($ac == "typeChanged")
{
	$price_id = '';
	if(isset($_REQUEST['price_id']))
	{
		$price_id = $_REQUEST['price_id'];
	}
	$sale_product_id = '';
	if(isset($_REQUEST['sale_product_id']))
	{
		$sale_product_id = $_REQUEST['sale_product_id'];
	}
	$sql = "SELECT type_id, factor FROM product_price WHERE id='".$price_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$type_id = "";
	$factor = 0;
	if(count($values)>0)
	{
		$type_id = $values[0][0];
		$factor = $values[0][1];
	}
	if($factor == "")
	{
		$factor = 1;
	}
	$sql = "UPDATE sale_product_local SET factor=".$factor.", type_id='".$type_id."', write_date=".$appSession->getTier()->getDateString()." WHERE id='".$sale_product_id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}


?>