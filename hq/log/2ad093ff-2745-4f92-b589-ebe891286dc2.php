<?php 
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
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

$sql = "SELECT d3.id, d3.code, d3.name, SUM(d1.amount) AS amount FROM  wallet d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.rel_id = d3.id)  WHERE d1.status =0 AND d2.status =0 AND d1.description ILIKE '%DEPOSIT%' AND d1.factor = 1";

$sql = $sql. " GROUP BY d3.id, d3.code, d3.name ORDER BY d3.code ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);


$sql = "SELECT d3.id,  SUM(d1.amount) AS amount FROM  wallet d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.rel_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d1.factor=-1  GROUP BY d3.id";


$msg->add("query", $sql);
$dt_withdraw = $appSession->getTier()->getTable($msg);


?>


	
<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td  align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Mã khách </strong></td>
    <td align="center" ><strong>Tên khách </strong></td>
    <td  align="center"><strong>Nhập</strong></td>
    <td  align="center"><strong>Xuất</strong></td>
    <td  align="center"><strong>Tồn</strong></td>
  </tr>
	<?php
	$no = 0;
	$total_a = 0;
	$total_b = 0;
	$total_c = 0;
	$total_d = 0;
	
	for($i =0; $i< $dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$id = $dt->getString($i, "id");
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$openning = $dt->getFloat($i, "amount");
		$withdraw = 0;
		for($j =0; $j<$dt_withdraw->getRowCount(); $j++)
		{
			if($dt_withdraw->getString($j, "id") == $id)
			{
				$withdraw = $dt_withdraw->getFloat($j, "amount");
				break;
			}
		}
		$end = $openning  - $withdraw;
		$total_a = $total_a + $openning;
		$total_b = $total_b + $withdraw;
		$total_c = $total_c + $end;
		
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($openning);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($withdraw);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($end);?></td>
	</tr>
	<?php
	}
	
	?>
	<tr>
		<td colspan="2" style="vertical-align:middle"><strong>Tổng cộng: </strong></td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_a);?></strong></td>
        <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_b);?></strong></td>
        <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_c);?></strong></td>
  </tr>
</table>
