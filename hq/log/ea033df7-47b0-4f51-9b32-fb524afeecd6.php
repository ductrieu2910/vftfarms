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

$sql = "SELECT  d2.customer_id, d3.code, d3.name, d3.phone, d3.email,  d3.address AS address, d5.name AS ward_name, d6.name AS dist_name, d7.name AS city_name, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount, SUM(d1.discount_amount) AS discount, SUM(d1.service_amount) AS service, SUM(d1.tax_amount) AS tax FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id) LEFT OUTER JOIN res_address d5 ON(d3.address_id = d5.id) LEFT OUTER JOIN res_address d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_address d7 ON(d6.parent_id = d7.id)  LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) WHERE d1.status =0 AND d2.status =0";

$sql = $sql." AND s.start_date>='".$fdate."'";
$sql = $sql." AND s.start_date<='".$tdate."'";
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
$sql = $sql." GROUP BY  d2.customer_id, d3.code, d3.name, d3.phone, d3.email,  d3.address, d5.name, d6.name, d7.name ";
$sql = $sql." ORDER BY d3.name ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);


$sql = "SELECT  d2.customer_id,  SUM(d4.quantity) AS quantity, SUM(d1.unit_price * d4.quantity) AS amount FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id)  LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) LEFT OUTER JOIN sale_refund_product d4 ON(d1.id = d4.rel_id AND d4.status =0) LEFT OUTER JOIN sale_refund d5 ON(d4.refund_id=d5.id) WHERE d1.status =0 AND d2.status =0 AND d5.status =0";

$sql = $sql." AND s.start_date>='".$fdate."'";
$sql = $sql." AND s.start_date<='".$tdate."'";
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
$sql = $sql." GROUP BY  d2.customer_id";

$msg->add("query", $sql);
$dt_refund = $appSession->getTier()->getTable($msg);



$sql = "SELECT d2.customer_id, COUNT(d2.id) AS sales FROM sale d2 LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id)   LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) WHERE  d2.status =0";

$sql = $sql." AND s.start_date>='".$fdate."'";
$sql = $sql." AND s.start_date<='".$tdate."'";
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
$sql = $sql." GROUP BY  d2.customer_id";


$msg->add("query", $sql);
$dt_count = $appSession->getTier()->getTable($msg);

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
    <td  align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>S&#7889; l&#432;&#7907;ng tr&#7843; </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Số đơn</strong> </td>
    <td  align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Giám giá </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Phí</strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Thuế</strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Ti&#7873;n tr&#7843; </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Tổng</strong></td>
    <td align="center" nowrap style="width:60px"> <strong>Mã </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Tên</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Email</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Địa chỉ </strong></td>
	<td align="center" nowrap ><strong>Phường</strong></td>
	<td align="center" nowrap  style="width:120px"><strong>Quận/Huyện</strong></td>
    <td align="center" nowrap style="width:120px"><strong>Tỉnh/Thành </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
 
	<?php
	$no = 0;
	$totalAmount = 0;
	$totalDiscount = 0;
	$totalService = 0;
	$totalTax = 0;
	$total_refund = 0;
	$total_quantity_refund = 0;
	$total_quantity = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$customer_id = $dt->getString($i, "customer_id");
		$quantity = $dt->getFloat($i, "quantity");
		$amount = $dt->getFloat($i, "amount");
		$discount = $dt->getFloat($i, "discount");
		$service = $dt->getFloat($i, "service");
		$sales = 0;
		$tax = $dt->getFloat($i, "tax");
		$quantity_refund = 0;
		$amount_refund = 0;
		for($j =0; $j<$dt_refund->getRowCount(); $j++)
		{
			if($customer_id == $dt_refund->getString($j, "customer_id"))
			{
				$quantity_refund = $quantity_refund + $dt_refund->getFloat($j, "quantity");
				$amount_refund = $amount_refund + $dt_refund->getFloat($j, "amount");
			}
		}
		
		$total = $amount - $discount + $service + $tax - $amount_refund;
		
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$phone = $dt->getString($i, "phone");
		$email = $dt->getString($i, "email");
		$address = $dt->getString($i, "address");
		$ward_name = $dt->getString($i, "ward_name");
		$dist_name = $dt->getString($i, "dist_name");
		$city_name = $dt->getString($i, "city_name");
		
		$total_quantity = $total_quantity + $quantity;
		$total_quantity_refund = $total_quantity_refund + $quantity_refund;
		
		$totalAmount = $totalAmount + $amount;
		$totalDiscount = $totalDiscount + $discount;
		$totalService = $totalService + $service;
		$totalTax = $totalTax + $tax;
		$total_refund = $total_refund + $amount_refund;
		for($j =0; $j<$dt_count->getRowCount();$j++)
		{
			if($dt_count->getString($j, "customer_id") == $customer_id)
			{
				$sales =  $dt_count->getFloat($j, "sales");
			}
		}
	
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity_refund);?></td>
		<td align="center" style="vertical-align:middle"><?php echo $sales;?> </td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($service);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($tax);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount_refund);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $phone;?></td>
		<td style="vertical-align:middle"><?php echo $email;?></td>
		<td style="vertical-align:middle"><?php echo $address;?></td>
		<td style="vertical-align:middle"><?php echo $ward_name;?></td>
		<td style="text-align: left;"><?php echo $dist_name;?></td>
		<td style="text-align: left;"><?php echo $city_name;?></td>
		<td style="text-align: left;"> </td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax - $total_refund;
	?>
	<tr>
		<td style="vertical-align:middle"><strong>Tổng cộng:</strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_quantity);?></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_quantity_refund);?></td>
		<td align="right" style="vertical-align:middle">&nbsp;</td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalDiscount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalService);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalTax);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_refund);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
		<td colspan="9" style="vertical-align:middle"> </td>
	  </tr>
</table>
