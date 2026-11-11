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

$sql = "SELECT d1.product_id, d3.unit_id, d3.code AS product_code, d3.name AS product_name, d4.name AS unit_name, d1.attribute_id, d5.name AS attribute_name, d2.customer_id, d6.code AS customer_code, d6.name AS customer_name, (d1.quantity) AS quantity, (d1.quantity * d1.unit_price) AS amount, (d1.discount_amount) AS discount, (d1.service_amount) AS service, (d1.tax_amount) AS tax FROM  sale_product d1 LEFT OUTER JOIN sale d2 ON(d1.sale_id = d2.id) LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d3.unit_id = d4.id)  LEFT OUTER JOIN  sale_shipping s ON(d2.id = s.sale_id AND s.status =0) LEFT OUTER JOIN attribute d5 ON(d1.attribute_id = d5.id) LEFT OUTER JOIN customer d6 ON(d2.customer_id = d6.id) WHERE d1.status =0 AND d2.status =0";

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
$sql = $sql." ORDER BY d3.code, d3.name";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$dt_product = $appSession->getTool()->selectDistinctTable($dt, ["product_id", "unit_id", "product_code", "product_name", "unit_name", "unit_name", "attribute_id", "attribute_name"]);

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
     <td align="center" nowrap style="width:60px"><strong>M&atilde; kh&aacute;ch </strong></td>
     <td align="center" nowrap style="width:120px"><strong>T&ecirc;n kh&aacute;ch </strong></td>
     <td align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  
	<?php
	$no = 0;
	$total = 0;
	$total_sale = 0;
	for($i =0; $i<$dt_product->getRowCount(); $i++)
	{
		$no = $no + 1;
		$product_code = $dt_product->getString($i, "product_code");
		$product_name = $dt_product->getString($i, "product_name");
		$unit_name = $dt_product->getString($i, "unit_name");
		$attribute_name = $dt_product->getString($i, "attribute_name");
		$type_name = $dt_product->getString($i, "type_name");
		$product_id = $dt_product->getString($i, "product_id");
		$unit_id = $dt_product->getString($i, "unit_id");
		$attribute_id = $dt_product->getString($i, "attribute_id");
		$discount_id = $dt_product->getString($i, "discount_id");
		$attribute_name = $dt_product->getString($i, "attribute_name");
		$customer_id = $dt_product->getString($i, "customer_id");
		$customer_code = $dt_product->getString($i, "customer_code");
		$customer_name = $dt_product->getString($i, "customer_name");
		
		
		$quantity = 0;
		
		$amount = 0;
		$discount = 0;
		$service = 0;
		
		$quantity_zero = 0;
		$amount_zero = 0;
		$discount_zero = 0;
		$service_zero = 0;
		
		for($j =0; $j<$dt->getRowCount(); $j++)
		{
			if($dt->getString($j, "product_id") == $product_id
			&& $dt->getString($j, "unit_id") == $unit_id
			&& $dt->getString($j, "attribute_id") == $attribute_id
			&& $dt->getString($j, "customer_id") == $customer_id
			)
			{
				$a = $dt->getFloat($j, "quantity");
				$b = $dt->getFloat($j, "amount");
				$c = $dt->getFloat($j, "discount");
				if($b ==$c)
				{
					$quantity_zero = $quantity_zero + $a;
					$amount_zero = $amount_zero + $b;
					$discount_zero = $discount_zero + $c;
				}else{
					$quantity = $quantity + $a;
					$amount = $amount + $b;
					$discount = $discount + $c;
				}
				
			}
		}
		
	?>
	<?php
	if($quantity>0)
	{
		$unit_price = $amount/$quantity;
		$unit_price_discount = ($amount-$discount)/$quantity;
		$amount_sale = $quantity * $unit_price_discount;
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
	    <td align="left" style="vertical-align:middle"><?php echo $customer_code;?></td>
	    <td align="left" style="vertical-align:middle"><?php echo $customer_name;?></td>
	    <td style="vertical-align:middle"> </td>
	</tr>
	<?php
	}
	?>
	<?php
	if($quantity_zero>0)
	{
		$unit_price = $amount_zero/$quantity_zero;
		$unit_price_discount = ($amount_zero-$discount_zero)/$quantity_zero;
		$amount_sale = $quantity_zero * $unit_price_discount;
		$unit_price = $unit_price_discount/1.05;
		$amount = $quantity_zero * $unit_price;
	
		$total = $total + $amount;
		$total_sale = $total_sale + $amount_sale;
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $unit_name;?></td>
		<td style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity_zero);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price_discount);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount_sale);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>

		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
	    <td align="left" style="vertical-align:middle"><?php $customer_code;?></td>
	    <td align="left" style="vertical-align:middle"><?php $customer_name;?></td>
	    <td style="vertical-align:middle"> </td>
	</tr>
	<?php
	}
	?>
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
	    <td align="right" style="vertical-align:middle">&nbsp;</td>
	    <td style="vertical-align:middle"> </td>
	</tr>
</table>
