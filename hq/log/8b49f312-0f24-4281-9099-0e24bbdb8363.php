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

$sql = "SELECT date_part('year', d1.receipt_date) AS y, date_part('month', d1.receipt_date) AS m, date_part('day', d1.receipt_date) AS d, d3.id, d3.user_name AS code, d3.name, COUNT(d1.id) AS c FROM res_receipt d1 LEFT OUTER JOIN product_classification d2 ON(d1.res_id = d2.id) LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id) WHERE (d1.status =2 OR d1.status =3) AND d2.id IS NOT NULL";
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
$sql = $sql." GROUP BY 1, 2, 3, 4, 5, 6";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$dt_user = $appSession->getTool()->selectDistinctTable($dt, ["id", "code", "name"]);

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
	<table border="0" class="borderLine">

  <tr class="header">
    <td style="width:120px" align="center"><strong>Ngày</strong></td>
	<?php
	for($j=0; $j<$dt_user->getRowCount(); $j++)
	{
	?>
	<td  style="width:100px" align="center"><strong><?php echo $dt_user->getString($j, "code").". ". $dt_user->getString($j, "name");?></strong></td>
	<?php
	}
	?>
    <td align="center" style="width:100px"><strong>Tổng</strong></td>
  </tr>
  <?php
  $no = 0;
  $dDate = strtotime($fdate);
  $t = strtotime($tdate);
  $total = 0;
  while($dDate<$t)
  {
  	 $y = date("Y", $dDate);
	 $m = date("m", $dDate);
	 $d = date("d", $dDate);
	 $c = 0;
	 
	
  ?>
  
		
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($y."-".$m."-".$d));?></td>
		
		<?php
		$total = 0;
	for($j=0; $j<$dt_user->getRowCount(); $j++)
	{
		$c = 0;
		$classification_id = $dt_user->getString($j, "id");
		for($i =0; $i<$dt->getRowCount(); $i++)
		 {
			if(intval($dt->getString($i, "y")) == $y && intval($dt->getString($i, "m")) == $m && intval($dt->getString($i, "d")) == $d && $dt->getString($i, "id") == $classification_id)
			{
				$c = $c + intval($dt->getString($i, "c"));
			}
		 }
		  $total = $total + $c;
		 ?>
		 <td style="vertical-align:middle; text-align:center"><?php echo $c;?></td>
		 
		<?php
		}
		?>
		 <td style="vertical-align:middle; text-align:center"><strong><?php echo $total;?></strong></td>
	  </tr>
	 
	  <?php
	  	$dDate = strtotime($y."-".$m."-".$d."+ 1 days");
	  }
	 
	  ?>
	  <tr >
		<td style="vertical-align:middle" align="center"><strong>Tổng</strong></td>
		<?php
		$total = 0;
	for($j=0; $j<$dt_user->getRowCount(); $j++)
	{
		$c = 0;
		$classification_id = $dt_user->getString($j, "id");
		for($i =0; $i<$dt->getRowCount(); $i++)
		 {
			if($dt->getString($i, "id") == $classification_id)
			{
				$c = $c + intval($dt->getString($i, "c"));
			}
		 }
		  $total = $total + $c;
	 
	?>
	 <td style="vertical-align:middle; text-align:center"><strong><?php echo $c;?></strong></td>
	 <?php
	 }
	 ?>
		 <td style="vertical-align:middle; text-align:center"><strong><?php echo $total;?></strong></td>
	  </tr>
</table>
