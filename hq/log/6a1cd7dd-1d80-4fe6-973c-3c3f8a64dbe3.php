<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$company_id = $report->getParamValueCaption("Company");
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

$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d2.code, d2.name, d4.code AS product_code, d4.name AS product_name, d5.name AS warehouse_name, d6.version, d7.user_name, d7.name AS account_name FROM res_receipt d1 LEFT OUTER JOIN product_classification d2 ON(d1.res_id = d2.id) LEFT OUTER JOIN stock_product d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN product d4 ON(d3.product_id = d4.id) LEFT OUTER JOIN stock_warehouse d5 ON(d1.rel_id = d5.id) LEFT OUTER JOIN res_version d6 ON(d1.id = d6.rel_id) LEFT OUTER JOIN res_user d7 ON(d1.create_uid = d7.id) WHERE d1.status =2 AND d2.id IS NOT NULL";
$sql = $sql." AND d1.receipt_date>='".$fdate."'";
$sql = $sql." AND d1.receipt_date<='".$tdate."'";
if($category_id != "")
{
	$sql = $sql." AND d2.category_id='".$category_id."'";
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
$sql = $sql." ORDER BY d1.receipt_date DESC";

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
    <td align="center" nowrap  style="width:40px"><strong>Số</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Ngày</strong></td>
    <td align="center" nowrap  style="width:60px"><strong>Mã</strong></td>
	  <td   align="center" nowrap><strong>Tên</strong></td>
	  <td align="center" nowrap  style="width:150px"><strong>Đối tượng</strong></td>
      <td align="center" nowrap  style="width:50px"><strong>Version</strong></td>
      <td align="center" nowrap  style="width:120px"><strong>Nhân viên </strong></td>
      <td colspan="2" align="center" nowrap  style="width:30px"><strong>Xuất</strong></td>
    <td align="center" nowrap  style="width:200px"><strong>Ghi chú</strong></td>
  </tr>
  <?php
  $no = 0;
  for($i =0; $i<$dt->getRowCount(); $i++)
  {
  	$id = $dt->getString($i, "id");
	$receipt_no = $dt->getString($i, "receipt_no");
	
	$receipt_date = $dt->getString($i, "receipt_date");
	$receipt_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($receipt_date));
	$name = $dt->getString($i, "name");
	$code = $dt->getString($i, "code");
	$no = $no + 1;

	$rel_name = $dt->getString($i, "product_name");
	if($rel_name != "")
	{
		$rel_name = $dt->getString($i, "product_code")." ". $rel_name;
	}else{
		$rel_name = $dt->getString($i, "warehouse_name");
	}
	$version = $dt->getString($i, "version");
	$user_name = $dt->getString($i, "user_name");
	$account_name = $dt->getString($i, "account_name");
  ?>
  
		
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>" target="_blank"><?php echo $receipt_no;?></a> </td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $receipt_date;?></td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $rel_name;?></td>
		 <td style="vertical-align:middle; text-align:center"><?php echo $version;?></td>
		 <td style="vertical-align:middle; text-align:left"><?php echo $user_name;?>. <?php echo $account_name;?></td>
		 <td style="vertical-align:middle; text-align:left"><a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>&view_type=excel" target="_blank">Excel</a></td>
		 <td style="vertical-align:middle; text-align:left"><a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>&view_type=word" target="_blank">Word</a></td>
		 <td style="vertical-align:middle"></td>
	  </tr>
	 
	  <?php
	  	
	  }
	 
	  ?>
</table>
