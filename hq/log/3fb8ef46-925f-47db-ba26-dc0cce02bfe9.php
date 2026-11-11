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

$sql = "SELECT  d3.code, d3.name, d3.phone, d3.email,  d3.address AS address, SUM(d1.quantity) AS quantity, SUM(d1.quantity * d1.unit_price) AS amount FROM  purchase_receive_product d1 LEFT OUTER JOIN purchase_receive d2 ON(d1.receive_id = d2.id) LEFT OUTER JOIN supplier d3 ON(d2.supplier_id = d3.id)   WHERE d1.status =0 AND d2.status =0";
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
$sql = $sql." GROUP BY  d3.code, d3.name, d3.phone, d3.email,  d3.address ";
$sql = $sql." ORDER BY d3.name ASC";

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
    <td  align="center" nowrap style="width:60px"><strong>Số lượng </strong></td>
    <td  align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
    <td align="center" nowrap style="width:60px"> <strong>Mã </strong></td>
	<td align="center" nowrap ><strong>Tên</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Điện thoại </strong></td>
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
	
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
	
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
		
		
		$totalAmount = $totalAmount + $amount;
		
	
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $phone;?></td>
		<td style="vertical-align:middle"><?php echo $email;?></td>
		<td style="vertical-align:middle"><?php echo $address;?></td>
		<td style="text-align: left;"> </td>
	</tr>
	<?php
	}
	$total = $totalAmount - $totalDiscount + $totalService + $totalTax;
	?>
	<tr>
		<td colspan="2" style="vertical-align:middle"><strong>Tổng cộng:</strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<td colspan="6" style="vertical-align:middle"> </td>
	  </tr>
</table>
