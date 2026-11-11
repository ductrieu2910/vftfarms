<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
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



$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, SUM(d1.quantity  - d1.quantity_out) AS quantity, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d1.company_id FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d2.unit_id = d3.id) LEFT OUTER JOIN stock d4 ON(d1.stock_id = d4.id) LEFT OUTER JOIN stock_category d5 ON(d4.category_id =d5.id)  LEFT OUTER JOIN stock_warehouse d8 ON(d1.warehouse_id = d8.id) WHERE d1.status =0 AND d4.status =0 AND d8.stock=1 AND d1.quantity>d1.quantity_out";	
$sql = $sql." GROUP BY d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d2.code, d2.name, d3.name, d5.name,d1.company_id";
$msg->add("query", $sql);
$dt_openning = $appSession->getTier()->getTable($msg);
	
	

$dt_product = $appSession->getTool()->selectDistinctTable($dt_openning, ["product_id","unit_id", "attribute_id", "type_id", "company_id"]);

$sql = "UPDATE product_count SET status =1 WHERE status =0";
$msg->add("query", $sql);


$appSession->getTier()->exec($msg);
$sql ="SELECT id, product_id, unit_id, type_id,attribute_id, company_id FROM product_count";

$msg->add("query", $sql);
$dt_product_count = $appSession->getTier()->getTable($msg);
$sqlList = "";
for($i =0; $i<$dt_product->getRowCount(); $i++)
{
	$product_id = $dt_product->getString($i, "product_id");
	$unit_id = $dt_product->getString($i, "unit_id");
	$attribute_id = $dt_product->getString($i, "attribute_id");
	$type_id = $dt_product->getString($i, "type_id");
	$company_id = $dt_product->getString($i, "company_id");
	$quantity = 0;
	for($j =0; $j<$dt_openning->getRowCount(); $j++)
	{
		if($dt_openning->getString($j, "product_id") == $product_id
		&& $dt_openning->getString($j, "unit_id") == $unit_id
		&&  $dt_openning->getString($j, "attribute_id") == $attribute_id
		&&  $dt_openning->getString($j, "type_id") == $type_id
		&&  $dt_openning->getString($j, "company_id") == $company_id)
		{
			$quantity = $quantity + $dt_openning->getFloat($j, "quantity");
		}
	}

	$hasItem = false;
	for($j =0; $j<$dt_product_count->getRowCount(); $j++)
	{
		if($dt_product_count->getString($j, "product_id") == $product_id
		&&  $dt_product_count->getString($j, "unit_id") == $unit_id
		&&  $dt_product_count->getString($j, "attribute_id") == $attribute_id
		&&  $dt_product_count->getString($j, "type_id") == $type_id
		&&  $dt_product_count->getString($j, "company_id") == $company_id)
		{
			if($sqlList != "")
			{
				$sqlList = $sqlList."; ";
			}
			$sqlList = $sqlList." UPDATE product_count SET quantity=".$quantity.", status=0 WHERE id ='".$dt_product_count->getString($j, "id")."'";
			
			
			$hasItem  = true;
			break;
		}
	}
	if($hasItem == false)
	{
		if($sqlList != "")
		{
			$sqlList = $sqlList."; ";
		}
		$sqlList = $sqlList." INSERT INTO product_count(id, product_id, unit_id, attribute_id, type_id, company_id, quantity, status)VALUES('".$appSession->getTool()->getId()."', '".$product_id."', '".$unit_id."', '".$attribute_id."', '".$type_id."', '".$company_id."', ".$quantity.", 0)";
		
	}
}
if($sqlList != "")
{
	
	$msg->add("query", $sqlList);
	
	$appSession->getTier()->exec($msg);
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

$sql = "SELECT d1.product_id, d1.unit_id, d1.type_id, d1.attribute_id, d2.code, d2.name, d3.name AS unit_name, d4.name AS attribute_name, d5.name AS type_name, d6.name AS category_name, d8.name AS parent_category_name, d8.sequence, d1.company_id, d7.name AS company_name , d6.sequence, SUM(d1.quantity) AS quantity FROM  product_count d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d1.unit_id = d3.id) LEFT OUTER JOIN attribute d4 ON(d1.attribute_id= d4.id) LEFT OUTER JOIN product_type d5 ON(d1.type_id = d5.id) LEFT OUTER JOIN product_category d6 ON(d2.category_id = d6.id) LEFT OUTER JOIN res_company d7 ON(d1.company_id = d7.id) LEFT OUTER JOIN product_category d8 ON(d6.parent_id = d8.id) WHERE d1.status =0 AND d6.status =0";

$sql = $sql." GROUP BY d1.product_id, d1.unit_id, d1.type_id, d1.attribute_id, d2.code, d2.name, d3.name, d4.name, d5.name, d6.name, d1.company_id, d7.name, d6.sequence, d8.sequence, d8.name";
$sql = $sql." ORDER BY d8.sequence ASC, d6.sequence ASC, d2.name ASC";



$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$dt_product = $appSession->getTool()->selectDistinctTable($dt, ["product_id", "unit_id", "type_id", "attribute_id", "code", "name", "unit_name", "attribute_name", "type_name", "category_name"]);

$dt_company = $appSession->getTool()->selectDistinctTable($dt, ["company_id", "company_name"]);


?>


 <strong><?php echo $report_name?></strong><br>
 
	
	<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td width="48" align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td width="202" align="center" nowrap style="width:80px"><strong>Mã sản phẩm </strong></td>
    <td width="206" align="center" nowrap style="width:60px"><strong>Tên sản phẩm </strong></td>
    <td width="167" align="center" nowrap style="width:60px"><strong>Đơn vị tính </strong></td>
    <td width="76" align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
	<?php
	for($j =0; $j<$dt_company->getRowCount(); $j++)
	{
		$company_name = $dt_company->getString($j, "company_name");
	?>
	<td width="190" align="center" nowrap style="width:60px"><strong><?php echo $company_name;?></strong></td>
	<?php
	}
	?>
    <td width="142" align="center" nowrap style="width:60px"><strong>Quy cách </strong></td>
    <td width="129" align="center" nowrap style="width:60px"><strong>Hướng  </strong></td>
    <td width="134" align="center" nowrap style="width:60px"><strong>Nhóm</strong></td>
	<td width="134" align="center" nowrap style="width:60px"><strong>Loại</strong></td>
    </tr>
  
	<?php
	$no = 0;
	


	
	for($i =0; $i<$dt_product->getRowCount(); $i++)
	{
		$no = $no + 1;
		
		
		$code = $dt_product->getString($i, "code");
		$name = $dt_product->getString($i, "name");
		$unit_name = $dt_product->getString($i, "unit_name");
		$attribute_name = $dt_product->getString($i, "attribute_name");
		$type_name = $dt_product->getString($i, "type_name");
		$product_id = $dt_product->getString($i, "product_id");
		$unit_id = $dt_product->getString($i, "unit_id");
		$attribute_id = $dt_product->getString($i, "attribute_id");
		$type_id = $dt_product->getString($i, "type_id");
		$quantity = 0;
		$category_name = $dt_product->getString($i, "category_name");
		$parent_category_name = $dt_product->getString($i, "parent_category_name");
		for($n =0; $n<$dt->getRowCount(); $n++)
		{
			if($dt->getString($n, "product_id") == $product_id && $dt->getString($n, "unit_id") == $unit_id && $dt->getString($n, "attribute_id") == $attribute_id && $dt->getString($n, "type_id") == $type_id)
			{
				$quantity = $quantity + $dt->getFloat($n, "quantity");
			}
		}
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $quantity;?></td>
		<?php
	for($j =0; $j<$dt_company->getRowCount(); $j++)
	{
		$company_id = $dt_company->getString($j, "company_id");
		$qty = 0;
		for($n =0; $n<$dt->getRowCount(); $n++)
		{
			if($dt->getString($n, "product_id") == $product_id && $dt->getString($n, "unit_id") == $unit_id && $dt->getString($n, "attribute_id") == $attribute_id && $dt->getString($n, "type_id") == $type_id && $dt->getString($n, "company_id") == $company_id)
			{
				$qty = $qty + $dt->getFloat($n, "quantity");
			}
		}
		
		
	?>
	<td align="right"  style="vertical-align:middle"  nowrap><?php echo $qty;?></td>
	<?php
	}
	?>
		<td align="left" style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $type_name;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $category_name;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $parent_category_name;?></td>
		
	  </tr>
	<?php
	}
	
	?>
</table>
