<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$company_id = $report->getParamValueCaption("Company");
$search = $report->getParamValueCaption("Search");
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
$company_currency_id = $appSession->getConfig()->getProperty("currency_id");

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

$sql = "SELECT d3.id, d3.code, d3.name, d3.phone, d3.email, d3.address, date_part('year', d2.receipt_date) AS year,
date_part('month', d2.receipt_date) AS month, date_part('day', d2.receipt_date) AS day, d1.product_id, d4.code AS product_code, d4.name AS product_name, d5.name AS unit_name, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount FROM purchase_receive_product d1 LEFT OUTER JOIN purchase_receive d2 ON(d1.receive_id = d2.id) LEFT OUTER JOIN supplier d3 ON(d2.supplier_id = d3.id) LEFT OUTER JOIN product d4 ON(d1.product_id = d4.id) LEFT OUTER JOIN product_unit d5 ON(d1.unit_id = d5.id) WHERE d1.status =0 AND d2.status =0";
$sql = $sql." AND d2.receipt_date>='".$fdate."'";
$sql = $sql." AND d2.receipt_date<='".$tdate."'";

if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d2.receipt_no", "d3.code", "d3.name", "d3.phone", "d3.email", "d3.address"], $search).")";
}


$company_ids = "";
for($i =0; $i<$dt_company->getRowCount(); $i++)
{
	if($company_ids != ""){
		$company_ids = $company_ids." OR ";
	}
	$id = $dt_company->getString($i, "id");
	$company_ids = $company_ids." d2.company_id='$id'";
}
$sql = $sql." AND (".$company_ids.") ";
$sql = $sql." GROUP BY   1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13";
$sql = $sql." ORDER BY 9, 8, 7, d3.name ASC, d4.name ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);
$dt_supplier = $appSession->getTool()->selectDistinctTable($dt, ["id", "code", "name", "phone", "email", "address"]);
?>


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
	
	<table width="100%" border="0" class="borderLine">
	<?php 
	for($n =0; $n<$dt_supplier->getRowCount(); $n++)
	{
		$supplier_id = $dt_supplier->getString($n, "id");
		$supplier_code = $dt_supplier->getString($n, "code");
		$supplier_name = $dt_supplier->getString($n, "name");
		$supplier_address = $dt_supplier->getString($n, "address");
		$dateList = [];
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "id") != $supplier_id)
			{
				continue;
			}
			$y = $dt->getString($i, "year");
			$m = $dt->getString($i, "month");
			$d = $dt->getString($i, "day");
			$hasItem = false;
			for($j =0; $j<count($dateList); $j++)
			{
				if($dateList[$j][0] == $y && $dateList[$j][1] == $m && $dateList[$j][2] == $d)
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
				$dateList[count($dateList)] = [$y, $m, $d, 0, 1];
			}
		}
		for($j =0; $j<count($dateList); $j++)
		{
			$amount = 0;
			$rowspan = 0;
			for($i =0; $i<$dt->getRowCount(); $i++)
			{
				if($dt->getString($i, "id") != $supplier_id)
				{
					continue;
				}
				$y = $dt->getString($i, "year");
				$m = $dt->getString($i, "month");
				$d = $dt->getString($i, "day");
				if($dateList[$j][0] == $y && $dateList[$j][1] == $m && $dateList[$j][2] == $d)
				{
					$amount = $amount + $dt->getFloat($i, "amount");
					$rowspan = $rowspan + 1;
				}
			}
			if($rowspan == 0){
				$rowspan = 1;
			}
			$dateList[$j][3] = $amount;
			$dateList[$j][4] = $rowspan;
		}
	?>
  <tr class="header">
    <td colspan="10" align="left" nowrap style="width:20px"><?php echo $supplier_code;?>. <?php echo $supplier_name;?> - <?php echo $supplier_address;?></td>
    </tr>
  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:60px">Ngày</td>
	<td align="center" nowrap style="width:60px"><strong>Mã</strong></td>
	<td align="center" nowrap ><strong>Tên</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Đơn vị tính </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Đơn giá </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Tổng</strong></td>
	<td  align="center" nowrap style="width:200px"><strong>Ghi chú </strong></td>
  </tr>
 	<?php
	$totalAmount = 0;
	$totalQuantity = 0;
	for($j =0; $j<count($dateList); $j++)
	{
		$amount = 0;
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "id") != $supplier_id)
			{
				continue;
			}
			$y = $dt->getString($i, "year");
			$m = $dt->getString($i, "month");
			$d = $dt->getString($i, "day");
			if($dateList[$j][0] == $y && $dateList[$j][1] == $m && $dateList[$j][2] == $d)
			{
			
				
			$product_code = $dt->getString($i, "product_code");
			$product_name = $dt->getString($i, "product_name");
			$unit_name = $dt->getString($i, "unit_name");
			$quantity = $dt->getFloat($i, "quantity");
			$amount = $dt->getFloat($i, "amount");
			$unit_price = $amount/$quantity;
			$totalAmount = $totalAmount + $amount;
			$totalQuantity = $totalQuantity + $quantity;
	?>
	<tr>
		<td style="vertical-align:middle" rowspan="<?php echo $dateList[$j][4];?>"><?php echo $j + 1;?>.</td>
		<td style="vertical-align:middle" rowspan="<?php echo $dateList[$j][4];?>"><?php echo $dateList[$j][2];?>/<?php echo $dateList[$j][1];?>/<?php echo $dateList[$j][0];?></td>
		<td style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle; text-align:center"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td style="vertical-align:middle; text-align:right"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td style="vertical-align:middle; text-align:right"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td style="text-align: right;" rowspan="<?php echo $dateList[$j][4];?>"><?php echo $appSession->getFormats()->getINT()->format($appSession->getTool()->toDouble($dateList[$j][3]));?></td>
		<td style="vertical-align:middle" rowspan="<?php echo $dateList[$j][4];?>"> </td>
	</tr>
	<?php
			break;
		}
	}

	?>
	<?php
	$no = 0;
	

	$first = true;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "id") != $supplier_id)
		{
			continue;
		}
		$y = $dt->getString($i, "year");
		$m = $dt->getString($i, "month");
		$d = $dt->getString($i, "day");
		if($dateList[$j][0] != $y || $dateList[$j][1] != $m || $dateList[$j][2] != $d)
		{
			continue;
		}
		if($first == true)
		{
			$first = false;
			continue;
		}
		
	
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$unit_name = $dt->getString($i, "unit_name");
		$quantity = $dt->getFloat($i, "quantity");
		$amount = $dt->getFloat($i, "amount");
		$unit_price = $amount/$quantity;
		$totalAmount = $totalAmount + $amount;
	
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle; text-align:center"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td style="vertical-align:middle; text-align:right"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td style="vertical-align:middle; text-align:right"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
	</tr>
	<?php
	}
	}
	?>
	<tr>
		<td colspan="5" style="vertical-align:middle"><strong>Tổng cộng: </strong></td>
		<td style="vertical-align:middle;  text-align:right"><b><?php echo $appSession->getFormats()->getDOUBLE()->format($totalQuantity);?></b></td>
		<td style="vertical-align:middle"> </td>
		<td style="vertical-align:middle"> </td>
		<td style="vertical-align:middle; text-align:right"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
	    <td style="vertical-align:middle"> </td>
	</tr>
	 <?php
	 }
	 ?>
</table>
