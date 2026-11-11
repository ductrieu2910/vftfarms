<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$customer_category_id = $report->getParamValueCaption("Category");
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
$company_id = $report->getParamValueCaption("Company");

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

$sql = "SELECT d1.product_id, d3.unit_id, d3.code AS product_code, d3.name AS product_name, d4.name AS unit_name, d1.attribute_id, d5.name AS attribute_name, d2.company_id, d6.name AS company_name, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount, SUM(d1.discount_amount) AS discount, SUM(d1.service_amount) AS service, SUM(d1.tax_amount) AS tax FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d3.unit_id = d4.id)  LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) LEFT OUTER JOIN attribute d5 ON(d1.attribute_id = d5.id) LEFT OUTER JOIN res_company d6 ON(d2.company_id = d6.id) WHERE d1.status =0 AND d2.status =0";

$sql = $sql." AND s.start_date>='".$fdate."'";
$sql = $sql." AND s.start_date<='".$tdate."'";


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
$sql = $sql." GROUP BY  d1.product_id, d3.unit_id, d3.code, d3.name, d4.name, d1.attribute_id, d5.name, d2.company_id, d6.name";
$sql = $sql." ORDER BY d3.code, d3.name";


$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);




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

  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:80px"><strong>Mã SP   </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Tên SP </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Đơn vị tính </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Hướng</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
	 <td align="center" nowrap style="width:60px"><strong>Đơn giá </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Đơn giá sau thuế </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
     <td align="center" nowrap style="width:60px"><strong>V&#7883; tr&iacute; </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  
	<?php
	$no = 0;
	$total = 0;
	$total_sale = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$unit_name = $dt->getString($i, "unit_name");
		$attribute_name = $dt->getString($i, "attribute_name");
		$type_name = $dt->getString($i, "type_name");
		$product_id = $dt->getString($i, "product_id");
		$unit_id = $dt->getString($i, "unit_id");
		$attribute_id = $dt->getString($i, "attribute_id");
		$discount_id = $dt->getString($i, "discount_id");
		$attribute_name = $dt->getString($i, "attribute_name");
		$company_name = $dt->getString($i, "company_name");
		
		$quantity = $dt->getFloat($i, "quantity");
		if($quantity == 0)
		{
			continue;
		}
		$amount = $dt->getFloat($i, "amount");
		$discount = $dt->getFloat($i, "discount");
		$service = $dt->getFloat($i, "service");
		$tax = $dt->getFloat($i, "tax");
		$unit_price = $amount/$quantity;
		$unit_price_discount = ($amount-$discount)/$quantity;
		$amount_sale = $quantity * $unit_price_discount;
		$category_name = $dt->getString($i, "category_name");
		$category_type_name = $dt->getString($i, "category_type_name");
		$company_name = $dt->getString($i, "company_name");
		$unit_price = $unit_price_discount/1.05;
		$amount = $quantity * $unit_price;
	
		$total = $total + $amount;
		$total_sale = $total_sale + $amount_sale;
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price_discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount_sale);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>

		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
	    <td style="vertical-align:middle"><?php echo $company_name;?></td>
	    <td style="vertical-align:middle"> </td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td colspan="5" style="vertical-align:middle"><strong>Tổng cộng: </strong><strong></strong></td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_sale);?></strong></td>
		<td align="right" style="vertical-align:middle"> </td>
		
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
	    <td align="right" style="vertical-align:middle">&nbsp;</td>
	    <td style="vertical-align:middle"> </td>
	</tr>
</table>
