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

$sql = "SELECT d3.id, d3.code, d3.name, SUM(d1.factor * d1.amount) AS amount FROM  wallet d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.rel_id = d3.id)  WHERE d1.status =0 AND d2.status =0 AND d1.create_date<'".$fdate."'";

$sql = $sql. " GROUP BY d3.id, d3.code, d3.name ORDER BY d3.code ASC";

$msg->add("query", $sql);
$dt_openning = $appSession->getTier()->getTable($msg);


$sql = "SELECT d3.id, d3.code, d3.name, d1.factor, SUM(d1.amount) AS amount FROM  wallet d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.rel_id = d3.id)  WHERE d1.status =0 AND d2.status =0 AND d1.create_date>='".$fdate."' AND d1.create_date<='".$tdate."'";

$sql = $sql. " GROUP BY d3.id, d3.code, d3.name, d1.factor ORDER BY d3.code ASC";

$msg->add("query", $sql);
$dt_wallet = $appSession->getTier()->getTable($msg);

$dt_customer = $appSession->getTool()->selectDistinctTable($dt_openning, ["id", "code", "name"]);
$dt1 = $appSession->getTool()->selectDistinctTable($dt_wallet, ["id", "code", "name"]);

for($i =0; $i<$dt1->getRowCount(); $i++)
{
	$hasItem = false;
	$id = $dt1->getString($i, "id");
	for($j =0; $j<$dt_customer->getRowCount(); $j++)
	{
		if($dt_customer->getString($j, "id") == $id)
		{
			$hasItem = true;
			break;
		}
	}
	if($hasItem == false)
	{
		$dt_customer->addArray($dt1->getDataRow($i));
	}
}

?>


	
<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td  align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Mã khách </strong></td>
    <td align="center" ><strong>Tên khách </strong></td>
    <td  align="center"><strong>&#272;&#7847;u k&#7923; </strong></td>
    <td  align="center">Nh&#7853;p</td>
    <td  align="center">Xu&#7845;t</td>
    <td  align="center">T&#7891;n</td>
  </tr>
	<?php
	$no = 0;
	$total_a = 0;
	$total_b = 0;
	$total_c = 0;
	$total_d = 0;
	
	for($i =0; $i<$dt_customer->getRowCount(); $i++)
	{
		$no = $no + 1;
		$id = $dt_customer->getString($i, "id");
		$code = $dt_customer->getString($i, "code");
		$name = $dt_customer->getString($i, "name");

		$openning = 0;
		for($j =0; $j<$dt_openning->getRowCount(); $j++)
		{
			if($dt_openning->getString($j, "id") == $id)
			{
				$openning = $dt_openning->getFloat($j, "amount");
				break;
			}
		}
		$in = 0;
		$out = 0;
		for($j =0; $j<$dt_wallet->getRowCount(); $j++)
		{
			if($dt_wallet->getString($j, "id") == $id)
			{
				$factor = $dt_wallet->getFloat($j, "factor");
				$amount = $dt_wallet->getFloat($j, "amount");
				if($factor>=0)
				{
					$in = $in + $amount;
				}else
				{
					$out = $out + $amount;
				}
			}
		}
		
		$end = $openning + $in - $out;
		
		$total_a = $total_a + $openning;
		$total_b = $total_b + $in;
		$total_c = $total_c + $out;
		$total_d= $total_d + $end;
		
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($openning);?></td>

	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($in);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($out);?></td>
	    <td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($end);?></td>
	</tr>
	<?php
	}
	
	?>
	<tr>
		<td colspan="2" style="vertical-align:middle"><strong>Tổng cộng: </strong></td>
		<td align="right" style="vertical-align:middle"> </td>
		<td align="right" style="vertical-align:middle"> <strong><?php echo $appSession->getFormats()->getINT()->format($total_a);?></strong></td>
        <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_b);?></strong></td>
        <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_c);?></strong></td>
        <td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($total_d);?></strong></td>
  </tr>
</table>
