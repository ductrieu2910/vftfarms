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

$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d1.order_no, d1.cashier_date, d1.end_date, d2.name AS category_name, d3.code AS customer_code, d3.name AS customer_name, d4.name AS table_name, d5.name AS user_order_name, d6.start_date, p.name AS company_name FROM sale d1 LEFT OUTER JOIN sale_category d2 ON(d1.category_id = d2.id) LEFT OUTER JOIN customer d3 ON(d1.customer_id = d3.id) LEFT OUTER JOIN product_table d4 ON(d1.table_id = d4.id) LEFT OUTER JOIN res_user d5 ON(d1.order_user_id = d5.id) LEFT OUTER JOIN sale_shipping d6 ON(d1.id = d6.sale_id) LEFT OUTER JOIN  sale_shipping d9 ON(d1.id = d9.sale_id AND d9.status =0)  LEFT OUTER JOIN res_company p ON(d1.company_id = p.id) WHERE d1.status =0";

$sql = $sql." AND d9.start_date>='".$fdate."'";
$sql = $sql." AND d9.start_date<='".$tdate."'";
if($customer_category_id != "")
{
	$sql = $sql." AND d3.category_id ='".$customer_category_id."'";
}

if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.receipt_no", "d3.code", "d3.name"], $search).")";
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

$sql = "SELECT d1.id, d1.sale_id, d1.unit_id, d1.product_id, d3.code AS product_code, d3.name AS product_name, d6.id AS category_id, d6.name AS category_name, d7.id AS parent_category_id, d7.name AS parent_category_name, d1.currency_id, d1.quantity, (d9.dividend / nullif(d9.divisor, 0) * d1.quantity) as weight,  d1.unit_price, d4.name AS unit_name, d8.code AS currency_code, d1.description, d9.name AS attribute_name, d10.name AS type_name, d1.discount_amount AS discount, d1.tax_amount AS tax, d1.service_amount AS service FROM sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id) LEFT OUTER JOIN res_currency d5 ON(d1.currency_id = d5.id) LEFT OUTER JOIN product_category d6 ON(d3.category_id = d6.id) LEFT OUTER JOIN product_category d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN res_currency d8 ON(d1.currency_id = d8.id) LEFT OUTER JOIN attribute d9 ON(d1.attribute_id = d9.id) LEFT OUTER JOIN product_type d10 ON(d1.type_id = d10.id) LEFT OUTER JOIN  sale_shipping d11 ON(d2.id = d11.sale_id AND d11.status =0) WHERE d1.status =0 AND d2.status =0 ";
$sql = $sql." AND d11.start_date>='".$fdate."'";
$sql = $sql." AND d11.start_date<='".$tdate."'";
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


$sql = "SELECT d1.id, d3.quantity, (d3.quantity * d1.unit_price) AS amount FROM sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN sale_refund_product d3 ON(d1.id = d3.rel_id AND d3.status =0) LEFT OUTER JOIN sale_refund d4 ON(d3.refund_id = d4.id) LEFT OUTER JOIN  sale_shipping d11 ON(d2.id = d11.sale_id AND d11.status =0) WHERE d1.status =0 AND d2.status =0 AND d4.status=0 ";
$sql = $sql." AND d11.start_date>='".$fdate."'";
$sql = $sql." AND d11.start_date<='".$tdate."'";
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

$msg->add("query", $sql);
$dt_product_refund = $appSession->getTier()->getTable($msg);





$sql = "SELECT d1.payment_id, d1.line_id, d1.rel_id, d1.currency_id, d3.name AS payment_name, d1.amount FROM account_payment_line d1 LEFT OUTER JOIN sale d2 ON(d1.line_id = d2.id) LEFT OUTER JOIN account_payment d3 ON(d1.payment_id = d3.id) LEFT OUTER JOIN res_currency d4 ON(d1.currency_id = d4.id) LEFT OUTER JOIN  sale_shipping d9 ON(d2.id = d9.sale_id AND d9.status =0) WHERE d1.status =0 AND d2.status =0 ";
$sql = $sql." AND d9.start_date>='".$fdate."'";
$sql = $sql." AND d9.start_date<='".$tdate."'";
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

$sql = "SELECT d1.id, d30.unit_price, d31.receipt_no, d32.name, d32.code, d30.unit_id FROM sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id)  LEFT OUTER JOIN sale_shipping d11 ON(d2.id = d11.sale_id AND d11.status =0)  LEFT OUTER JOIN stock d13 ON(d2.id = d13.rel_id) LEFT OUTER JOIN stock_product d14 ON(d13.id = d14.stock_id AND d14.product_id = d1.product_id)  LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id) LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id)  LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id) LEFT OUTER JOIN stock_product d18 ON(d17.rel_id = d18.id) LEFT OUTER JOIN stock_product d19 ON(d18.rel_id = d19.id) LEFT OUTER JOIN stock_product d20 ON(d19.rel_id = d20.id) LEFT OUTER JOIN purchase_receive_product_local d30 ON(d14.rel_id = d30.id OR d15.rel_id = d30.id OR d16.rel_id = d30.id OR d17.rel_id = d30.id 
OR d18.rel_id = d30.id OR d19.rel_id = d30.id OR d20.id = d30.rel_id) LEFT OUTER JOIN purchase_receive_local d31 ON(d30.receive_id = d31.id) LEFT OUTER JOIN supplier d32 ON(d31.supplier_id = d32.id) WHERE d1.status =0 AND d2.status =0 ";
$sql = $sql." AND d11.start_date>='".$fdate."'";
$sql = $sql." AND d11.start_date<='".$tdate."'";
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

$msg->add("query", $sql);
$dt_stock = $appSession->getTier()->getTable($msg);

$payment_category = $appSession->getTool()->selectDistinctTable($dt_payment, ["payment_id", "payment_name"]);



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
    <td style="width:20px" align="center"><strong>Số chứng từ </strong></td>
    <td style="width:20px" align="center"><strong>Ngày chứng từ </strong></td>
    <td style="width:20px" align="center"><strong>Số đơn </strong></td>
    <td style="width:20px" align="center"><strong>Mã khách hàng </strong></td>
    <td style="width:20px" align="center"><strong>Tên khách hàng </strong></td>
    <td style="width:20px" align="center"><strong>Ngày giao </strong></td>
    <td style="width:20px" align="center"><strong>Cửa hàng </strong></td>
    <td  style="width:40px" align="center"><strong>Mã SP</strong></td>
    <td align="center"><strong>Tên</strong></td>
    <td  style="width:60px" align="center"><strong>Quy cách</strong></td>
    <td  style="width:60px" align="center"><strong>Trọng lượng</strong></td>
    <td  style="width:60px" align="center"><strong>Hướng</strong></td>
    <td  style="width:60px" align="center"><strong>Số lượng</strong></td>
    <td  style="width:60px" align="center"><strong>Số lượng (kg) </strong></td>
    <td  style="width:60px" align="center"><strong>Đơn giá </strong></td>
    <td  style="width:60px" align="center"><strong>Đơn giá (kg) </strong></td>
    <td  style="width:60px" align="center"><strong>Thành tiền</strong></td>
    <td  style="width:60px" align="center"><strong>Giảm gá</strong></td>
    <td style="width:60px" align="center"><strong>Phí</strong></td>
	<td style="width:60px" align="center"><strong>Thuế</strong></td>
	<td style="width:60px" align="center"><strong>Số lượng trả </strong></td>
	<td style="width:60px" align="center"><strong>Tiền trả </strong></td>
	<td style="width:60px" align="center"><strong>Tổng</strong></td>
	<td style="width:60px" align="center"><strong>H&igrave;nh th&#432;c thanh to&aacute;n </strong></td>
	<?php
	for($p =0; $p<$payment_category->getRowCount(); $p++)
	{
		$payment_name = $payment_category->getString($p, "payment_name");
	?>
	  <td align="center" nowrap style="width:60px"><strong><?php echo $payment_name;?></strong></td>
	<?php
	}
	?>
    <td align="center" nowrap style="width:60px"><strong>Giá nhập </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Vốn</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số nhập </strong></td>
    <td align="center" nowrap style="width:150px"><strong>Nhà cung cấp </strong></td>
    <td  style="width:200px" align="center"><strong>Ghi chú</strong></td>
  </tr>
  <?php
  $no = 0;
	$totalAmount = 0;
	$totalDiscount = 0;
	$totalService = 0;
	$totalTax = 0;
	$totalCost = 0;
	$totalQuantity =0;
	$totalWeight =0;
	$total_quantity_refund = 0;
	$total_quantity = 0;
	$total_amount_refund = 0;
	$total_quantity_weight = 0;
  for($i =0; $i<$dt_sale->getRowCount(); $i++)
  {
  	$sale_id = $dt_sale->getString($i, "id");
	$receipt_no = $dt_sale->getString($i, "receipt_no");
	$order_no = $dt_sale->getString($i, "order_no");
	$receipt_date = $dt_sale->getString($i, "receipt_date");
	$receipt_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($receipt_date));
	$customer_code = $dt_sale->getString($i, "customer_code");
	$customer_name = $dt_sale->getString($i, "customer_name");
	$category_name = $dt_sale->getString($i, "category_name");
	$company_name = $dt_sale->getString($i, "company_name");
	
	$delivery_date = $dt_sale->getString($i, "start_date");
	if($delivery_date != "")
	{
		$delivery_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($delivery_date));
	}
	
  ?>
 
		<?php
		$no = 0;
		
		$payment_ids = "";
		
		for($j =0; $j<$dt_product->getRowCount(); $j++)
		{
			if($sale_id == $dt_product->getString($j, "sale_id"))
			{
			$no = $no + 1;
			$sale_product_id = $dt_product->getString($j, "id");
			$product_code = $dt_product->getString($j, "product_code");
			$product_id = $dt_product->getString($j, "product_id");
			$unit_id = $dt_product->getString($j, "unit_id");
			$product_name = $dt_product->getString($j, "product_name");
			$unit_name = $dt_product->getString($j, "unit_name");
			$attribute_name = $dt_product->getString($j, "attribute_name");
			$type_name = $dt_product->getString($j, "type_name");
			$description = $dt_product->getString($j, "description");
			$quantity = $dt_product->getFloat($j, "quantity");
			$weight = $dt_product->getFloat($j, "weight");
			if($weight == 0)
			{
				$weight = $quantity;
			}
			$unit_price = $dt_product->getFloat($j, "unit_price");
			
			$factor = 1;
			if($weight == 0){
				$weight = $quantity;
			}
			if($weight != 0){
				$factor = $quantity/$weight;
			} 
			
			$quantity_refund = 0;
			$amount_refund = 0;
			for($jj =0; $jj<$dt_product_refund->getRowCount(); $jj++)
			{
				if($dt_product_refund->getString($jj, "id") == $sale_product_id)
				{
					$quantity_refund = $quantity_refund + $dt_product_refund->getFloat($jj, "quantity");
					$amount_refund = $amount_refund + $dt_product_refund->getFloat($jj, "amount");
					
				}
			}
			
			$unit_price_weight = $unit_price * $factor;
			$amount = $quantity * $unit_price;
			$discount = $dt_product->getFloat($j, "discount");
			$service = $dt_product->getFloat($j, "service");
			$tax = $dt_product->getFloat($j, "tax");
			$total = $amount - $discount + $service + $tax -$amount_refund;
			
			
			$total_quantity_refund = $total_quantity_refund + $quantity_refund;
			$total_quantity = $total_quantity + $quantity;
			$total_amount_refund = $total_amount_refund + $amount_refund;
			$total_quantity_weight = $total_quantity_weight + $weight;
		
			
			$totalAmount = $totalAmount + $amount;
			$totalDiscount = $totalDiscount + $discount;
			$totalService = $totalService + $service;
			$totalTax = $totalTax + $tax;
			$cost_price = 0;
			$receive_code = "";
			
			$supplier_name = "";
			for($jj =0; $jj<$dt_stock->getRowCount(); $jj++)
			{
				if($dt_stock->getString($jj, "id") == $sale_product_id)
				{
					$cost_price = $dt_stock->getFloat($jj, "unit_price");
					$cost_price = $appSession->getUnit()->convert($dt_stock->getString($jj, "unit_id"), $unit_id, $product_id, $cost_price);
					
					if($cost_price != 0){
						$receive_code = $dt_stock->getString($jj, "receipt_no");
						$supplier_name = $dt_stock->getString($jj, "code").". ".$dt_stock->getString($jj, "name");
					}
					break;
				}
			}
			$cost = $quantity * $cost_price;
			$totalCost = $totalCost + $cost;
			
			$totalQuantity = $totalQuantity + $quantity;
			$totalWeight = $totalWeight  + $weight;
			
			$totalAmount = $totalAmount  + $amount;
			
		?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td style="vertical-align:middle" align="center"><?php echo $receipt_no;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $receipt_date;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $order_no;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $customer_code;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $customer_name;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $delivery_date;?></td>
		<td style="vertical-align:middle" align="center"><?php echo $company_name;?></td>
		<td  style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td  style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td style="vertical-align:middle"><?php echo $type_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($weight);?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price_weight); ?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td align="right"  style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($service);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($tax);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity_refund);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount_refund);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
		<?php
		$payment_name = "";
		for($p =0; $p<$payment_category->getRowCount(); $p++)
		{
			$payment_id = $payment_category->getString($p, "payment_id");
			
			for($n =0; $n<$dt_payment->getRowCount(); $n++)
			{
				if($dt_payment->getString($n, "payment_id") == $payment_id && ($dt_payment->getString($n, "line_id") == $sale_id || $dt_payment->getString($n, "rel_id") == $sale_id))  {
				
				if($payment_name != "")
				{
					$payment_name = $payment_name.", ";
				}
					
				$payment_name = $payment_name.$dt_payment->getString($n, "payment_name");
				
				}
			}
		}
		?>
		
		<td align="left" style="vertical-align:middle"><?php echo $payment_name;?></td>
		<?php
		for($p =0; $p<$payment_category->getRowCount(); $p++)
		{
			$payment_id = $payment_category->getString($p, "payment_id");
			$payment_amount = 0;
			for($n =0; $n<$dt_payment->getRowCount(); $n++)
			{
				if($dt_payment->getString($n, "payment_id") == $payment_id && ($dt_payment->getString($n, "line_id") == $sale_id || $dt_payment->getString($n, "rel_id") == $sale_id))  {
				if($appSession->getTool()->indexOf($payment_ids, $payment_id)!=-1)
				{
					continue;
				}
				$payment_ids = $payment_ids.",".$payment_id;
				$payment_amount = $payment_amount + $dt_payment->getFloat($n, "amount");
				break;
				}
			}
		?>
		  <td align="right" nowrap style="width:60px"><?php echo $appSession->getFormats()->getINT()->format($payment_amount);?></td>
		<?php
		}
		?>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($cost_price);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($cost);?></td>
		<td align="left" style="vertical-align:middle"><?php echo $receive_code;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $supplier_name;?></td>
		<td style="vertical-align:middle"><?php echo $description;?></td>
	  </tr>
	  
	  <?php
	  	}
	  }
	
	  ?>
	  
	  
  <?php
  }
    $total = $totalAmount - $totalDiscount + $totalService + $totalTax - $total_amount_refund;
  ?>
  <tr >
	    <td colspan="13" align="center" style="vertical-align:middle"><strong>Tổng</strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($totalQuantity);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($totalWeight);?></strong></td>
	    <td align="right"  style="vertical-align:middle"> </td>
	    <td align="right"  style="vertical-align:middle"> </td>
	    <td align="right"  style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($totalAmount);?></strong></td>
	    <td align="right"  style="vertical-align:middle"> </td>
	    <td align="right" style="vertical-align:middle"> </td>
	    <td align="right" style="vertical-align:middle"> </td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($total_quantity_refund);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($total_amount_refund);?></strong></td>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($total);?></strong> </td>
		<td align="right" style="vertical-align:middle">&nbsp;</td>
		<?php
	for($p =0; $p<$payment_category->getRowCount(); $p++)
	{
			$payment_id = $payment_category->getString($p, "payment_id");
			$payment_id = $payment_category->getString($p, "payment_id");
			$payment_amount = 0;
			for($n =0; $n<$dt_payment->getRowCount(); $n++)
			{
				if($dt_payment->getString($n, "payment_id") == $payment_id)  {
				$payment_amount = $payment_amount + $dt_payment->getFloat($n, "amount");
				
					
				}
			}
			
	?>
	    <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getDOUBLE()->format($payment_amount);?></strong></td>
		<?php
		}
		?>
	    <td align="right" style="vertical-align:middle"> </td>
	    <td align="left" style="vertical-align:middle"> </td>
	    <td align="left" style="vertical-align:middle"> </td>
	    <td style="vertical-align:middle"> </td>
		<td style="vertical-align:middle"> </td>
      </tr>
</table>
