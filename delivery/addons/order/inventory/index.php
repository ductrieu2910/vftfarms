<?php
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$sale_product_id = "";
if(isset($_REQUEST['sale_product_id']))
{
	$sale_product_id = $_REQUEST['sale_product_id'];
}
$sale_id = "";
if(isset($_REQUEST['sale_id']))
{
	$sale_id = $_REQUEST['sale_id'];
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

$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.quantity , d2.order_no FROM sale_product_local d1 LEFT OUTER JOIN sale_local d2 ON(d1.sale_id = d2.id) where d1.id ='".$sale_product_id."'";
$msg->add("query", $sql);
$values = $appSession->getTier()->getArray($msg);
if(count($values)>0)
{
	$product_id = $values[0][0];
	$unit_id = $values[0][1];
	$attribute_id = $values[0][2];
	$type_id = $values[0][3];
	$sale_quantity =  $appSession->getTool()->toDouble($values[0][4]);
	$order_no = $values[0][5];
	
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$quantity = "";
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	if($company_id != "" && $quantity != "")
	{
		$sql = "SELECT id FROM purchase_request WHERE rel_id='".$sale_id."' AND company_id='".$company_id."'";
		$msg->add("query", $sql);
		$request_id = $appSession->getTier()->getValue($msg);
		$user_id = $appSession->getConfig()->getProperty("user_id");
		if($request_id == "")
		{
			$request_id = $appSession->getTool()->getId();
			$sql = "SELECT code FROM res_company WHERE id ='".$company_id."'";
			$msg->add("query", $sql);
			$receipt_no = $order_no.".".$appSession->getTier()->getValue($msg);
			$builder = $appSession->getTier()->createBuilder("purchase_request");
			$builder->add("id", $request_id);
			$builder->add("create_uid", $user_id);
			$builder->add("write_uid", $user_id);
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("receipt_no", $receipt_no);
			$builder->add("status", 0);
			$builder->add("company_id", $company_id);
			$builder->add("rel_id", $sale_id);
			$sql = $appSession->getTier()->getInsert($builder);
			
			$msg->add("query", $sql);
			
			$appSession->getTier()->exec($msg);
		}else{
			$sql = "UPDATE purchase_request SET status =0, rel_id='".$sale_id."' WHERE id ='".$request_id."'";
			
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
		}
		$builder = $appSession->getTier()->createBuilder("purchase_request_product");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("request_id", $request_id);
		$builder->add("create_uid", $user_id);
		$builder->add("write_uid", $user_id);
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("product_id", $product_id);
		$builder->add("rel_id", $sale_product_id);
		$builder->add("unit_id", $unit_id);
		$builder->add("attribute_id", $attribute_id);
		$builder->add("type_id", $type_id);
		$builder->add("quantity", $quantity);
		$builder->add("description", $description);
		$builder->add("status", 0);
		$builder->add("company_id", $company_id);
	
		$sql = $appSession->getTier()->getInsert($builder);
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}

	
	$sql = "SELECT d52.id, d52.code AS supplier_code, d52.name AS supplier_name, d1.sku,d53.id AS company_id, d53.name AS company_name , d2.name AS warehouse_name, d54.name AS unit_name, d55.name AS attribute_name, d56.name AS type_name,  SUM(d1.quantity-d1.quantity_out) AS quantity";
	$sql = $sql." FROM stock_product d1";
	$sql = $sql." LEFT OUTER JOIN stock_warehouse d2 ON(d1.warehouse_id = d2.id)";
	$sql = $sql." LEFT OUTER JOIN stock_product d14 ON(d1.rel_id = d14.id)";
	$sql = $sql." LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id)";
	$sql = $sql." LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id)";
	$sql = $sql." LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id)";
	$sql = $sql." LEFT OUTER JOIN purchase_receive_product_local d50 ON(d1.rel_id = d50.id OR d14.rel_id = d50.id OR d16.rel_id = d50.id OR d17.rel_id = d50.id) LEFT OUTER JOIN purchase_receive_local d51 ON(d50.receive_id = d51.id) LeFT OUTER JOIN supplier d52 ON(d51.supplier_id = d52.id) LEFT OUTER JOIN res_company d53 ON(d1.company_id = d53.id) LEFT OUTER JOIN product_unit d54 ON(d1.unit_id = d54.id) LEFT OUTER JOIN attribute d55 ON(d1.attribute_id = d55.id) LEFT OUTER JOIN product_type d56 ON(d1.type_id = d56.id)";
	$sql = $sql." WHERE d2.stock=1 AND d1.status =0 AND d1.product_id='".$product_id."' AND d1.unit_id='".$unit_id."' AND d1.attribute_id='".$attribute_id."' AND d1.type_id='".$type_id."' AND d1.quantity>d1.quantity_out";
	$sql = $sql." GROUP BY d52.id, d52.code, d52.name, d1.sku, d53.id, d53.name, d2.name, d54.name, d55.name, d56.name";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	
	$sql = "SELECT SUM(d1.quantity) FROM purchase_request_product d1 LEFT OUTER JOIN purchase_request d2 ON(d1.request_id = d2.id) WHERE d1.rel_id ='".$sale_product_id."' AND d1.status =0 AND d2.status =0";
	$msg->add("query", $sql);

	$sValue = $appSession->getTier()->getValue($msg);
	if($sValue != "")
	{
		$sale_quantity = $sale_quantity - $appSession->getTool()->toDouble($sValue);
	}
	
	?>
	<div class="row">
		<div class="col-12">
		<div class="table-responsive">
			<table class="table">
				<tr>
					
					<th>SL Tồn</th>
					<th>SL đặt</th>
					<th width="30"></th>
					<th>Kho</th>
					<th>Địa điểm</th>
					<th>SKU</th>
					<th>ĐVT</th>
					<th>Loại</th>
					<th>Hướng</th>
					<th>Mã NCC</th>
					<th>Tên NCC</th>
					
					<th width="40"></th>
				</tr>
				<?php
				for($i =0; $i<$dt->getRowCount(); $i++)
				{
					$quantity = $dt->getFloat($i, "quantity");
					$supplier_code = $dt->getString($i, "supplier_code");
					$supplier_name = $dt->getString($i, "supplier_name");
					$warehouse_name = $dt->getString($i, "warehouse_name");
					$company_name = $dt->getString($i, "company_name");
					$company_id = $dt->getString($i, "company_id");
					$unit_name = $dt->getString($i, "unit_name");
					$attribute_name = $dt->getString($i, "attribute_name");
					$type_name = $dt->getString($i, "type_name");
					$sku = $dt->getString($i, "sku");
					$a = $sale_quantity;
					if($a>$quantity)
					{
						$a = $quantity;
					}
				?>
				<tr>
					
					<td><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
					<td><input id="input<?php echo $i;?>" style="width:80px" type="number" class="form-control" value="<?php echo $a;?>"></td>
					<td><input class="btn btn-primary" type="button" value="+" onclick="addRequest(<?php echo $i;?>,'<?php echo $a;?>',  '<?php echo $company_id;?>','<?php echo $sku;?>')"/></td>
					<td><?php echo $warehouse_name;?></td>
					<td><?php echo $company_name;?></td>
					<td><?php echo $sku;?></td>
					<td><?php echo $unit_name;?></td>
					<td><?php echo $attribute_name;?></td>
					<td><?php echo $type_name;?></td>
					<td><?php echo $supplier_code;?></td>
					<td><?php echo $supplier_name;?></td>
					
				</tr>
				<?php
				}
				?>
			</table>
			</div>
		</div>
	</div>
	<script>
		function addRequest(index, qty, company_id, description)
		{
			var ctr = document.getElementById('input'+index);
			if(ctr.value == '')
			{
				alert('Please enter quantity');
				ctr.focus();
				return;
			}
			var quantity = parseFloat(ctr.value);
			if(quantity<=0 || quantity>qty)
			{
				alert("Số lượng không hợp lệ");
				ctr.focus();
				return;
			}
			var _url = '<?php echo URL;?>addons/order/inventory/?sale_id=<?php echo $sale_id;?>&sale_product_id=<?php echo $sale_product_id; ?>&company_id=' + company_id + '&description=' + encodeURIComponent(description);
			_url = _url + '&quantity=' + encodeURIComponent(quantity);
			
			loadPage('popupContent', _url, function(status, message)
			{
				
			}, false);
		}
		<?php
		if($company_id != "")
		{
			?>
			loadSaleProducts();
			<?php
		}
		?>
	</script>
	<?php
}

?>

    
