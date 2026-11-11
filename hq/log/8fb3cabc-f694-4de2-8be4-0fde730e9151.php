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

$sql = "SELECT  d2.receipt_no, d2.receipt_date, d3.code, d3.name, d3.phone, d3.email,  d3.address AS address, d4.code AS product_code, d5.name AS product_name, d5.name AS unit_name, d1.quantity, d1.unit_price, (d1.quantity * d1.unit_price) AS amount, d6.name AS company_name FROM  purchase_receive_product d1 LEFT OUTER JOIN purchase_receive d2 ON(d1.receive_id = d2.id) LEFT OUTER JOIN supplier d3 ON(d2.supplier_id = d3.id) LEFT OUTER JOIN product d4 ON(d1.product_id = d4.id) LEFT OUTER JOIN product_unit d5 ON(d1.unit_id = d5.id) LEFT OUTER JOIN res_company d6 ON(d2.company_id = d6.id)   WHERE d1.status =0 AND d2.status =0";
if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d3.code", "d3.name", "d3.phone", "d3.email", "d3.address"], $search).")";
}

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

$sql = $sql." ORDER BY d2.receipt_date ASC, d4.code ASC";

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
    <td  align="center" nowrap style="width:60px"><strong>Ng&agrave;y h&#7841;ch to&aacute;n</strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Ng&agrave;y ch&#7913;ng t&#7915;</strong></td>
    <td  align="center" nowrap style="width:60px"><strong>S&#7889; phi&#7871;u nh&#7853;p</strong></td>
    <td align="center" nowrap style="width:60px"> <strong>M&atilde; nh&agrave; cung c&#7845;p </strong></td>
	<td align="center" nowrap ><strong>T&ecirc;n nh&agrave; cung c&#7845;p</strong></td>
	<td align="center" nowrap ><strong>Di&#7877;n gi&#7843;i</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Điện thoại </strong></td>
	<td align="center" nowrap style="width:60px"><strong>M&atilde; h&agrave;ng</strong></td>
	<td align="center" nowrap style="width:60px"><strong>T&ecirc;n h&agrave;ng</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Kho</strong></td>
	<td align="center" nowrap style="width:60px"><strong>S&#7889; l&#432;&#7907;ng </strong></td>
	<td align="center" nowrap style="width:60px"><strong>&#272;&#417;n gi&aacute; </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Th&agrave;nh ti&#7873;n </strong></td>
	<td align="center" nowrap style="width:60px"><strong>Email</strong></td>
	<td align="center" nowrap style="width:250px"><strong>Địa chỉ </strong></td>
	<td  align="center" nowrap style="width:200px"><strong>Ghi chú </strong></td>
  </tr>
 
	<?php
	$no = 0;
	$totalAmount = 0;
	$totalDiscount = 0;
	$totalService = 0;
	$totalTax = 0;
	$total_quantity = 0;
	
	
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
	
		$quantity = $dt->getFloat($i, "quantity");
		$unit_price = $dt->getFloat($i, "unit_price");
		$amount = $dt->getFloat($i, "amount");
		$discount = $dt->getFloat($i, "discount");
		$service = $dt->getFloat($i, "service");
		$tax = $dt->getFloat($i, "tax");
		$total = $amount - $discount + $service + $tax;
		$total_quantity = $total_quantity + $quantity;
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$phone = $dt->getString($i, "phone");
		$email = $dt->getString($i, "email");
		$address = $dt->getString($i, "address");
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$unit_name = $dt->getString($i, "unit_name");
		$company_name = $dt->getString($i, "company_name");
		
		$totalAmount = $totalAmount + $amount;
		
	
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td align="left" style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td align="left" style="vertical-align:middle"><?php echo $receipt_no;?></td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle">Mua hàng <?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $phone;?></td>
		<td style="vertical-align:middle"><?php echo $product_code;?></td>
		<td style="vertical-align:middle"><?php echo $product_name;?></td>
		<td style="vertical-align:middle"><?php echo $company_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($unit_price);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td style="vertical-align:middle"><?php echo $email;?></td>
		<td style="vertical-align:middle"><?php echo $address;?></td>
		<td style="text-align: left;"> </td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	?>
	<tr>
		<td colspan="11" style="vertical-align:middle"><strong>Tổng cộng:</strong> </td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($total_quantity);?></td>
	    <td align="right" style="vertical-align:middle">&nbsp;</td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($total);?></td>
	    <td style="vertical-align:middle">&nbsp;</td>
	    <td style="vertical-align:middle">&nbsp;</td>
	    <td style="vertical-align:middle">&nbsp;</td>
	</tr>
</table>
