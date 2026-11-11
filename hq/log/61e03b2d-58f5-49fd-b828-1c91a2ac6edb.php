<?php
$config = new AppConfig("");
$config->parseWith($data, '&');
$fdate = $config->getProperty("fdate");
$fdate = $appSession->getTool()->urlDecode($fdate);
$tdate = $config->getProperty("tdate");
$tdate = $appSession->getTool()->urlDecode($tdate);
$location = $config->getProperty("location_id");
$warehouse = $config->getProperty("warehouse");
$warehouse = $appSession->getTool()->urlDecode($warehouse);
$search = $config->getProperty("search");
$search = $appSession->getTool()->urlDecode($search);

if($warehouse == "")
{
	exit();
}
$fdate = $appSession->getTool()->replace($fdate, " 00:00:00.000", "");
$tdate = $appSession->getTool()->replace($tdate, " 00:00:00.000", "");
$index = $appSession->getTool()->indexOf($fdate, " ");
if($index != -1)
{
	$fdate = $appSession->getTool()->subString($fdate, 0, $index);
}
$index = $appSession->getTool()->indexOf($tdate, " ");
if($index != -1)
{
	$tdate = $appSession->getTool()->subString($tdate, 0, $index);
}

if($fdate == "")
{
	$fdate = date("y-m-d");
}
if($tdate == "")
{
	$tdate = date("y-m-d");
}
if($appSession->getTool()->indexOf($fdate, " ") == -1)
{
	$fdate = $fdate." 00:00:00.000";
}
if($appSession->getTool()->indexOf($tdate, " ") == -1)
{
	$tdate = $tdate." 23:59:59.999";
}

$warehouseList = $appSession->getTool()->split($warehouse, ",");
$totalTimeList =  [];

?>

<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
<style>
	<?php 
	
	echo $appSession->getTool()->readFile(ABSPATH."assets/report/css.css"); ?>
	</style>
</head>
<body style="padding:5px">

 <strong>Báo cáo nhập/xuất tồn chi tiết </strong><br>
 <strong>From Date: <?php if($fdate != ""){ 
	$dDate = $appSession->getTool()->toDateTime($fdate);
	$sValue = $appSession->getFormats()->getDATE()->formatShortDateTime($dDate);
	echo $sValue;
	}
	?> </strong><br>
<strong>To Date: <?php if($tdate != "") {
$dDate = $appSession->getTool()->toDateTime($tdate);
$sValue = $appSession->getFormats()->getDATE()->formatShortDateTime($dDate);

echo $sValue;
}
?> </strong><br>
<strong>Warehouse:
<?php
	$warehouse_ids = "";
	for($i =0; $i<count($warehouseList); $i++)
	{
		$warehouse_id = $warehouseList[$i];
		if($warehouse_id != "")
		{
			if($warehouse_ids != ""){
				$warehouse_ids = $warehouse_ids." OR ";
			}
			$warehouse_ids = $warehouse_ids." d1.id='$warehouse_id' ";
		}
	}
	if($warehouse_ids != ""){
		$sql = "SELECT d1.name FROM stock_warehouse d1 WHERE (".$warehouse_ids.") ORDER BY d1.name ASC";
		$msg->add("query", $sql);
		$dt_warehouse = $appSession->getTier()->getTable($msg);
		$names = "";
		for($i =0; $i<$dt_warehouse->getRowCount(); $i++)
		{
			$name = $dt_warehouse->getString($i, "name");
			if($names != ""){
				$names = $names.", ";
			}
			$names = $names.$name;
		}
		echo $names;
	}
 ?>
</strong>
	<?php
	
	$sql = "SELECT d1.id, d4.id AS stock_id, d4.receipt_no, d4.receipt_date, d5.name AS category_name, d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, d1.quantity, d1.quantity_out, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d7.name AS attribute_name, d8.name AS type_name, d1.barcode AS barcode1, d14.barcode AS barcode2, d15.barcode AS barcode3, d16.barcode AS barcode4, d17.barcode AS barcode5, d18.barcode AS barcode6, d19.barcode AS barcode7, d20.barcode AS barcode8, d21.barcode AS barcode9, d22.barcode AS barcode10 , d23.barcode AS barcode11, d24.barcode AS barcode12, d25.barcode AS barcode13, d26.barcode AS barcode14, d27.barcode AS barcode15, d28.barcode AS barcode16, d29.barcode AS barcode17, d1.sku FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_category d5 ON(d4.category_id= d5.id) LEFT OUTER JOIN attribute d7 ON(d1.attribute_id = d7.id) LEFT OUTER JOIN product_type d8 ON(d1.type_id = d8.id) LEFT OUTER JOIN stock_product d14 ON(d1.rel_id = d14.id) LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id) LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id) LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id) LEFT OUTER JOIN stock_product d18 ON(d17.rel_id = d18.id) LEFT OUTER JOIN stock_product d19 ON(d18.rel_id = d19.id) LEFT OUTER JOIN stock_product d20 ON(d19.rel_id = d20.id) LEFT OUTER JOIN stock_product d21 ON(d20.rel_id = d21.id) LEFT OUTER JOIN stock_product d22 ON(d21.rel_id = d22.id) LEFT OUTER JOIN stock_product d23 ON(d22.rel_id = d23.id) LEFT OUTER JOIN stock_product d24 ON(d23.rel_id = d24.id) LEFT OUTER JOIN stock_product d25 ON(d24.rel_id = d25.id) LEFT OUTER JOIN stock_product d26 ON(d25.rel_id = d26.id) LEFT OUTER JOIN stock_product d27 ON(d26.rel_id = d27.id) LEFT OUTER JOIN stock_product d28 ON(d27.rel_id = d28.id) LEFT OUTER JOIN stock_product d29 ON(d28.rel_id = d29.id) WHERE d1.status =0 AND d4.status =0 AND d4.receipt_date>='".$fdate."' AND d4.receipt_date<'".$tdate."' AND d4.type='STOCKIN'";
	$warehouse_ids = "";
	for($j =0; $j<count($warehouseList); $j++){
		$warehouse_id = $warehouseList[$j];
		if($warehouse_id != ""){
			if($warehouse_ids != ""){
				$warehouse_ids = $warehouse_ids." OR ";
			}
			$warehouse_ids = $warehouse_ids." d1.warehouse_id ='".$warehouse_id."'";
		}
	}
	if($warehouse_ids != ""){
	$sql = $sql." AND (".$warehouse_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d2.code", "d2.name", "d1.barcode", "d1.sku"], $search).")";
	}
	$sql = $sql." ORDER BY d4.receipt_date ASC";
	$msg->add("query", $sql);
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	
	
	?>

	<table style="min-width:800px" border="0" cellspacing="0" cellpadding="3" class="borderLine">

	<tr class="header">
    <td width="20"  align="center"><strong>Số</strong></td>
	<td width="40" align="center"><strong>Mã</strong></td>
    <td  align="center"><strong>Tên</strong></td>
	 <td  align="center"><strong>Số phiếu</strong></td>
	  <td  align="center"><strong>Ngày</strong></td>
      <td  align="center"><strong>Barcode</strong></td>
      <td width="60"  align="center"><strong>Đơn vị tính </strong></td>
	<td width="60"  align="center"><strong>Nhập</strong></td>
	<td width="60"  align="center"><strong>Xuất</strong></td>
	<td width="60"  align="center"><strong>Tồn</strong></td>
	<td width="60"  align="center"><strong>Loại</strong></td>
	<td width="60"  align="center">PP KTCL</td>
	<td width="60"  align="center"><strong>SKU</strong></td>
	
	</tr>
	

	<?php
	$no = 0;
	$totalIn = 0;
	$totalOut = 0;
	$totalProduction = 0;
	for($i =0; $i< $dt_stock_product->getRowCount(); $i++)
	{
		
		$stock_product_id = $dt_stock_product->getString($i, "id");
		$product_id = $dt_stock_product->getString($i, "product_id");
		$receipt_no = $dt_stock_product->getString($i, "receipt_no");
		$receipt_date = $dt_stock_product->getString($i, "receipt_date");
		$category_name = $dt_stock_product->getString($i, "category_name");
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		$product_code = $dt_stock_product->getString($i, "product_code");
		$product_name = $dt_stock_product->getString($i, "product_name");
		$unit_name = $dt_stock_product->getString($i, "unit_name");


		$quantity = $dt_stock_product->getFloat($i, "quantity");
		
		$quantity_out = $dt_stock_product->getFloat($i, "quantity_out");
		$quantity_end = $quantity - $quantity_out;
		$attribute_name = $dt_stock_product->getString($i, "attribute_name");
		$type_name = $dt_stock_product->getString($i, "type_name");
		$sku = $dt_stock_product->getString($i, "sku");
		
		
		$no = $no + 1;
		
		$barcode = $dt_stock_product->getString($i, "barcode1");
		if($dt_stock_product->getString($i, "barcode2") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode2");
		}
		if($dt_stock_product->getString($i, "barcode3") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode3");
		}
		if($dt_stock_product->getString($i, "barcode4") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode4");
		}
		if($dt_stock_product->getString($i, "barcode5") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode5");
		}
		if($dt_stock_product->getString($i, "barcode6") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode6");
		}
		if($dt_stock_product->getString($i, "barcode7") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode7");
		}
		if($dt_stock_product->getString($i, "barcode8") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode8");
		}
		if($dt_stock_product->getString($i, "barcode9") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode9");
		}
		if($dt_stock_product->getString($i, "barcode10") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode10");
		}
		if($dt_stock_product->getString($i, "barcode11") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode11");
		}
		if($dt_stock_product->getString($i, "barcode12") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode12");
		}
		if($dt_stock_product->getString($i, "barcode13") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode13");
		}
		if($dt_stock_product->getString($i, "barcode14") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode14");
		}
		if($dt_stock_product->getString($i, "barcode15") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode15");
		}
		if($dt_stock_product->getString($i, "barcode16") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode16");
		}
		if($dt_stock_product->getString($i, "barcode17") != "")
		{
			if($barcode != "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt_stock_product->getString($i, "barcode17");
		}
		
		$totalIn = $totalIn + $quantity;
		$totalOut = $totalOut + $quantity_out;
		
		?>
		
		
		<tr>
		<td align="center"><?php echo $no;?></td>
		<td ><?php echo $product_code; ?></td>
		<td><?php echo $product_name;?></td>
		<td><?php echo $receipt_no;?></td>
		<td><?php echo $receipt_date;?></td>
		<td><?php echo $barcode;?></td>
		<td><?php echo $unit_name;?></td>
		<td  align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($quantity);?></td>
		<td  align="right"><a href="<?php echo URL;?>api/action/?action_id=96b12236-b2b4-4844-e4ed-7eaf35410f3a&data=<?php echo $appSession->getTool()->urlEncode("rel_id=".$stock_product_id."&product_id=".$product_id);?>" target="_blank"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($quantity_out);?></a></td>
		<td  align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($quantity_end);?></td>
		<td><?php echo $attribute_name;?></td>
		<td><?php echo $type_name;?></td>
		<td  align="left"><?php  echo $sku;?></td>
		
		</tr>
		
		
	<?php
			
	}
	$quantity_end = $totalIn - $totalOut;
	?>
	<tr>
		  <td colspan="7" align="center"><strong>Tổng:</strong></td>
		   <td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($totalIn);?></td>
		   <td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($totalOut);?></td>
		   
		   <td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($quantity_end);?></td>
		   <td align="right"></td>
		   <td align="center"> </td>
		   <td align="center"> </td>
		   
		  
	  </tr>
</table>

</body>
</html>
