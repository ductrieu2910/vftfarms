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

$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date,  d3.code AS supplier_code, d3.name AS supplier_name, d3.address FROM purchase_return d1 LEFT OUTER JOIN supplier d3 ON(d1.supplier_id = d3.id)  WHERE d1.status =0";

$sql = $sql." AND d1.receipt_date>='".$fdate."'";
$sql = $sql." AND d1.receipt_date<='".$tdate."'";

if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.receipt_no", "d3.code", "d3.name", "d3.phone", "d3.email", "d3.address"], $search).")";
}



$company_ids = "";
for($i =0; $i<$dt_company->getRowCount(); $i++)
{
	if($company_ids != ""){
		$company_ids = $company_ids." OR ";
	}
	$id = $dt_company->getString($i, "id");
	$company_ids = $company_ids." d1.company_id='$id'";
}
$sql = $sql." AND (".$company_ids.") ";
$sql = $sql." ORDER BY d1.receipt_date ASC";

$msg->add("query", $sql);
$dt_sale = $appSession->getTier()->getTable($msg);

$sql = "SELECT d1.id, d1.return_id, d3.code AS product_code, d3.name AS product_name, d6.id AS category_id, d6.name AS category_name, d7.id AS parent_category_id, d7.name AS parent_category_name, d1.currency_id, d1.quantity,  d1.unit_price, d4.name AS unit_name, d8.code AS currency_code, d1.description, d9.name AS attribute_name, d10.name AS type_name FROM purchase_return_product d1 LEFT OUTER JOIN purchase_return d2 ON(d1.return_id = d2.id) LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id) LEFT OUTER JOIN res_currency d5 ON(d1.currency_id = d5.id) LEFT OUTER JOIN product_category d6 ON(d3.category_id = d6.id) LEFT OUTER JOIN product_category d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN res_currency d8 ON(d1.currency_id = d8.id) LEFT OUTER JOIN attribute d9 ON(d1.attribute_id = d9.id) LEFT OUTER JOIN product_type d10 ON(d1.type_id = d10.id) WHERE d1.status =0 AND d2.status =0 ";
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
$sql = $sql." ORDER BY d3.name ASC";

$msg->add("query", $sql);

$dt_product = $appSession->getTier()->getTable($msg);

$sql = "SELECT d1.line_id, d1.currency_id, d3.name AS payment_name, d1.amount FROM account_payment_line d1 LEFT OUTER JOIN purchase_return d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN account_payment d3 ON(d1.payment_id = d3.id) LEFT OUTER JOIN res_currency d4 ON(d1.currency_id = d4.id) WHERE d1.status =0 AND d2.status =0 ";
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
$sql = $sql." ORDER BY d3.name ASC";

$msg->add("query", $sql);
$dt_payment = $appSession->getTier()->getTable($msg);

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
    <td style="width:20px" align="center"><strong>Stt</strong></td>
    <td  style="width:40px" align="center"><strong>Mã SP</strong></td>
    <td align="center"><strong>Tên</strong></td>
    <td  style="width:60px" align="center"><strong>Quy cách</strong></td>
    <td  style="width:60px" align="center"><strong>Trọng lượng</strong></td>
    <td  style="width:60px" align="center"><strong>Hướng</strong></td>
    <td  style="width:60px" align="center"><strong>Số lượng</strong></td>
    <td  style="width:60px" align="center"><strong>Đơn giá </strong></td>
    <td  style="width:60px" align="center"><strong>Thành tiền</strong></td>
    <td style="width:60px" align="center"><strong>Tổng</strong></td>
    <td  style="width:200px" align="center"><strong>Ghi chú</strong></td>
  </tr>
  <?php
  for($i =0; $i<$dt_sale->getRowCount(); $i++)
  {
  	$return_id = $dt_sale->getString($i, "id");
	$receipt_no = $dt_sale->getString($i, "receipt_no");
	$order_no = $dt_sale->getString($i, "order_no");
	$receipt_date = $dt_sale->getString($i, "receipt_date");
	$receipt_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($receipt_date));
	$supplier_code = $dt_sale->getString($i, "supplier_code");
	$supplier_name = $dt_sale->getString($i, "supplier_name");
	$address = $dt_sale->getString($i, "address");
	
	
  ?>
  <tr >
    <td colspan="11" align="left" ><strong>Số chứng từ: <?php echo $receipt_no;?>; Ngày: <?php echo $receipt_date;?>; Mã nhà cung cấp: <?php echo $supplier_code;?>; Tên nhà cung cấp: <?php echo $supplier_name;?>; Địa chỉ: <?php echo $address;?></strong></td>
    </tr>
		<?php
		$no = 0;
		$totalAmount = 0;
	$totalDiscount = 0;
	$totalService = 0;
	$totalTax = 0;
	
		for($j =0; $j<$dt_product->getRowCount(); $j++)
		{
			if($return_id == $dt_product->getString($j, "return_id"))
			{
			$no = $no + 1;
			$product_code = $dt_product->getString($j, "product_code");
			$product_name = $dt_product->getString($j, "product_name");
			$unit_name = $dt_product->getString($j, "unit_name");
			$attribute_name = $dt_product->getString($j, "attribute_name");
			$type_name = $dt_product->getString($j, "type_name");
			$description = $dt_product->getString($j, "description");
			$quantity = $dt_product->getFloat($j, "quantity");
			$unit_price = $dt_product->getFloat($j, "unit_price");
			$amount = $quantity * $unit_price;
			$discount = $dt_product->getFloat($j, "discount");
			$service = $dt_product->getFloat($j, "service");
			$tax = $dt_product->getFloat($j, "tax");
			$total = $amount - $discount + $service + $tax;
			
			$totalAmount = $totalAmount + $amount;
			$totalDiscount = $totalDiscount + $discount;
			$totalService = $totalService + $service;
			$totalTax = $totalTax + $tax;
			
			
		?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td  style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td style="vertical-align:middle"><?php echo $type_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
		<td style="vertical-align:middle"><?php echo $description;?></td>
	  </tr>
	  <?php
	  	}
	  }
	  $total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	  ?>
	  <tr >
		<td colspan="8" align="left" style="vertical-align:middle"><strong>Tổng:</strong></td>
		<td align="right"  style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total);?></strong></td>
		<td style="vertical-align:middle"><strong></strong></td>
	  </tr>
	  <tr >
		<td colspan="11" align="left" style="vertical-align:middle"><strong>Thanh toán:</strong></td>
	  </tr>
	  <?php
	  for($j =0; $j<$dt_payment->getRowCount(); $j++)
		{
			if($return_id == $dt_payment->getString($j, "line_id"))
			{
			$payment_name = $dt_payment->getString($j, "payment_name");
			$amount = $dt_payment->getFloat($j, "amount");
		?>
	   <tr >
		<td colspan="9" align="right" style="vertical-align:middle"><strong><?php echo $payment_name;?>: </strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($amount);?></strong></td>
		<td style="vertical-align:middle"><strong></strong></td>
	  </tr>
	  <?php
	  	}
	  }
	  ?>
  <?php
  }
  ?>
</table>
