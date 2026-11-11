<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$customer_category_id = $report->getParamValueCaption("Category");
$search = $report->getParamValueCaption("Search");
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

$sql = "SELECT d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d13.code AS product_code, d13.name AS product_name, d14.name AS unit_name, d15.name AS attribute_name, d16.name AS type_name, d3.code, d3.name, d3.phone, d3.email,  d3.address AS address, d5.name AS ward_name, d6.name AS dist_name, d7.name AS city_name, d9.name AS delivery_name, d9.tel AS delivery_phone, d9.address AS delivery_address, d10.name AS ward_delivery_name, d11.name AS dist_delivery_name, d12.name AS city_delivery_name, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount, SUM(d1.discount_amount) AS discount, SUM(d1.service_amount) AS service, SUM(d1.tax_amount) AS tax FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id) LEFT OUTER JOIN res_address d5 ON(d3.address_id = d5.id) LEFT OUTER JOIN res_address d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_address d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN  sale_shipping d9 ON(d2.id = d9.sale_id AND d9.status =0) LEFT OUTER JOIN res_address d10 ON(d9.address_id = d10.id) LEFT OUTER JOIN res_address d11 ON(d10.parent_id = d11.id) LEFT OUTER JOIN res_address d12 ON(d11.parent_id = d12.id) LEFT OUTER JOIN product d13 ON(d1.product_id = d13.id) LEFT OUTER JOIN product_unit d14 ON(d1.unit_id = d14.id) LEFT OUTER JOIN attribute d15 ON(d1.attribute_id= d15.id) LEFT OUTER JOIN product_type d16 ON(d1.type_id = d16.id) WHERE d1.status =0 AND d2.status =0";

$sql = $sql." AND d9.start_date>='".$fdate."'";
$sql = $sql." AND d9.start_date<='".$tdate."'";


if($customer_category_id != "")
{
	$sql = $sql." AND d3.category_id ='".$customer_category_id."'";
}

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
$sql = $sql." GROUP BY d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d13.code , d13.name, d14.name, d15.name , d16.name, d3.code, d3.name, d3.phone, d3.email,  d3.address, d5.name, d6.name, d7.name , d9.name, d9.tel, d9.address, d10.name , d11.name , d12.name, d1.unit_price ";
$sql = $sql." ORDER BY d3.name ASC, d13.name ASC";

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
    <td rowspan="2" align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td rowspan="2" align="center" nowrap style="width:80px"><strong>Mã SP   </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Tên SP </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Quy cách </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Định lượng </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Hướng</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Giám giá </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Phí</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Thuế</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Tổng</strong></td>
    <td colspan="8" align="center" nowrap style="width:60px"><strong>Khách hàng </strong></td>
    <td colspan="6" align="center" nowrap style="width:60px"><strong>Thông tin giao hàng </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  <tr class="header">
    <td align="center" nowrap style="width:60px"> <strong>Mã </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Tên</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Email</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Địa chỉ </strong></td>
	<td align="center" nowrap ><strong>Phường</strong></td>
	<td align="center" nowrap  style="width:120px"><strong>Quận/Huyện</strong></td>
    <td align="center" nowrap style="width:120px"><strong>Tỉnh/Thành </strong></td>
    <td align="center" nowrap  style="width:200px"><strong>Người nhận </strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap  style="width:90px"><strong>Địa chỉ </strong></td>
	<td align="center" nowrap  style="width:90px"><strong>Phường</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Quận/Huyện</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Tỉnh/Thành </strong></td>
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
		$quantity = $dt->getFloat($i, "quantity");
		$amount = $dt->getFloat($i, "amount");
		$discount = $dt->getFloat($i, "discount");
		$service = $dt->getFloat($i, "service");
		$tax = $dt->getFloat($i, "tax");
		$total = $amount - $discount + $service + $tax;
		
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$phone = $dt->getString($i, "phone");
		$email = $dt->getString($i, "email");
		$address = $dt->getString($i, "address");
		$ward_name = $dt->getString($i, "ward_name");
		$dist_name = $dt->getString($i, "dist_name");
		$city_name = $dt->getString($i, "city_name");
		$delivery_name = $dt->getString($i, "delivery_name");
		$delivery_phone = $dt->getString($i, "delivery_phone");
		$delivery_address = $dt->getString($i, "delivery_address");
		$delivery_ward_name = $dt->getString($i, "ward_delivery_name");
		$delivery_dist_name = $dt->getString($i, "dist_delivery_name");
		$delivery_city_name = $dt->getString($i, "city_delivery_name");
		
		$totalAmount = $totalAmount + $amount;
		$totalDiscount = $totalDiscount + $discount;
		$totalService = $totalService + $service;
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
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($service);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($tax);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $phone;?></td>
		<td style="vertical-align:middle"><?php echo $email;?></td>
		<td style="vertical-align:middle"><?php echo $address;?></td>
		<td style="vertical-align:middle"><?php echo $ward_name;?></td>
		<td style="text-align: left;"><?php echo $dist_name;?></td>
		<td style="text-align: left;"><?php echo $city_name;?></td>
		<td style="text-align: left;"><?php echo $delivery_name;?></td>
		<td style="text-align: left;"><?php echo $delivery_phone;?></td>
		<td style="text-align: left;"><?php echo $delivery_address;?></td>
		<td style="text-align: left;"><?php echo $delivery_ward_name;?></td>
	    <td style="text-align: left;"><?php echo $delivery_dist_name;?></td>
	    <td style="text-align: left;"><?php echo $delivery_city_name;?></td>
	    <td style="text-align: left;"> </td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	?>
	<tr>
		<td colspan="7" style="vertical-align:middle"><strong>Tổng cộng:</strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalDiscount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalService);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalTax);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
		<td colspan="15" style="vertical-align:middle"> </td>
	  </tr>
</table>
