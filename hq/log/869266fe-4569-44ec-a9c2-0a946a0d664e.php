<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$company_id =  $appSession->getConfig()->getProperty("company_id");
$category_id = $report->getParamValueCaption("Category");
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

$sql = "SELECT d1.product_id, d1.sale_id, d2.code, d2.name, d4.name AS unit_name, d3.receipt_no, d3.receipt_date, d1.quantity, d5.code AS customer_code, d5.name AS customer_name FROM sale_product_local d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN sale_local d3 ON(d1.sale_id = d3.id) LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id) LEFT OUTER JOIN customer d5 ON(d3.customer_id = d5.id) WHERE d1.status =0 AND d2.status =0 AND d3.category_id ='8176454a-ccb1-48b4-d24f-580ccea50166'";

$sql = $sql." AND d3.receipt_date>='".$fdate."'";
$sql = $sql." AND d3.receipt_date<='".$tdate."'";
$company_ids = "";
for($i =0; $i<$dt_company->getRowCount(); $i++)
{
	if($company_ids != ""){
		$company_ids = $company_ids." OR ";
	}
	$id = $dt_company->getString($i, "id");
	$company_ids = $company_ids." d3.company_id='$id'";
}
$sql = $sql." AND (".$company_ids.") ";
$sql = $sql." ORDER BY d3.receipt_date ASC, d2.code ASC";
$msg->add("query", $sql);

$dt = $appSession->getTier()->getTable($msg);



?>


 <strong><?php echo $report_name?></strong><br>
 <strong>Công ty: <?php echo $company_names;?></strong><br>
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
	    <td align="center"  nowrap style="width:20px"><strong>Stt</strong></td>
	    <td align="center" nowrap  style="width:40px"><strong>Số</strong></td>
	    <td align="center" nowrap  style="width:40px"><strong>Ngày</strong></td>
	    <td align="center" nowrap  style="width:40px"><strong>Mã</strong></td>
	    <td align="center"  nowrap><strong>Tên</strong></td>
        <td align="center"  nowrap style="width:40px"><strong>Đơn vị tính </strong></td>
	  
	    <td align="center"  nowrap style="width:40px"><strong>Số lượng </strong></td>
	
	    <td align="center"  nowrap style="width:200px"><strong>Khách hàng </strong></td>
	  </tr>
	 
	  <?php
	   $no = 0;
	  for($j =0; $j<$dt->getRowCount(); $j++)
	  {
	
		
		$product_id = $dt->getString($j, "product_id");
		$sale_id = $dt->getString($j, "sale_id");
		$receipt_no = $dt->getString($j, "receipt_no");
		$receipt_date = $dt->getString($j, "receipt_date");
		$code = $dt->getString($j, "code");
		$name = $dt->getString($j, "name");
		$code = $dt->getString($j, "code");
		$unit_name = $dt->getString($j, "unit_name");
		$quantity = $dt->getFloat($j, "quantity");
		$customer_code = $dt->getString($j, "customer_code");
		$customer_name = $dt->getString($j, "customer_name");
		$no = $no + 1;
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		
	  ?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><?php echo $receipt_no;?></td>
		<td  style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td  style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $name;?></td>
        <td style="vertical-align:middle" nowrap="nowrap"><?php echo $unit_name;?></td>
	    <td align="right" nowrap="nowrap" style="vertical-align:middle"><a href="<?php echo URL;?>api/action/?action_id=346d9190-8485-474e-a0ad-d437efe1c9a6&data=<?php echo $appSession->getTool()->urlEncode("product_id=".$product_id."&sale_id=".$sale_id);?>" target="_blank"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></a></td>
	
	    <td nowrap="nowrap" style="vertical-align:middle"><?php echo $customer_code;?>. <?php echo $customer_name;?></td>
	  </tr>
	  <?php
	  }
	  ?>
</table>

