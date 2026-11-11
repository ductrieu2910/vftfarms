<?php 
$config = new AppConfig("");
$config->parseWith($data, '&');
$fdate = $config->getProperty("From Date");
$fdate = $appSession->getTool()->urlDecode($fdate);
$tdate = $config->getProperty("To Date");
$tdate = $appSession->getTool()->urlDecode($tdate);
$customer_id = $config->getProperty("id");
$fdate = $appSession->getTool()->replace($fdate, " 00:00:00.000", "");
$tdate = $appSession->getTool()->replace($tdate, " 00:00:00.000", "");
$index = $appSession->getTool()->indexOf($fdate, " ");

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

$sql = "SELECT d1.sale_id, d2.receipt_no, d2.receipt_date, d2.order_no, d2.order_date, d3.code, d3.name, d3.phone, d3.email,  d3.address AS address, d5.name AS ward_name, d6.name AS dist_name, d7.name AS city_name, d9.name AS delivery_name, d9.start_date, d9.tel AS delivery_phone, d9.address AS delivery_address, d10.name AS ward_delivery_name, d11.name AS dist_delivery_name, d12.name AS city_delivery_name, p.id, p.name AS company_name, SUM(d1.quantity * d1.unit_price) AS amount, SUM(d1.discount_amount) AS discount, SUM(d1.service_amount) AS service, SUM(d1.tax_amount) AS tax, d2.description, d9.description AS delivery_description FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.customer_id = d3.id) LEFT OUTER JOIN res_address d5 ON(d3.address_id = d5.id) LEFT OUTER JOIN res_address d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_address d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN  sale_shipping d9 ON(d2.id = d9.sale_id AND d9.status =0) LEFT OUTER JOIN res_address d10 ON(d9.address_id = d10.id) LEFT OUTER JOIN res_address d11 ON(d10.parent_id = d11.id) LEFT OUTER JOIN res_address d12 ON(d11.parent_id = d12.id) LEFT OUTER JOIN res_company p ON(d2.company_id = p.id)  WHERE d1.status =0 AND d2.status =0 AND d2.customer_id ='".$customer_id."'";

//$sql = $sql." AND d9.start_date>='".$fdate."'";
//$sql = $sql." AND d9.start_date<='".$tdate."'";
if($customer_category_id != "")
{
	$sql = $sql." AND d3.category_id ='".$customer_category_id."'";
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
$sql = $sql." GROUP BY d1.sale_id, d2.order_no, d2.order_date, d2.receipt_no, d2.receipt_date, d3.code, d3.name, d3.phone, d3.email,  d3.address, d5.name, d6.name, d7.name , d9.name, d9.tel, d9.address, d10.name , d11.name , d12.name, d9.start_date, d2.description,  d9.description, p.id, p.name";
$sql = $sql." ORDER BY d9.start_date ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$sql = "SELECT d1.line_id, d1.rel_id, d1.payment_id, d3.name, d3.sequence,  SUM(d1.amount) AS amount FROM  account_payment_line d1 LEFT OUTER JOIN sale d2 ON(d1.line_id = d2.id) LEFT OUTER JOIN account_payment d3 ON(d1.payment_id = d3.id)  LEFT OUTER JOIN  sale_shipping d9 ON(d2.id = d9.sale_id AND d9.status =0) WHERE d1.status =0 AND d2.status =0 AND d2.customer_id ='".$customer_id."'";
//$sql = $sql." AND d9.start_date>='".$fdate."'";
//$sql = $sql." AND d9.start_date<='".$tdate."'";

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
$sql = $sql." GROUP BY d1.line_id, d1.rel_id, d1.payment_id, d3.name, d3.sequence";
$sql = $sql." ORDER BY d3.sequence ASC, d3.name ASC";

$msg->add("query", $sql);
$dt_payment = $appSession->getTier()->getTable($msg);


$dt_payment_group = $appSession->getTool()->selectDistinctTable($dt_payment, ["payment_id", "name"]);

?>


 <strong><?php echo $report_name?></strong><br>
 

	<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td rowspan="2" align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td rowspan="2" align="center" nowrap style="width:80px"><strong>Số CT </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Ngày CT </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Số đặt </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Ngày đặt </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Giảm giá </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Phí</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Thuế</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Tổng</strong></td>
	<td colspan="<?php echo $dt_payment_group->getRowCount()+1;?>" align="center" nowrap style="width:60px"><strong>Thanh toán</strong></td>
    <td colspan="8" align="center" nowrap style="width:60px"><strong>Khách hàng </strong></td>
    <td colspan="8" align="center" nowrap style="width:60px"><strong>Thông tin giao hàng </strong></td>
	<td rowspan="2" align="center" nowrap  style="width:90px"><strong>Cửa hàng</strong></td>
    <td   rowspan="2" align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  <tr class="header">
  	<td   align="center" nowrap style="width:60px"><strong>Tổng thanh toán</strong></td>
	<?php
	for($j=0; $j<$dt_payment_group->getRowCount(); $j++)
	{
	?>
	<td   align="center" nowrap style="width:60px"><strong><?php echo $dt_payment_group->getString($j, "name");?></strong></td>
	<?php
	}
	?>
    <td align="center" nowrap style="width:60px"> <strong>Mã </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Tên</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Email</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Địa chỉ </strong></td>
	<td align="center" nowrap ><strong>Phường</strong></td>
	<td align="center" nowrap  style="width:120px"><strong>Quận/Huyện</strong></td>
    <td align="center" nowrap style="width:120px"><strong>Tỉnh/Thành</strong></td>
    <td align="center" nowrap  style="width:200px"><strong>Tên</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Ngày giao </strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap  style="width:90px"><strong>Địa chỉ </strong></td>
	<td align="center" nowrap  style="width:90px"><strong>Phường</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Quận</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Tỉnh/thành</strong></td>
    
	 <td align="center" nowrap  style="width:90px"><strong>Ghi chú </strong></td>
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
		$sale_id = $dt->getString($i, "sale_id");
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		
		$order_no = $dt->getString($i, "order_no");
		$order_date = $dt->getString($i, "order_date");
		$order_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($order_date));
		
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
		$delivery_date = $dt->getString($i, "start_date");
		$company_name = $dt->getString($i, "company_name");
		if($delivery_date != "")
		{
			$delivery_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($delivery_date));
		}
		$description = $dt->getString($i, "description");
		$delivery_description = $dt->getString($i, "delivery_description");
		$totalAmount = $totalAmount + $amount;
		$totalDiscount = $totalDiscount + $discount;
		$totalService = $totalService + $service;
		$totalTax = $totalTax + $tax;
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $receipt_no;?></td>
		<td style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td style="vertical-align:middle"><?php echo $order_no;?></td>
		<td style="vertical-align:middle"><?php echo $order_date;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($service);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($tax);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
		<?php
		$payment = 0;
		for($n =0; $n<$dt_payment->getRowCount(); $n++)
		{
			if( ($dt_payment->getString($n, "line_id") == $sale_id || $dt_payment->getString($n, "rel_id") == $sale_id))
			{
				$payment = $payment + $dt_payment->getFloat($n, "amount");
			}
		}
		?>
		<td align="right"><?php echo $appSession->getFormats()->getINT()->format($payment);?></td>
		<?php
			
		for($j=0; $j<$dt_payment_group->getRowCount(); $j++)
		{
			$payment = 0;
			$payment_id = $dt_payment_group->getString($j, "payment_id");
			
			for($n =0; $n<$dt_payment->getRowCount(); $n++)
			{
				if($dt_payment->getString($n, "payment_id") == $payment_id && ($dt_payment->getString($n, "line_id") == $sale_id || $dt_payment->getString($n, "rel_id") == $sale_id))
				{
					$payment = $payment + $dt_payment->getFloat($n, "amount");
				}
			}
		?>
		<td align="right"><?php echo $appSession->getFormats()->getINT()->format($payment);?></td>
		<?php
		}
		?>

	
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $phone;?></td>
		<td style="vertical-align:middle"><?php echo $email;?></td>
		<td style="vertical-align:middle"><?php echo $address;?></td>
		<td style="vertical-align:middle"><?php echo $ward_name;?></td>
		<td style="text-align: left;"><?php echo $dist_name;?></td>
		<td style="text-align: left;"><?php echo $city_name;?></td>
		<td style="text-align: left;"><?php echo $delivery_name;?></td>
		<td style="text-align: left;"><?php echo $delivery_date;?></td>
		<td style="text-align: left;"><?php echo $delivery_phone;?></td>
		<td style="text-align: left;"><?php echo $delivery_address;?></td>
		<td style="text-align: left;"><?php echo $delivery_ward_name;?></td>
	    <td style="text-align: left;"><?php echo $delivery_dist_name;?></td>
	    <td style="text-align: left;"><?php echo $delivery_city_name;?></td>
	    <td style="text-align: left;"><?php echo $delivery_description;?></td>
	    <td style="text-align: left;"><?php echo $company_name;?></td>
		<td style="text-align: left;"><?php echo $description;?></td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	?>
	<tr>
		<td colspan="5" style="vertical-align:middle"><strong>Tổng cộng: </strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalDiscount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalService);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalTax);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
		<?php
		$payment = 0;
		for($n =0; $n<$dt_payment->getRowCount(); $n++)
		{
			
			$payment = $payment + $dt_payment->getFloat($n, "amount");
		}
		?>
		<td align="right"><b><?php echo $appSession->getFormats()->getINT()->format($payment);?></b></td>
		<?php
		
		for($j=0; $j<$dt_payment_group->getRowCount(); $j++)
		{
			$payment = 0;
			$payment_id = $dt_payment_group->getString($j, "payment_id");
			
			for($n =0; $n<$dt_payment->getRowCount(); $n++)
			{
				if($dt_payment->getString($n, "payment_id") == $payment_id)
				{
					$payment = $payment + $dt_payment->getFloat($n, "amount");
				}
			}
		?>
		<td align="right"><b><?php echo $appSession->getFormats()->getINT()->format($payment);?></b></td>
		<?php
		}
		?>
		<td colspan="18" style="vertical-align:middle"> </td>
	  </tr>
</table>
