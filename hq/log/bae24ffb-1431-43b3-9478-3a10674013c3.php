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

$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d13.code AS product_code, d13.name AS product_name, d14.name AS unit_name, d15.name AS attribute_name, d16.name AS type_name, d13.category_id, d17.sequence AS category_sequence, d17.code AS category_code, d17.name AS category_name, d18.sequence AS category_type_sequence, d18.code AS category_type_code, d18.name AS category_type_name, d1.discount_id, d19.name AS discount_name, p.id, p.name AS company_name, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount, SUM(d1.discount_amount) AS discount, SUM(d1.service_amount) AS service, SUM(d1.tax_amount) AS tax FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN product d13 ON(d1.product_id = d13.id) LEFT OUTER JOIN product_unit d14 ON(d1.unit_id = d14.id) LEFT OUTER JOIN attribute d15 ON(d1.attribute_id= d15.id) LEFT OUTER JOIN product_type d16 ON(d1.type_id = d16.id) LEFT OUTER JOIN product_category d17 ON(d13.category_id = d17.id) LEFT OUTER JOIN product_category d18 ON(d17.parent_id = d18.id) LEFT OUTER JOIN account_service d19 ON(d1.discount_id = d19.id) LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) LEFT OUTER JOIN res_company p ON(d2.company_id = p.id) LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id) WHERE d1.status =0 AND d2.status =0";

$sql = $sql." AND s.start_date>='".$fdate."'";
$sql = $sql." AND s.start_date<='".$tdate."'";

if($customer_category_id != "")
{
	$sql = $sql." AND d3.category_id ='".$customer_category_id."'";
}

if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d2.receipt_no", "d3.code", "d3.name", "d13.code", "d13.name"], $search).")";
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
$sql = $sql." GROUP BY  d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d13.code , d13.name , d14.name, d15.name, d16.name , d13.category_id, d17.sequence, d17.code, d17.name , d18.sequence , d18.code, d18.name, d1.discount_id, d19.name, p.id, p.name, d1.unit_price";
$sql = $sql." ORDER BY d17.sequence ASC, d13.name ASC";


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
    <td align="center" nowrap style="width:60px"><strong>Quy cách </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Định lượng </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Hướng</strong></td>
	 <td align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
	 <td align="center" nowrap style="width:60px"><strong>Đơn giá trước chiết khẩu </strong></td>
	 
	 <td align="center" nowrap style="width:60px"><strong>Đơn giá sau chiết khẩu </strong></td>
	 <td align="center" nowrap style="width:60px"><strong>Thành tiền trước chiết khấu </strong></td>
	
	<td align="center" nowrap style="width:60px">Chiết khấu</strong></td>

	 <td align="center" nowrap style="width:60px"><strong>Thành tiền sau chiết khấu </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Phí</strong></td>
     <td align="center" nowrap style="width:60px"><strong>Thuế</strong></td>
     <td align="center" nowrap style="width:60px"><strong>Tổng </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Nhóm</strong></td>
     <td align="center" nowrap style="width:60px"><strong>Loại</strong></td>
     <td align="center" nowrap style="width:60px"><strong>Cửa hàng</strong></td>
	 <td align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  
	<?php
	$no = 0;
	$totalAmount = 0;
	$totalDiscount = 0;
	$totalService = 0;
	$totalTax = 0;
	
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
		$type_id = $dt->getString($i, "type_id");
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
		$category_name = $dt->getString($i, "category_name");
		$category_type_name = $dt->getString($i, "category_type_name");
		$company_name = $dt->getString($i, "company_name");
		
		$totalAmount = $totalAmount + $amount;
		$totalDiscount = $totalDiscount + $discount;
		$totalService = $totalService + 0;
		$totalTax = $totalTax + $tax;
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td style="vertical-align:middle"><?php echo $type_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price_discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		
		<td align="right" nowrap style="width:60px"><?php echo $appSession->getFormats()->getINT()->format($discount);?></td>
		<?php
		
		$total = $amount - $discount + $service + $tax;
		?>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount-$discount);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($service);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($tax);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
	    <td style="vertical-align:middle"><?php echo $category_name;?></td>
	    <td style="vertical-align:middle"><?php echo $category_type_name;?></td>
	    <td style="vertical-align:middle"><?php echo $company_name;?> </td>
		<td style="vertical-align:middle"> </td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	?>
	<tr>
		<td colspan="6" style="vertical-align:middle"><strong>Tổng cộng: </strong><strong></strong></td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
	
		<td align="right" nowrap style="width:60px"><strong><?php echo $appSession->getFormats()->getINT()->format($totalDiscount);?></strong>
		</td>
		<?php
		$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
		?>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount-$totalDiscount);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalService);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalTax);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
	    <td style="vertical-align:middle"> </td>
	    <td style="vertical-align:middle"> </td>
	    <td style="vertical-align:middle"> </td>
		<td style="vertical-align:middle"> </td>
	</tr>
</table>
