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

$sql = "SELECT d1.quantity, d2.code, d2.name, d3.name AS unit_name, d4.name AS attribute_name, d5.name AS type_name, d6.name AS category_name, d7.unit_price, d1.product_id, d1.unit_id, d1.type_id, d1.attribute_id FROM  product_count d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d3 ON(d1.unit_id = d3.id) LEFT OUTER JOIN attribute d4 ON(d1.attribute_id= d4.id) LEFT OUTER JOIN product_type d5 ON(d1.type_id = d5.id) LEFT OUTER JOIN product_category d6 ON(d2.category_id = d6.id) LEFT OUTER JOIN product_price d7 ON(d1.product_id = d7.product_id AND d1.unit_id = d7.unit_id AND d1.attribute_id = d7.attribute_id AND d1.type_id = d7.type_id AND d7.status =0) WHERE d1.status =0 AND d2.publish=1 AND d6.status =0";

$sql = $sql." AND d1.quantity>0";
$sql = $sql." ORDER BY d1.create_date DESC";



$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);


?>


 <strong><?php echo $report_name?></strong><br>
 
	
	<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:80px"><strong>Mã sản phẩm </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Tên sản phẩm </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Đơn vị tính </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Đơn giá</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Quy cách </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Hướng  </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Nhóm</strong></td>
    </tr>
  
	<?php
	$no = 0;
	

	
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$unit_name = $dt->getString($i, "unit_name ");
		$attribute_name = $dt->getString($i, "attribute_name");
		$type_name = $dt->getString($i, "type_name");
		$quantity = $dt->getFloat($i, "quantity");
		$category_name = $dt->getString($i, "category_name");
		$unit_price = $dt->getFloat($i, "unit_price");
		
		$product_id = $dt->getString($i, "product_id");
		$unit_id = $dt->getString($i, "unit_id");
		$attribute_id = $dt->getString($i, "attribute_id");
		$type_id = $dt->getString($i, "type_id");
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td align="center" style="vertical-align:middle"><a href="<?php echo URL;?>api/action/?action_id=b5437b9b-4b93-41fc-f86a-df51a0cb809b&data=<?php echo $appSession->getTool()->urlEncode("product_id=".$product_id."&attribute_id=".$attribute_id."&type_id=".$type_id."&unit_id=".$unit_id);?>" target="_blank"><?php echo $quantity;?></a></td>
		<td align="right" style="vertical-align:middle"><?php echo $unit_price;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $type_name;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $category_name;?></td>
		
	  </tr>
	<?php
	}
	
	?>
</table>
