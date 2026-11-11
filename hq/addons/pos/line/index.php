<?php
require_once(ABSPATH . 'api/Sale.php');
validUser($appSession);

$appSession->getLang()->load($appSession->getTier(), "SALE", $appSession->getConfig()->getProperty("lang_id"));

$msg = $appSession->getTier()->createMessage();


$sale_id = '';
if(isset($_REQUEST['sale_id']))
{
	$sale_id = $_REQUEST['sale_id'];
}
$category_id = '';
if(isset($_REQUEST['category_id']))
{
	$category_id = $_REQUEST['category_id'];
}
$sale = new Sale($appSession);
$sale->checkSaleService($sale_id);
$sql = "SELECT d1.id, d1.parent_id, d1.rel_id, d1.sale_id AS sale_id, d3.code AS product_code, d3.name AS product_name";
$sql = $sql.", d4.name AS unit_name, d5.name AS attriubte_name, d6.name AS type_name";
$sql = $sql.", d1.currency_id, d1.quantity, d1.unit_price, d1.factor, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.rel_id,d7.name category_name, d8.name parent_category_name, d1.description, d1.proccess";
$sql = $sql." FROM sale_product_local d1";
$sql = $sql." LEFT OUTER JOIN sale_local d2 ON(d1.sale_id = d2.id)";
$sql = $sql." LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id)";
$sql = $sql." LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id)";
$sql = $sql." LEFT OUTER JOIN attribute d5 ON(d1.attribute_id = d5.id)";
$sql = $sql." LEFT OUTER JOIN product_type d6 ON(d1.type_id = d6.id)";
$sql = $sql." LEFT OUTER JOIN product_category d7 ON(d3.category_id = d7.id)";
$sql = $sql." LEFT OUTER JOIN product_category d8 ON(d7.parent_id = d8.id)";
$sql = $sql." WHERE d1.status =0 AND d2.id='".$sale_id."' AND (d7.id='".$category_id."' OR d8.id='".$category_id."')";
$sql = $sql." ORDER BY d1.create_date ASC";


$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$price_rel_ids = "";
for($i=0; $i<$dt->getRowCount(); $i++)
{
	if($price_rel_ids != "")
	{
		$price_rel_ids = $price_rel_ids." OR ";
	}
	$price_rel_ids = $price_rel_ids." d1.rel_id='".$dt->getString($i, "rel_id")."'";
}
$sql = "SELECT d1.id, d1.rel_id, d2.name, d1.factor FROM attribute_line d1 LEFT OUTER JOIN product_type d2 ON(d1.type_id = d2.id) LeFT OUTER JOIN sale_product_local d3 ON(d1.rel_id= d3.id) WHERE d1.status =0 AND d3.sale_id = '".$sale_id."'" ;
$sql = $sql." ORDER BY d2.sequence, d2.name ASC";
$msg->add("query", $sql);
$dt_type = $appSession->getTier()->getTable($msg);

$sql = "SELECT d1.id, d1.rel_id, d3.name, d1.percent, d1.value, d1.operator FROM account_service_line_local d1 LEFT OUTER JOIN sale_product_local d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN account_service d3 ON(d1.service_id = d3.id) WHERE d2.sale_id='".$sale_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
$msg->add("query", $sql);
$dt_service = $appSession->getTier()->getTable($msg);

$company_currency_id = $appSession->getConfig()->getProperty("currency_id");

function printProduct($appSession, $dt, $dt_type, $parent_id, $pno, $dt_service, $category_id)
{
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "parent_id") != $parent_id)
		{
			continue;
		}
		$no = $no + 1;
		$id = $dt->getString($i, "id");
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$attriubte_name = $dt->getString($i, "attriubte_name");
		$type_name = $dt->getString($i, "type_name");
		$unit_name = $dt->getString($i, "unit_name");
		$currency_id = $dt->getString($i, "currency_id");
		$quantity = $dt->getFloat($i, "quantity");
		$unit_price = $dt->getFloat($i, "unit_price");
		for($j =0; $j<$dt_type->getRowCount(); $j++)
		{
			if($dt_type->getString($j, "rel_id") == $id)
			{
				if($dt_type->getString($j, "rel_id") == $id)
				{
					$f = $dt_type->getFloat($j, "factor");
					$unit_price = $unit_price * $f;
				}
			}
		}
		$factor = $dt->getFloat($i, "factor");
		$amount = $quantity * $unit_price * $factor;
		$category_name = $dt->getString($i, "category_name");
		$parent_category_name = $dt->getString($i, "parent_category_name");
		$type_id = $dt->getString($i, "type_id");
		$description = $dt->getString($i, "description");
		
		
		
		?>
		<tr>
			<td nowrap="nowrap"> <a href="javascript:productList('<?php echo $id;?>', '<?php echo $category_id;?>');"><?php if($pno != ""){echo $pno."."; }?><?php echo $no;?> <i class="icon-plus"></i></a> <td>
			<td style="text-align:right">
			<?php if($parent_id == ""){
			?>
			<input type="text" class="input" style="width:100px; text-align:right" value="<?php echo $quantity;?>" onblur="quantityChanged(this, '<?php echo $id;?>', <?php echo $quantity;?>, '<?php echo $category_id;?>')">
			<?php
			}else{
				?>
			<?php echo $quantity;?>
			<?php
			}
			?>
			<td>
			<td><a href="javascript:productListEdit('<?php echo $id;?>', '<?php echo $category_id;?>')"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></a><td>
			<td><?php echo $appSession->getFormats()->getPERCENT()->format($factor);?><td>
			<td>
			<?php echo $appSession->getCurrency()->format($currency_id, $amount);?>
			
			<td>
			<td><?php echo $product_code.". ".$product_name;?>
			<input type="text" class="input"  value="<?php echo $description;?>" onblur="saveLine('sale_product_local', 'description', '', '<?php echo $id;?>', this,function(status, message){})">
			<td>
			<td><?php echo $attriubte_name;?><td>
			<td>
			<?php echo $type_name;?><a href="javascript:showType('<?php echo $id;?>', '<?php echo  $dt->getString($i, "rel_id");?>')"><i class="icon-plus"></i></a>
			<?php
			for($j =0; $j<$dt_type->getRowCount(); $j++)
			{
				if($dt_type->getString($j, "rel_id") == $id)
				{
					
					$_id = $dt_type->getString($j, "id");
					$name = $dt_type->getString($j, "name");
					$f = $dt_type->getFloat($j, "factor");
				
			?>
			<br><?php echo $name;?> (<?php echo $appSession->getFormats()->getPERCENT()->format($f);?> <a href="javascript:delType('<?php echo $_id;?>')"><i class="icon-minus"></i></a>
			<?php
				}
			}
			?>
			<td>
			
			
			<td><a href="javascript:showService('<?php echo $id;?>', '<?php echo $category_id;?>')"><i class="icon-plus"></i></a>
			<?php
			for($j =0; $j<$dt_service->getRowCount(); $j++)
			{
				if($dt_service->getString($j, "rel_id") == $id)
				{
					
					$service_id = $dt_service->getString($j, "id");
					$service_name = $dt_service->getString($j, "name");
					$service_percent = $dt_service->getFloat($j, "percent");
					$service_value = $dt_service->getFloat($j, "value");
					$operator = $dt_service->getString($j, "operator");
					if($operator == "-")
					{
						$amount = $amount - (($amount * $service_percent) + $service_value);
					}else if($operator == "+")
					{
						$amount = $amount + (($amount * $service_percent) + $service_value);
					}else if($operator == "*")
					{
						$amount = $amount * (($amount * $service_percent) + $service_value);
					}else if($operator == "/")
					{
						$amount = $amount / (($amount * $service_percent) + $service_value);
					}
					
				?>
				<br><?php echo $service_name;?> (<?php echo $appSession->getFormats()->getPERCENT()->format($service_percent);?> <a href="javascript:delService('<?php echo $service_id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a>
				<?php
				}
			}
			?>
			<td>
			
		
			<td><a href="javascript:delProduct('<?php echo $id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a></td>
		</tr>
		
		<?php
		if($pno == "")
		{
			printProduct($appSession, $dt, $dt_type, $id, $no, $dt_service, $category_id);
		}else{
			printProduct($appSession, $dt, $dt_type , $id, $pno.".".$no, $dt_service, $category_id);
		}
	}
}	

?>
<div class="row">
	<div class="col m12 responsive" >
<table class="table">
	<?php
	if($category_id == "7e4bf7df-fe8c-4a96-f496-8916d12c99a9")
	{
		
	?>
	<tr>
		<td width="20"><b>STT</b></td>
		<td width="80" style="text-align:center"><b>Diện tích<br>(m<sup>2</sup>)</b></td>
		<td width="100" style="text-align:center"><b>Đơn giá<br>đồng/m2)</b></td>
		<td  style="text-align:center"><b>Tỉ lệ(%)</b></td>
		<td width="100" style="text-align:center"><b>Thành tiền<br>(đồng)</b></td>
		<td width="200" style="text-align:center"><b>Loại đất</b></td>
		<td></td>
	</tr>
	<?php
	$no = 0;
	$sql = "SELECT id, name FROM sale_category WHERE (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') AND status =0 ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$saleCategoryList = $appSession->getTier()->getArray($msg);
	$total = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$id = $dt->getString($i, "id");
		$currency_id = $dt->getString($i, "currency_id");
		$quantity = $dt->getFloat($i, "quantity");
		$unit_price = $dt->getFloat($i, "unit_price");
		$rel_id = $dt->getString($i, "rel_id");
		$amount = $quantity * $unit_price;
		$no = $no + 1;
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no + 1;?>.</td>
		<td style="vertical-align:middle"><input type="text" class="input" style="text-align:right" value="<?php echo $quantity;?>" onblur="quantityChanged(this, '<?php echo $id;?>', <?php echo $quantity;?>, '<?php echo $category_id;?>')"></td>
		<td style="text-align:right"><a href="javascript:productListEdit('<?php echo $id;?>', '<?php echo $category_id;?>')"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></a></td>
		<td style="text-align:left"><a href="javascript:showService('<?php echo $id;?>', '<?php echo $category_id;?>', 'DISCOUNT')"><i class="icon-plus"></i></a><?php
			for($j =0; $j<$dt_service->getRowCount(); $j++)
			{
				if($dt_service->getString($j, "rel_id") == $id)
				{
					
					$service_id = $dt_service->getString($j, "id");
					$service_name = $dt_service->getString($j, "name");
					$service_percent = $dt_service->getFloat($j, "percent");
					$service_value = $dt_service->getFloat($j, "value");
					$operator = $dt_service->getString($j, "operator");
					if($operator == "-")
					{
						$amount = $amount - (($amount * $service_percent) + $service_value);
					}else if($operator == "+")
					{
						$amount = $amount + (($amount * $service_percent) + $service_value);
					}else if($operator == "*")
					{
						$amount = $amount * (($amount * $service_percent) + $service_value);
					}else if($operator == "/")
					{
						$amount = $amount / (($amount * $service_percent) + $service_value);
					}
					
				?>
				<br><?php echo $appSession->getFormats()->getPERCENT()->format($service_percent);?> <a href="javascript:delService('<?php echo $service_id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a>
				<?php
				}
			}
			?></td>
		<td style="text-align:right; vertical-align:middle"><?php $total = $total + $amount; echo $appSession->getCurrency()->format($currency_id, $amount);?></td>
		<td><select class="select" onblur="saveLine('sale_product_local', 'rel_id', '', '<?php echo $id;?>', this,function(status, message){})">
		<option value=""></option>
		<?php
		for($j =0; $j<count($saleCategoryList); $j++)
		{
			$sale_category_id = $saleCategoryList[$j][0];
			$sale_category_name = $saleCategoryList[$j][1];
		?>
		<option value="<?php echo $sale_category_id;?>" <?php if($rel_id == $sale_category_id){ echo " selected "; }?>><?php echo $sale_category_name;?></option>
		<?php
		}
		?>
		</select></td>
		<td><a href="javascript:delProduct('<?php echo $id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td style="text-align:left; vertical-align:middle" colspan="4"><b>Tổng:</b></td>
		<td style="text-align:right; vertical-align:middle"><b><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></b></td>
		<td></td>
	</tr>
	<?php
	}else if($category_id == "0f47d0d8-8cba-4f3c-cf3a-3a467316d951")
	{
		
	?>
		<tr>
			<td width="200"><b>Mô tả hiện trạng</b></td>
			<td width="80" style="text-align:center"><b>Diện tích<br>(m<sup>2</sup>)</b></td>
			<td  width="100" style="text-align:center"><b>Đơn giá theo QĐ<br>đồng/m2)</b></td>
			<td  width="200" style="text-align:center"><b>Hệ số giá</b></td>
			<td  width="100" style="text-align:center"><b>Đơn giá<br>đồng/m2)</b></td>
			<td width="200" style="text-align:center"><b>Tỉ lệ(%)</b></td>
			<td width="100" style="text-align:center"><b>Thành tiền<br>(đồng)</b></td>
			<td></td>
		</tr>
		<?php
		$no = 0;
		$total = 0;
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$currency_id = $dt->getString($i, "currency_id");
			$description = $dt->getString($i, "description");
			$quantity = $dt->getFloat($i, "quantity");
			$unit_price = $dt->getFloat($i, "unit_price");
			$product_name = $dt->getString($i, "product_name");
			
			$no = $no + 1;
		?>
		<tr>
		<td style="vertical-align:middle"><input type="text" class="input" style="min-width:200px"  value="<?php echo $description;?>" onblur="saveLine('sale_product_local', 'description', '', '<?php echo $id;?>', this,function(status, message){})"><?php echo $product_name;?></td>
		<td style=""><input type="text" class="input" style="width:100px; text-align:right" value="<?php echo $quantity;?>" onblur="quantityChanged(this, '<?php echo $id;?>', <?php echo $quantity;?>, '<?php echo $category_id;?>')"></td>
		<td style=" text-align:right"><a href="javascript:productListEdit('<?php echo $id;?>', '<?php echo $category_id;?>')"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></a></td>
		<td style=""><a href="javascript:showType('<?php echo $id;?>', '<?php echo  $dt->getString($i, "rel_id");?>')"><i class="icon-plus"></i></a>
			<?php
			for($j =0; $j<$dt_type->getRowCount(); $j++)
			{
				if($dt_type->getString($j, "rel_id") == $id)
				{
					
					$_id = $dt_type->getString($j, "id");
					$name = $dt_type->getString($j, "name");
					$f = $dt_type->getFloat($j, "factor");
					$unit_price = $unit_price * $f;
				
			?>
			<br><?php echo $appSession->getFormats()->getPERCENT()->format($f);?> <a href="javascript:delType('<?php echo $_id;?>')"><i class="icon-minus"></i></a>
			<?php
				}
			}
			?>
		</td>
		<td style=" text-align:right"><a href="javascript:productListEdit('<?php echo $id;?>', '<?php echo $category_id;?>')"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></a></td>
		<?php
		$amount = $quantity * $unit_price
		?>
		<td style=""><a href="javascript:showService('<?php echo $id;?>', '<?php echo $category_id;?>', 'SERVICE')"><i class="icon-plus"></i></a><?php
			for($j =0; $j<$dt_service->getRowCount(); $j++)
			{
				if($dt_service->getString($j, "rel_id") == $id)
				{
					
					$service_id = $dt_service->getString($j, "id");
					$service_name = $dt_service->getString($j, "name");
					$service_percent = $dt_service->getFloat($j, "percent");
					$service_value = $dt_service->getFloat($j, "value");
					$operator = $dt_service->getString($j, "operator");
					if($operator == "-")
					{
						$amount = $amount - (($amount * $service_percent) + $service_value);
					}else if($operator == "+")
					{
						$amount = $amount + (($amount * $service_percent) + $service_value);
					}else if($operator == "*")
					{
						$amount = $amount * (($amount * $service_percent) + $service_value);
					}else if($operator == "/")
					{
						$amount = $amount / (($amount * $service_percent) + $service_value);
					}
					
				?>
				<br><?php echo $appSession->getFormats()->getPERCENT()->format($service_percent);?> <a href="javascript:delService('<?php echo $service_id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a>
				<?php
				}
			}
			?>
		</td>
		<td style="vertical-align:middle; text-align:right"><?php $total = $total + $amount; echo $appSession->getCurrency()->format($currency_id, $amount);?></td>
		<td><a href="javascript:delProduct('<?php echo $id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a></td>
	</tr>
	<?php
		}
		?>
	<tr>
		<td style="text-align:left; vertical-align:middle" colspan="6"><b>Tổng:</b></td>
		<td style="text-align:right; vertical-align:middle"><b><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></b></td>
		<td></td>
	</tr>
	<?php
	}
	else{
	?>
	<tr>
		<td width="20" style="text-align:center"><b>STT</b></td>
		<td style="text-align:center"><b>Tên</b></td>
		<td width="100" style="text-align:center"><b>Đơn vị tính</b></td>
		<td width="100" style="text-align:center"><b>Số lượng</b></td>
		<td width="100" style="text-align:center"><b>Đơn giá</b></td>
		<td width="100" style="text-align:center"><b>Thành tiền</b></td>
		<td width="80" style="text-align:center"><b>Không<br>áp dụng</b><td>
		<td></td>
	</tr>
	<?php
		$no = 0;
		$total = 0;
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$currency_id = $dt->getString($i, "currency_id");
			$product_name = $dt->getString($i, "product_name");
			$unit_name = $dt->getString($i, "unit_name");
			$quantity = $dt->getFloat($i, "quantity");
			$unit_price = $dt->getFloat($i, "unit_price");
			$proccess = $dt->getString($i, "proccess");
			$amount = $quantity * $unit_price;
			$total = $total + $amount;
			
			$no = $no + 1;
			
		?>
		<tr>
		<td><?php echo $no;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle"><input type="text" class="input" style="width:100px; text-align:right" value="<?php echo $quantity;?>" onblur="quantityChanged(this, '<?php echo $id;?>', <?php echo $quantity;?>, '<?php echo $category_id;?>')"></td>
		<td style="vertical-align:middle; text-align:right"><a href="javascript:productListEdit('<?php echo $id;?>', '<?php echo $category_id;?>')"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></a></td>
		<td style="vertical-align:middle; text-align:right"><?php $total = $total + $amount; echo $appSession->getCurrency()->format($currency_id, $amount);?></td>
		<td style="text-align:center; vertical-align:middle"><input type="checkbox" class="checkbox"  value="<?php echo $proccess;?>" <?php if($proccess == "1"){ echo " checked "; }?> onchange="if(this.checked){this.value=1}else {this.value=0;};saveLine('sale_product_local', 'proccess', '', '<?php echo $id;?>', this,function(status, message){})"></td>
		<td><a href="javascript:delProduct('<?php echo $id;?>', '<?php echo $category_id;?>')"><i class="icon-minus"></i></a></td>
		<?php
		}
		?>
		<tr>
		<td style="text-align:left; vertical-align:middle" colspan="5"><b>Tổng:</b></td>
		<td style="text-align:right; vertical-align:middle"><b><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></b></td>
		<td></td>
		<td></td>
	</tr>
	<?php
	}
	?>
	
</table>
	</div>
</div>
	


    
