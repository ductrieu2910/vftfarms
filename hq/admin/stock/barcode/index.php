<?php
$msg = $appSession->getTier()->createMessage();
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}


if($ac == "view")
{
	
?>
	
	<div class="row">
		<div class="col m1 padding"><b>Barcode</b>:</div>
		<div class="col m10 padding"><input type="text" class="input" value="" id="editSearchBarcode" onKeyDown="if(event.keyCode == 13){seachBarcode();}"/></div>
		<div class="col m1 padding"><button class="btn btn-default" onclick="seachBarcode()">Search</button></div>
	</div>
	
	<div class="row">
		<div class="col m12 padding" id="pnStockProduct"></div>
	</div>
	<script>
		function seachBarcode()
		{
			var search= document.getElementById('editSearchBarcode').value;
			var _url = '<?php echo URL;?>admin/stock/barcode/?ac=find&barcode=' + encodeURIComponent(search);
			loadPage('pnStockProduct', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
	</script>
<?php
}else if($ac == "find")
{
	$barcode = "";
	if(isset($_REQUEST['barcode']))
	{
		$barcode = $_REQUEST['barcode'];
	}
	$sql = "SELECT d1.id, d2.code, d2.name, d1.product_id, d3.name AS warehouse_name FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN stock_warehouse d3 ON(d1.warehouse_id = d3.id) WHERE d1.barcode='".$appSession->getTool()->replace($barcode, "'", "''")."' AND d1.status =0";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount() == 0)
	{
		echo "<h1>".$barcode." is not found</h1>";
		exit();
	}
	$warehouse_name = $dt->getString(0, "warehouse_name");
	if($warehouse_name == "")
	{
		$warehouse_name = "?";
	}
	?>
	Code: <b><?php echo $dt->getString(0, "code");?></b><br>
	Name: <b><?php echo $dt->getString(0, "name");?></b><br>
	Warehouse: <b><a href="javascript:openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=16f6a001-803b-47cf-956f-017e51f654dd&module_model_id=bc525bcc-4356-4a7f-cd9a-65ec0c1e50b6&pid=&parent_id=&parent_value=&func=selListWarehouse')"><?php echo $warehouse_name;?></a></b><br>
	<br>
	<a href="javascript:addResReceipt()"><i class="icon-plus"></i> Log</a>
	<div id="pnStockList"></div>
	<script>
		function loadReceipt()
		{
			var _url = '<?php echo URL;?>admin/stock/barcode/?ac=list_specification&rel_id=<?php echo $dt->getString(0, "id");?>';
			loadPage('pnStockList', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		loadReceipt();
		function addResReceipt()
		{
		
			openPopup('<?php echo URL;?>admin/stock/barcode/?ac=specification&product_id=<?php echo $dt->getString(0, "product_id");?>&rel_id=<?php echo $dt->getString(0, "id");?>');
		}
		
		function addSpecification(res_id)
		{
			var _url = '<?php echo URL;?>admin/stock/barcode/?ac=addReceipt&rel_id=<?php echo $dt->getString(0, "id");?>&res_id=' + res_id;
		
			loadPage('pnStockList', _url, function(status, message)
			{
				if(status  == 0)
				{
					if(message.indexOf("OK") != -1)
					{
						loadReceipt();
						
					}else
					{
						alert(message);
					}
				}
			}, true);
			
			
		}
		function delReceipt(id)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/stock/barcode/?ac=del&id=' + id;
			loadPage('pnStockList', _url, function(status, message)
			{
				if(status  == 0)
				{
					if(message.indexOf("OK") != -1)
					{
						loadReceipt();
					}else
					{
						alert(message);
					}
				}
			}, true);
		}
		function showAttribute(id, rel_ids)
		{
			openPopup('<?php echo URL;?>admin/attribute_line_view/?id=' + id + '&rel_ids=' + rel_ids);
		}
		function selListWarehouse(id, name)
		{
			var _url = '<?php echo URL;?>admin/stock/barcode/?ac=updateWarehouse&id=<?php echo $dt->getString(0, "id");?>&warehouse_id=' + id;
			closePopup();
			loadPage('pnStockList', _url, function(status, message)
			{
				if(status  == 0)
				{
					seachBarcode();
				}
			}, true);
		}
		
		
	</script>
	<?php
	
}else if($ac == "list_specification")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.id, d1.rel_id, d1.res_id, d1.receipt_no, d1.receipt_date, d3.name FROM res_receipt d1 LEFT OUTER JOIN product_classification d3 ON(d1.res_id = d3.id) WHERE d1.status =0 AND d1.rel_id='".$rel_id."' ORDER BY d1.receipt_date DESC";
	$msg->add("query", $sql);

	$dt_receipt = $appSession->getTier()->getTable($msg);
	?>
	<div class="responsive">
	<table class="table">
		<tr class="header">
			<td width="20"></td>
			<td>Receipt No</td>
			<td>Date</td>
			<td>Name</td>
			<td width="20"></td>
		</tr>
		<?php
		for($i =0; $i<$dt_receipt->getRowCount(); $i++)
		{
			$id = $dt_receipt->getString($i, "id");
			$receipt_no = $dt_receipt->getString($i, "receipt_no");
			$receipt_date = $dt_receipt->getString($i, "receipt_date");
			$name = $dt_receipt->getString($i, "name");
			if($receipt_date != "")
			{
				$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
			}
		?>
		<tr>
		<td width="20"><a href="javascript:showAttribute('<?php echo $id;?>', '<?php echo $dt_receipt->getString($i, "res_id");?>')"><i class="icon-pencil"></i></a>
		</td>
		<td><?php echo $receipt_no;?></td>
		<td><?php echo $receipt_date;?></td>
		<td><?php echo $name;?></td>
		<td width="20"><a href="javascript:delReceipt('<?php echo $id;?>')"><i class="icon-minus"></i></a></td>
		</tr>
		<?php
		}
		?>
	</table>
	</div>
	<?php
	
}else if($ac == "specification")
{
	$product_id = "";
	if(isset($_REQUEST['product_id']))
	{
		$product_id = $_REQUEST['product_id'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d2.id, d2.code, d2.name FROM res_rel d1 LEFT OUTER JOIN product_classification d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND d1.res_id='".$product_id."' ORDER BY d2.code ASC, d2.name ASC";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	?>
	<div class="responsive">
	<table class="table">
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$code = $dt->getString($i, "code");
			$name = $dt->getString($i, "name");
		?>
		<tr><td width="20"><a href="javascript:addSpecification('<?php echo $id;?>')"><i class="icon-plus"></i></a></td><td><?php echo $code;?></td><td><?php echo $name;?></td></tr>
		<?php
		}
		?>
	</table>
	</div>
	<?php
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_receipt SET status=1 WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addReceipt")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$res_id = "";
	if(isset($_REQUEST['res_id']))
	{
		$res_id = $_REQUEST['res_id'];
	}
	
	
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "res_receipt.specification");
	$builder = $appSession->getTier()->createBuilder("res_receipt");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("rel_id", $rel_id);
	$builder->add("res_id", $res_id);
	$builder->add("receipt_no", $receipt_no);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "updateWarehouse")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$warehouse_id = "";
	if(isset($_REQUEST['warehouse_id']))
	{
		$warehouse_id = $_REQUEST['warehouse_id'];
	}
	$sql = "UPDATE stock_product SET warehouse_id='".$warehouse_id."' WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}

?>

