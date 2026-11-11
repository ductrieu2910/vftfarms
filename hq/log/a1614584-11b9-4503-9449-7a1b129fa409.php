<?php
$config = new AppConfig("");
$config->parseWith($data, '&');
$fdate = $config->getProperty("fdate");
$fdate = $appSession->getTool()->urlDecode($fdate);
$tdate = $config->getProperty("tdate");
$tdate = $appSession->getTool()->urlDecode($tdate);
$warehouse = $config->getProperty("warehouse");
$warehouse = $appSession->getTool()->urlDecode($warehouse);
$fdate = $appSession->getTool()->replace($fdate, " 00:00:00.000", "");
$tdate = $appSession->getTool()->replace($tdate, " 00:00:00.000", "");
$index = $appSession->getTool()->indexOf($fdate, " ");
if($warehouse == "")
{
exit();
}
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

$sql = "SELECT d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, d1.quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name , '0' AS deduction FROM stock_balance_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) WHERE 1=0";

$msg->add("query", $sql);
$dt_openning = $appSession->getTier()->getTable($msg);

?>

<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
	<?php 
	
	echo $appSession->getTool()->readFile(ABSPATH."assets/report/css.css"); ?>
	</style>
<body style="padding:10px">

 <strong>Xuất nhập tồn</strong><br>
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
	for($i =0; $i<count($warehouseList); $i++)
	{
		$warehouse_id = $warehouseList[$i];
		$sql = "SELECT id, receipt_date FROM stock_balance WHERE warehouse_id='".$warehouse_id."' AND receipt_date<'".$fdate."' ORDER BY receipt_date DESC LIMIT 1";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getTable($msg);
		$receipt_date = "";
		for($j=0; $j< $values->getRowCount(); $j++)
		{
			$receipt_date = $values->getString($j, "receipt_date");
			$balance_id = $values->getString($j, "id");
			
			$sql = "SELECT d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, d1.quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, '0'  AS deduction FROM stock_balance_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) WHERE d1.balance_id='".$balance_id."'";
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
			for($ii =0; $ii<$dt->getRowCount(); $ii++)
			{
				$unit_id = $dt->getString($ii, "unit_id");
				$product_unit_id = $dt->getString($ii, "product_unit_id");
				$quantity = $dt->getFloat($ii, "quantity");
				$product_id = $dt->getString($ii, "product_id");
				$quantity = $appSession->getUnit()->convert($unit_id, $product_unit_id, $product_id, $quantity);
				$dt->setValue($quantity, $ii, "quantity");
			}
			for($n=0; $n< $dt->getRowCount(); $n++){
				$dt_openning->addArray($dt->getDataRow($n));
			}
		}
		
		$sql = "SELECT d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, SUM(d1.quantity ) AS quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d4.deduction FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_category d5 ON(d4.category_id =d5.id)  WHERE d1.status =0 AND d4.status =0 AND d1.warehouse_id='".$warehouse_id."'";
		if($receipt_date != ""){
			$sql = $sql." AND d4.receipt_date>='".$receipt_date."'";
		}
	
		$sql = $sql." AND d4.receipt_date<'".$fdate."' GROUP BY d1.product_id, d2.unit_id, d1.unit_id, d2.code, d2.name, d3.name, d4.deduction, d5.name";
		
		
		$msg->add("query", $sql);
	
		$dt = $appSession->getTier()->getTable($msg);
		for($ii =0; $ii<$dt->getRowCount(); $ii++)
		{
			$unit_id = $dt->getString($ii, "unit_id");
			$product_unit_id = $dt->getString($ii, "product_unit_id");
			$quantity = $dt->getFloat($ii, "quantity");
			$product_id = $dt->getString($ii, "product_id");
			$quantity = $appSession->getUnit()->convert($unit_id, $product_unit_id, $product_id, $quantity);
			$dt->setValue($quantity, $ii, "quantity");
		}
		
		for($j =0; $j<$dt->getRowCount(); $j++)
		{
			$dt_openning->addArray($dt->getDataRow($j));
		}
		
		$sql = "SELECT d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, SUM(d1.quantity * -1) AS quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d4.deduction FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_category d5 ON(d4.category_id =d5.id)  LEFT OUTER JOIN stock_product d6 ON(d1.rel_id = d6.id AND d6.status =0) LEFT OUTER JOIN stock d7 ON(d6.stock_id = d7.id) WHERE d1.status =0 AND d4.status =0 AND d6.status =0 AND d7.status =0 AND d6.warehouse_id='".$warehouse_id."'";
		if($receipt_date != ""){
			$sql = $sql." AND d4.receipt_date>='".$receipt_date."'";
		}
		$sql = $sql." AND d4.receipt_date<'".$fdate."' GROUP BY d1.product_id, d2.unit_id, d1.unit_id, d2.code, d2.name, d3.name, d4.deduction, d5.name";
		
			
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($ii =0; $ii<$dt->getRowCount(); $ii++)
		{
			$unit_id = $dt->getString($ii, "unit_id");
			$product_unit_id = $dt->getString($ii, "product_unit_id");
			$quantity = $dt->getFloat($ii, "quantity");
			$product_id = $dt->getString($ii, "product_id");
			$quantity = $appSession->getUnit()->convert($unit_id, $product_unit_id, $product_id, $quantity);
			$dt->setValue($quantity, $ii, "quantity");
		}
		
		for($j =0; $j<$dt->getRowCount(); $j++)
		{
			$dt_openning->addArray($dt->getDataRow($j));
		}
		
			
	}
	
	$sql = "SELECT d1.product_id, d2.unit_id AS product_unit_id, d1.unit_id, SUM(d1.quantity) AS quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, 0 AS deduction FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_category d5 ON(d4.category_id= d5.id) WHERE d1.status =0 AND d4.status =0 AND d4.receipt_date>='".$fdate."' AND d4.receipt_date<'".$tdate."'";
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
	$sql = $sql." GROUP BY d1.product_id, d2.unit_id, d1.unit_id, d2.code, d2.name, d3.name, d5.deduction";


	
	$msg->add("query", $sql);
	$dt_stock_product = $appSession->getTier()->getTable($msg);
	
	for($ii =0; $ii<$dt_stock_product->getRowCount(); $ii++)
	{
		$unit_id = $dt_stock_product->getString($ii, "unit_id");
		$product_unit_id = $dt_stock_product->getString($ii, "product_unit_id");
		$quantity = $dt_stock_product->getFloat($ii, "quantity");
		$product_id = $dt_stock_product->getString($ii, "product_id");
		$quantity = $appSession->getUnit()->convert($unit_id, $product_unit_id, $product_id, $quantity);
		$dt_stock_product->setValue($quantity, $ii, "quantity");
	}
	
	
	
	$sql = "SELECT d5.product_id, d2.unit_id AS product_unit_id, d1.unit_id, SUM(d1.quantity) AS quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, 1 AS deduction FROM stock_product d1 LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_product d5 ON(d1.rel_id = d5.id) LEFT OUTER JOIN stock_category d6 ON(d4.category_id = d6.id)  LEFT OUTER JOIN product d2 ON(d5.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) WHERE d1.status =0 AND d4.status =0 AND d5.status =0 AND d4.receipt_date>='".$fdate."' AND d4.receipt_date<'".$tdate."'";
	$warehouse_ids = "";
	for($j =0; $j<count($warehouseList); $j++){
		$warehouse_id = $warehouseList[$j];
		if($warehouse_id != ""){
			if($warehouse_ids != ""){
				$warehouse_ids = $warehouse_ids." OR ";
			}
			$warehouse_ids = $warehouse_ids." d5.warehouse_id ='".$warehouse_id."'";
		}
	}
	if($warehouse_ids != ""){
	$sql = $sql." AND (".$warehouse_ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY d5.product_id, d2.unit_id, d1.unit_id, d2.code, d2.name, d3.name, d6.deduction";
	$msg->add("query", $sql);

	$dt_stock_product_out = $appSession->getTier()->getTable($msg);
	for($ii =0; $ii<$dt_stock_product_out->getRowCount(); $ii++)
	{
		
		$unit_id = $dt_stock_product_out->getString($ii, "unit_id");
		$product_unit_id = $dt_stock_product_out->getString($ii, "product_unit_id");
		$quantity = $dt_stock_product_out->getFloat($ii, "quantity");
		$product_id = $dt_stock_product_out->getString($ii, "product_id");
		
		$quantity = $appSession->getUnit()->convert($unit_id, $product_unit_id, $product_id, $quantity);
		$dt_stock_product_out->setValue($quantity, $ii, "quantity");
		
		
	}


	$dt_product = $appSession->getTool()->selectDistinctTable($dt_openning, ["product_id", "product_code" ,"product_name","product_unit_id","unit_name"]);
	
	$dt = $appSession->getTool()->selectDistinctTable($dt_stock_product, ["product_id" , "product_code" ,"product_name" , "product_unit_id","unit_name"]);

	for($n=0; $n<$dt->getRowCount(); $n++){
		$hasItem = 0;
		$product_id = $dt->getString($n, "product_id");
		for($m=0; $m<$dt_product->getRowCount(); $m++){
			$product_id1 = $dt_product->getString($m, "product_id");
			if($product_id == $product_id1){
				$hasItem = 1;
			}
		}
		if($hasItem == 0){
			$dt_product->addArray($dt->getDataRow($n));
		}
	}
	
	$dt = $appSession->getTool()->selectDistinctTable($dt_stock_product_out, ["product_id" , "product_code" ,"product_name" , "product_unit_id","unit_name"]);

	for($n=0; $n<$dt->getRowCount(); $n++){
		$hasItem = 0;
		$product_id = $dt->getString($n, "product_id");
		for($m=0; $m<$dt_product->getRowCount(); $m++){
			$product_id1 = $dt_product->getString($m, "product_id");
			if($product_id == $product_id1){
				$hasItem = 1;
			}
		}
		if($hasItem == 0){
			$dt_product->addArray($dt->getDataRow($n));
		}
	}
	
	
	?>
	
	<table width="100%" style="min-width:800px" border="0" cellspacing="0" cellpadding="2" class="borderLine">

	<tr class="header">
    <td width="20"  align="center"><strong>Số</strong></td>
	<td width="40" align="center"><strong>Mã</strong></td>
    <td  align="center"><strong>Tên</strong></td>
    <td width="60"  align="center"><strong>Đơn vị tính </strong></td>
    <td width="60"  align="center"><strong>Đầu kỳ </strong></td>
    <td align="center" colspan="$cin_len"><strong>Nhập</strong></td>
    <td align="center"  colspan="$cout_len"><strong>Xuất</strong></td>
	<td width="60"  align="center"><strong>Cuối kỳ </strong></td>
	</tr>
	

	<?php
	$no = 0;
	$total_openning = 0;
	$total_stock_in = 0;
	$total_stock_out = 0;
	for($i =0; $i< $dt_product->getRowCount(); $i++)
	{
		$no = $no + 1;
		$product_id = $dt_product->getString($i, "product_id");
		$product_code = $dt_product->getString($i, "product_code");
		$product_name = $dt_product->getString($i, "product_name");
		$unit_name = $dt_product->getString($i, "unit_name");
		$openning = 0;
		$stock_in = 0;
		$stock_out = 0;
		
		
		for($j=0 ; $j<$dt_openning->getRowCount(); $j++)
		{
			$productid = $dt_openning->getString($j, "product_id");
			$deduction = $dt_openning->getString($j, "deduction");
			if($productid == $product_id){
				$d = $dt_openning->getFloat($j, "quantity");
				$openning = $openning + $d;
				
			}
		}
		?>	
		<tr>
		<td align="center"><?php echo $no;?></td>
		<td ><?php echo $product_code; ?></td>
		<td><?php echo $product_name;?></td>
		<td><?php echo $unit_name;?></td>
		<td  align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($openning);?></td>
		<?php
		
		$qty = 0;
		for($j=0; $j<$dt_stock_product->getRowCount(); $j++)
		{
			$productid = $dt_stock_product->getString($j, "product_id");
			$deduction =  $dt_stock_product->getString($j, "deduction");
			if( $productid == $product_id){
				$d =  $dt_stock_product->getFloat($j, "quantity");
				$qty = $qty + $d;
			}
		}
		$stock_in = $stock_in + $qty;
		?>
		 <td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($qty);?></td>
		 <?php
			
			
		$qty =0;
		for($n=0; $n<$dt_stock_product_out->getRowCount(); $n++)
		{
			$productid = $dt_stock_product_out->getString($n, "product_id");
			$deduction =  $dt_stock_product_out->getString($n, "deduction");
			if( $productid == $product_id){
				$d = $dt_stock_product_out->getFloat($n, "quantity");
				$qty = $qty + $d;
			}
		}
		$stock_out =$stock_out + $qty;
		?>
		 <td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($qty);?></td>
		 <?php
			
		
		$ending = $openning + $stock_in;
		$ending =$ending - $stock_out;
		?>
		<td align="right"><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($ending);?></td>
		</tr>
		<?php
		$total_openning = $total_openning + $openning;
		$total_stock_in = $total_stock_in + $stock_in;
		$total_stock_out = $total_stock_out + $stock_out;
	
	}
	$ending = $total_openning + $total_stock_in;
	$ending =$ending - $total_stock_out;
	?>
	<tr>
		<td colspan="4" align="center"><strong>Tổng</strong></td>
		<td  align="right"><strong><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($total_openning);?></strong></td>
		<td  align="right"><strong><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($total_stock_in);?></strong></td>
		<td  align="right"><strong><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($total_stock_out);?></strong></td>
		<td  align="right"><strong><?php echo $appSession->getFormats()->getDOUBLE()->formatZero($ending);?></strong></td>
	  </tr>
</table>
</body>
</html>
