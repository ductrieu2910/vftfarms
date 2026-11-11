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


$sql = "SELECT d2.receipt_date, d2.receipt_no, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d3.code AS product_code, d3.name AS product_name, d4.name AS unit_name,  d9.name AS attribute_name, d10.name AS type_name, d2.category_id, d11.name AS category_name, (d1.quantity) AS quantity, d12.name AS warehouse_name, d13.name AS company_name FROM stock_product d1 LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id) LEFT OUTER JOIN attribute d9 ON(d1.attribute_id = d9.id) LEFT OUTER JOIN product_type d10 ON(d1.type_id = d10.id) LEFT OUTER JOIN stock_category d11 ON(d2.category_id = d11.id) LEFT OUTER JOIN stock_product d111 ON(d1.rel_id= d111.id) LEFT OUTER JOIN stock_warehouse d12 ON(d111.warehouse_id = d12.id) LEFT OUTER JOIN res_company d13 ON(d2.company_id = d13.id) WHERE d1.status =0 ANd d2.status =0 AND d2.category_id ='ea95e897-f2d6-4e5c-84f0-05cd16882d16'";
$sql = $sql." AND d2.receipt_date>='".$fdate."'";
$sql = $sql." AND d2.receipt_date<='".$tdate."'";
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
$sql = $sql." ORDER BY d2.receipt_date ASC, d2.receipt_no ";


$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$dt_category = $appSession->getTool()->selectDistinctTable($dt, ["category_id", "category_name"]);



?>


 <strong><?php echo $report_name?></strong><br>
 <strong>Công ty: <?php echo $company_names;?></strong><br>
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

  <tr class="header">
    <td style="width:20px" align="center"><strong>Stt</strong></td>
    <td  style="width:40px" align="center"><strong>Mã SP</strong></td>
    <td align="center"><strong>Tên</strong></td>
    <td  style="width:60px" align="center"><strong>S&#7889; phi&#7871;u </strong></td>
    <td  style="width:60px" align="center"><strong>Ng&agrave;y</strong></td>
    <td  style="width:60px" align="center"><strong>Kho</strong></td>
    <td  style="width:60px" align="center"><strong>V&#7883; tr&iacute; </strong></td>
    <td  style="width:60px" align="center"><strong>&#272;&#417;n v&#7883; t&iacute;nh </strong></td>
    <td  style="width:60px" align="center"><strong>S&#7889; l&#432;&#7907;ng </strong></td>
    <td  style="width:60px" align="center"><strong>Thuộc tính</strong></td>
    <td  style="width:60px" align="center"><strong>Loại</strong></td>
  </tr>
  <?php
  $no = 0;
  $total = 0;
  for($i =0; $i<$dt->getRowCount(); $i++)
  {
		$product_id = $dt->getString($i, "product_id");
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$unit_name = $dt->getString($i, "unit_name");
		$attribute_name = $dt->getString($i, "attribute_name");
		$type_name = $dt->getString($i, "type_name");
		$unit_id = $dt->getString($i, "unit_id");
		$attribute_id = $dt->getString($i, "attribute_id");
		$type_id = $dt->getString($i, "type_id");
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		$warehouse_name = $dt->getString($i, "warehouse_name");
		$quantity = $dt->getFloat($i, "quantity");
		$company_name = $dt->getString($i, "company_name");
		$no = $no + 1;
	
		$total = $total + $quantity;
		
			
		?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $receipt_no;?></td>
		<td style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td style="vertical-align:middle"><?php echo $warehouse_name;?></td>
		<td style="vertical-align:middle"><?php echo $company_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $quantity;?></td>
		<td  style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td style="vertical-align:middle"><?php echo $type_name;?></td>
	  </tr>
	  <?php
	  	
	 
	}
	  ?>
	  <tr >
		<td colspan="8" align="center" style="vertical-align:middle"><strong>Tổng:</strong></td>
	    <td align="right" style="vertical-align:middle"><?php echo $total;?></td>
	    <td align="center" style="vertical-align:middle">&nbsp;</td>
	    <td align="center" style="vertical-align:middle">&nbsp;</td>
	  </tr>
</table>
