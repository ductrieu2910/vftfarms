<?php
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$company_id = $report->getParamValueCaption("Company");
if($fdate == "")
{
	$fdate = date('Y-m-d');
}
if($tdate == "")
{
	$tdate = date('Y-m-d');
}
if($appSession->getTool()->indexOf($fdate, " ") == -1)
{
	$fdate = $fdate." 00:00:00";
}
if($appSession->getTool()->indexOf($tdate, " ") == -1)
{
	$tdate = $tdate." 23:59:59.999";
}

if($company_id == "")
{
	$company_id = $appSession->getConfig()->getProperty("company_id");
}


if($appSession->getTool()->indexOf($fdate, " ") == -1)
{
	$fdate = $fdate." 00:00:00.000";
}
if($appSession->getTool()->indexOf($tdate, " ") == -1)
{
	$tdate = $tdate." 23:59:59.999";
}
$sql = "SELECT d1.name, lg.description FROM ir_module_report d1 LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$lang_id."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE  d1.id='".$report_id."'";

$report_name = "";
$module_id = "";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

for($i =0; $i<$dt->getRowCount(); $i++)
{
	$report_name = $dt->getString($i, "description");
	$module_id = $dt->getString($i, "module_id");
	if($report_name == ""){
		$report_name = $dt->getString($i, "name");
	}
}

$sql = "SELECT id, code, name FROM res_company WHERE (id='".$company_id."' OR parent_id='".$company_id."') AND status =0";
$msg->add("query", $sql);
$dt_company = $appSession->getTier()->getTable($msg);

$company_names = "";
for($i =0; $i<$dt_company->getRowCount(); $i++)
{
	if($company_names != ""){
		$company_names = $company_names.", ";
	}
	$code = $dt_company->getString($i, "code");
	$name = $dt_company->getString($i, "name");
	$company_names = $company_names.$code.".".$name;
}


?>

<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
	<?php 
	
	echo $appSession->getTool()->readFile(ABSPATH."assets/report/css.css"); ?>
	</style>
<body style="padding:5px">

 <strong><?php echo $report_name?></strong><br>
 <strong>Cửa hàng: <?php echo $company_names;?></strong><br>
 <strong>Từ ngày: 
 <?php if($fdate != "") {
	echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($fdate));
 } ?>
</strong><br>
<strong>Đến ngày: 
<?php 
if($tdate != "") {
	echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($tdate));
 }?></strong><br>
 
 
	<?php
	
	$sql = "SELECT d1.id AS rel_id, d10.receipt_no, d10.receipt_date, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name , d11.code AS supplier_code, d11.name AS supplier_name, d1.barcode, d1.quantity, d9.quantity AS supplier_quantity, d4.receipt_no AS stock_receipt_no FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN purchase_receive_product d9 ON(d1.rel_id = d9.id) LEFT OUTER JOIN purchase_receive d10 ON(d9.receive_id = d10.id) LEFT OUTER JOIN supplier d11 ON(d10.supplier_id = d11.id) WHERE d1.status =0 AND d4.status =0 AND d4.receipt_date>='".$fdate."' AND d4.receipt_date<'".$tdate."' AND d10.id IS NOT NULL";
	
	$sql = $sql." ORDER BY d4.receipt_date ASC";
	$msg->add("query", $sql);
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	
	
	$ids = "";
	for($i =0; $i<$dt_stock_product->getRowCount(); $i++)
	{
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." d2.rel_id='".$dt_stock_product->getString($i, "rel_id")."'";
	}
	
	$sql= "SELECT d1.rel_id, d1.product_id, d2.type AS type1, d1.quantity AS quantity1, d3.quantity AS quantity2, d4.type AS type2 ,  d5.quantity AS quantity3, d6.type AS type3 ,  d7.quantity AS quantity4, d8.type AS type4 ,  d9.quantity AS quantity5, d10.type AS type5 , d1p.name AS product_name1, d1p.code AS product_code1, d1a.name AS attribute_name1, d2p.name AS product_name2, d2p.code AS product_code2, d2a.name AS attribute_name2, d3p.name AS product_name3, d3p.code AS product_code3, d3a.name AS attribute_name3, d4p.name AS product_name4, d4p.code AS product_code4, d4a.name AS attribute_name4, d5p.name AS product_name5, d5p.code AS product_code5, d5a.name AS attribute_name5, d1p.id AS product_id1, d2p.id AS product_id2, d3p.id AS product_id3, d4p.id AS product_id4, d5p.id AS product_id5, d1.id AS stock_product_id1, d3.id AS stock_product_id2, d5.id AS stock_product_id3, d7.id AS stock_product_id4, d9.id AS stock_product_id5 FROM stock_product d1 LEFT OUTER JOIN product d1p ON(d1.product_id = d1p.id) LEFT OUTER JOIN stock_warehouse d1a ON(d1.warehouse_id = d1a.id) LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) LEFT OUTER JOIN stock_product d3 ON(d3.rel_id = d1.id) LEFT OUTER JOIN product d2p ON(d3.product_id = d2p.id) LEFT OUTER JOIN stock_warehouse d2a ON(d3.warehouse_id = d2a.id) LEFT OUTER JOIN stock d4 ON(d3.stock_id = d4.id)  LEFT OUTER JOIN stock_product d5 ON(d5.rel_id = d3.id AND d5.status =0) LEFT OUTER JOIN product d3p ON(d5.product_id = d3p.id) LEFT OUTER JOIN stock_warehouse d3a ON(d5.warehouse_id = d3a.id) LEFT OUTER JOIN stock d6 ON(d5.stock_id = d6.id)  LEFT OUTER JOIN stock_product d7 ON(d7.rel_id = d5.id) LEFT OUTER JOIN product d4p ON(d7.product_id = d4p.id) LEFT OUTER JOIN stock_warehouse d4a ON(d7.warehouse_id = d4a.id) LEFT OUTER JOIN stock d8 ON(d7.stock_id = d8.id) LEFT OUTER JOIN stock_product d9 ON(d9.rel_id = d7.id) LEFT OUTER JOIN stock d10 ON(d9.stock_id = d10.id) LEFT OUTER JOIN product d5p ON(d9.product_id = d5p.id) LEFT OUTER JOIN stock_warehouse d5a ON(d9.warehouse_id = d5a.id) WHERE d1.status =0";
	if($ids != "")
	{
		$sql = $sql." AND (".$ids.")";
	}else
	{
		$sql = $sql." AND 1=0";
	}
	
	$msg->add("query", $sql);
	$dt_product = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT d2.rel_id, d1.id AS stock_product_id1, d1.product_id AS product_id1, d5.type AS type1, d1.quantity AS quantity1, d4.code AS product_code1, d4.name AS product_name1, d3.name AS attribute_name1 FROM stock_product d1 LEFT OUTER JOIN stock_product d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN stock_warehouse d3 ON(d1.warehouse_id = d3.id) LEFT OUTER JOIN product d4 ON(d1.product_id = d4.id) LEFT OUTER JOIN stock d5 ON(d2.stock_id = d5.id) WHERE d1.status =0";
	if($ids != "")
	{
		$sql = $sql." AND (".$ids.")";
	}else
	{
		$sql = $sql." AND 1=0";
	}
	
	$msg->add("query", $sql);
	$dt_product = $appSession->getTier()->getTable($msg);

	$productList =  [];
	$groupAttributes = [];
	for($i =0; $i<$dt_product->getRowCount(); $i++)
	{
		for($j=1; $j<=1; $j++)
		{
		
			if($dt_product->getString($i, "type".$j) == "WORKORDER" || $dt_product->getString($i, "type".$j) == "WORKORDER.CLOSED")
			{
				$hasItem = false;
				$attribute_name = $dt_product->getString($i, "attribute_name".$j);
				
				for($ii =0; $ii<count($groupAttributes); $ii++)
				{
					if($groupAttributes[$ii] == $attribute_name)
					{
						$hasItem = true;
						break;
					}
				}
				if($hasItem == false)
				{
					//$attribute_name = "";
				}
				$hasItem = false;
				for($n =0; $n<count($productList); $n++)
				{
					if($productList[$n][0] == $dt_product->getString($i, "product_id".$j) && $productList[$n][3] == $attribute_name)
					{
						$hasItem = true;
						break;
					}
				}
				if($hasItem == false){
					$productList[count($productList)] = [$dt_product->getString($i, "product_id".$j), $dt_product->getString($i, "product_name".$j), $dt_product->getString($i, "product_code".$j),  $attribute_name];
				}
			}
		}
		
	}


	?>

	<table style="min-width:800px" border="0" cellspacing="0" cellpadding="3" class="borderLine">

	<tr class="header">
    <td width="20"  align="center" nowrap><strong>Số</strong></td>
	<td width="40" align="center" nowrap><strong>Ngày nhập </strong></td>
    <td  align="center" nowrap><strong>Mã CC </strong></td>
	 <td  align="center" nowrap><strong>Tên nhà cung cấp </strong></td>
	  <td  align="center" nowrap><strong>Số lô </strong></td>
      <td width="60"  align="center" nowrap><strong>PO</strong></td>
	  <td width="60"  align="center" nowrap>Số phiếu nhập </td>
	  <td width="60"  align="center" nowrap><strong>Mã nguyên liệu </strong></td>
	  <td width="60"  align="center" nowrap><strong>Tên nguyên liệu </strong></td>
	  <td width="60"  align="center"><strong>Số lượng nhập tho </strong></td>
	  <td width="60"  align="center"><strong>Nhập B. Bản </strong></td>
	  <?php
	  for($n = 0; $n<count($productList); $n++)
	  {
	  	$name = $productList[$n][2].". ".$productList[$n][1];
		if($productList[$n][3] != "")
		{
			$name = $name." (".$productList[$n][3].")";
		}
	  ?>
	  <td width="60"  align="center"><strong><?php echo $name;?></strong></td>
	 
	  <?php
	  }
	  ?>
	  
	   <td width="60"  align="center"><strong>Tỷ lệ thu hồi</strong></td>
	</tr>
	

	<?php
	$no = 0;
	$totalQuantity = 0;
	$totalSupplier = 0;
	for($i =0; $i< $dt_stock_product->getRowCount(); $i++)
	{
		
		$rel_id = $dt_stock_product->getString($i, "rel_id");
		$receipt_no = $dt_stock_product->getString($i, "receipt_no");
		$receipt_date = $dt_stock_product->getString($i, "receipt_date");
		$supplier_code = $dt_stock_product->getString($i, "supplier_code");
		$supplier_name = $dt_stock_product->getString($i, "supplier_name");
		$barcode = $dt_stock_product->getString($i, "barcode");
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		$product_code = $dt_stock_product->getString($i, "product_code");
		$product_name = $dt_stock_product->getString($i, "product_name");
		$unit_name = $dt_stock_product->getString($i, "unit_name");
		$quantity = $dt_stock_product->getFloat($i, "quantity");
		$supplier_quantity = $dt_stock_product->getFloat($i, "supplier_quantity");
		
		$attribute_name = $dt_stock_product->getString($i, "attribute_name");
		$type_name = $dt_stock_product->getString($i, "type_name");
		$no = $no + 1;
		$totalQuantity = $totalQuantity + $quantity;
		$totalSupplier = $totalSupplier + $supplier_quantity;
		$stock_receipt_no = $dt_stock_product->getString($i, "stock_receipt_no");
		?>
		
		
		<tr>
		<td align="center" nowrap><?php echo $no;?></td>
		<td nowrap ><?php echo $receipt_date; ?></td>
		<td nowrap><?php echo $supplier_code;?></td>
		<td nowrap><?php echo $supplier_name;?></td>
		<td nowrap><?php echo $barcode;?></td>
		<td nowrap><?php echo $receipt_no;?></td>
		<td nowrap><?php echo $stock_receipt_no;?></td>
		<td nowrap><?php echo $product_code;?></td>
		<td nowrap><?php echo $product_name;?></td>
		<td  align="right"><a href="<?php echo URL;?>api/action/?action_id=3d712371-b5bf-44fb-c98e-042ef329a758&data=<?php echo $appSession->getTool()->urlEncode("rel_id=".$rel_id);?>" target="_blank"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($quantity);?></a></td>
		<td  align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($supplier_quantity);?></td>
		  <?php
		$workorder_quantity_reuse = 0;
		$workorder_quantity_waste = 0;
	  for($n = 0; $n<count($productList); $n++)
	  {
	  	$product_id = $productList[$n][0];
		$attribute_name = $productList[$n][3];
		$workorder_quantity = 0;
	
		$stockProductList = [];
		for($j =0; $j<$dt_product->getRowCount(); $j++)
		{
			
			if($dt_product->getString($j, "rel_id") == $rel_id)
			{
				
				for($m=1; $m<=1; $m++)
				{
					$product_attribute_name = $dt_product->getString($j, "attribute_name".$m);
					$hasItem = false;
					for($ii =0; $ii<count($groupAttributes); $ii++)
					{
						if($groupAttributes[$ii] == $product_attribute_name)
						{
							$hasItem = true;
							break;
						}
					}
					
					if(($dt_product->getString($j, "type".$m) == "WORKORDER"|| $dt_product->getString($j, "type".$m) == "WORKORDER.CLOSED") && $dt_product->getString($j, "product_id".$m) == $product_id && $product_attribute_name == $attribute_name)
					{
						$workorder_quantity =  $workorder_quantity + floatval($dt_product->getString($j, "quantity".$m));
						$workorder_quantity_reuse =  $workorder_quantity_reuse + floatval($dt_product->getString($j, "quantity".$m));
						if($attribute_name == "Hao hụt")
						{
							$workorder_quantity_waste =  $workorder_quantity_waste + floatval($dt_product->getString($j, "quantity".$m));
						}
						
					}
					
				}
			}
		}
		?>
		<td  align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($workorder_quantity);?></td>
		
		<?php
		}
		
		$pp = 0;
		if($workorder_quantity_reuse != 0)
		{
			$pp = (($quantity-$workorder_quantity_waste)/$quantity);
		}
		?>
		<td  align="right"><?php echo $appSession->getFormats()->getPERCENT()->formatWithPlace($pp, 2);?></td>
		</tr>
		
	<?php
			
	}
	?>
	
</table>

</body>
</html>
