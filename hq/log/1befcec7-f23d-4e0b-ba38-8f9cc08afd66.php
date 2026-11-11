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

$sql = "SELECT  d1.category_id, d2.name AS category_name, d1.partner_id, d3.code AS partner_code, d3.name AS partner_name, d3.phone, d3.email, d3.vat, d1.currency_id, d4.payment_id, d9.name AS payment_name, SUM(d1.amount) AS amount, SUM(d4.amount) AS payment FROM  account_invoice d1 LEFT OUTER JOIN account_invoice_category d2 ON(d1.category_id = d2.id) LEFT OUTER JOIN res_partner d3 ON(d1.partner_id = d3.id) LEFT OUTER JOIN account_payment_line d4 ON(d1.id = d4.rel_id AND d4.status=0) LEFT OUTER JOIN account_payment d9 ON(d4.payment_id = d9.id )  WHERE d1.status =0 AND d2.status =0";

$sql = $sql." AND d1.receipt_date>='".$fdate."'";
$sql = $sql." AND d1.receipt_date<='".$tdate."'";



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
$sql = $sql." GROUP BY d1.category_id, d2.name, d1.partner_id, d3.code, d3.name, d3.phone, d3.email, d3.vat, d1.currency_id, d4.payment_id, d9.name";
$sql = $sql." ORDER BY d3.code";


$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$dt_payment = $appSession->getTool()->selectDistinctTable($dt, ["payment_id", "payment_name"]);

$dt_partner = $appSession->getTool()->selectDistinctTable($dt, ["category_id", "category_name", "partner_id", "partner_code","partner_name", "phone", "email", "vat"]);

$ids = "";
for($i =0; $i<$dt_partner->getRowCount(); $i++)
{
	if($ids != "")
	{
		$ids = $ids." OR ";
	}
	$ids = $ids." d1.rel_id='".$dt_partner->getString($i, "partner_id")."'";
}

$sql = "SELECT d1.rel_id, d1.code, d1.name, d2.name AS bank_name, d1.bank_id, d2.name AS bank_name FROM account_bank_line d1 LEFT OUTER JOIN account_bank d2 ON(d1.bank_id = d2.id) WHERE d1.status =0";
if($ids != "")
{
	$sql = $sql." AND (".$ids.")";
}else{
	$sql = $sql." AND 1=0";
}
$msg->add("query", $sql);
$dt_bank = $appSession->getTier()->getTable($msg);

$dt_bank_names = $appSession->getTool()->selectDistinctTable($dt_bank, ["bank_id", "bank_name"]);


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
    <td rowspan="2" align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>M&atilde; &#273;&#7889;i t&#432;&#7907;ng </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>T&ecirc;n &#273;&#7889;i t&#432;&#7907;ng </strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Lo&#7841;i</strong></td>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Thành tiền </strong></td>
	<?php if($dt_payment->getRowCount()>0){ ?>
    <td colspan="<?php echo $dt_payment->getRowCount();?>" align="center" nowrap style="width:60px"><strong>Thanh toán</strong></td>
	<?php } ?>
	<?php if($dt_bank_names->getRowCount()>0){ ?>
	<td colspan="<?php echo $dt_bank_names->getRowCount();?>" align="center" nowrap style="width:60px"><strong>Thông tin chuyển khoản</strong></td>
	<?php } ?>
    <td rowspan="2" align="center" nowrap style="width:60px"><strong>Ghi chú </strong></td>
  </tr>
  <tr class="header">
	<?php
	for($j=0; $j<$dt_payment->getRowCount(); $j++)
	{
	?>
	<td   align="center" nowrap style="width:60px"><strong><?php echo $dt_payment->getString($j, "payment_name");?></strong></td>
	<?php
	}
	?>
	<?php
	for($j=0; $j<$dt_bank_names->getRowCount(); $j++)
	{
	?>
	<td   align="center" nowrap style="width:60px"><strong><?php echo $dt_bank_names->getString($j, "bank_name");?></strong></td>
	<?php
	}
	?>
    </tr>
	<?php
	$no = 0;
	$totalAmount = 0;

	
	for($i =0; $i<$dt_partner->getRowCount(); $i++)
	{
		$no = $no + 1;
		$partner_id = $dt_partner->getString($i, "partner_id");
		$partner_code = $dt_partner->getString($i, "partner_code");
		$partner_name = $dt_partner->getString($i, "partner_name");
		$category_id = $dt_partner->getString($i, "category_id");
		$category_name = $dt_partner->getString($i, "category_name");
		$amount =$dt_partner->getFloat($i, "amount");
		$totalAmount = $totalAmount + $amount;
		
		
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle"><?php echo $partner_code;?></td>
		<td style="vertical-align:middle"><?php echo $partner_name;?></td>
		<td style="vertical-align:middle"><?php echo $category_name;?></td>
		<td align="right" style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($amount);?></td>
		<?php
		
		for($j=0; $j<$dt_payment->getRowCount(); $j++)
		{
			$payment = 0;
			$payment_id = $dt_payment->getString($j, "payment_id");
			
			for($n =0; $n<$dt->getRowCount(); $n++)
			{
				if($dt->getString($n, "payment_id") == $payment_id && $dt->getString($n, "partner_id") == $partner_id  && $dt->getString($n, "category_id") == $category_id)
				{
					$payment = $payment + $dt->getFloat($n, "payment");
				}
			}
		?>
		<td align="right"><?php echo $appSession->getFormats()->getINT()->format($payment);?></td>
		
		<?php
		}
		?>
		<?php
		for($j=0; $j<$dt_bank_names->getRowCount(); $j++)
		{
			$bank_id = $dt_bank_names->getString($j, "bank_id");
			$bank = "";
			for($n=0; $n<$dt_bank->getRowCount(); $n++)
			{
				if($dt_bank->getString($n, "bank_id") == $bank_id && $dt_bank->getString($n, "rel_id") == $partner_id )
				{
					$bank = $dt_bank->getString($n, "code").". ".$dt_bank->getString($n, "name");
					break;
				}
			}
			
		?>
		<td style="vertical-align:middle"><?php echo $bank;?></td>
		<?php
		}
		?>
		<td></td>
	  </tr>
	<?php
	}
	
	?>
	<tr>
		<td colspan="4" style="vertical-align:middle"><strong>Tổng cộng: </strong></td>
		<td align="right" style="vertical-align:middle"><strong><?php echo $appSession->getFormats()->getINT()->format($totalAmount);?></strong></td>
		<?php
		
		for($j=0; $j<$dt_payment->getRowCount(); $j++)
		{
			$payment = 0;
			$payment_id = $dt_payment->getString($j, "payment_id");
			
			for($n =0; $n<$dt->getRowCount(); $n++)
			{
				if($dt->getString($n, "payment_id") )
				{
					$payment = $payment + $dt->getFloat($n, "payment");
				}
			}
		?>
		<td style="vertical-align:middle"><?php echo $appSession->getFormats()->getINT()->format($payment);?></strong></td>
		<?php
		}
		?>
		<?php
	for($j=0; $j<$dt_bank_names->getRowCount(); $j++)
	{
	?>
	<td></td>
	<?php
	}
	?>
		<td style="vertical-align:middle"> </td>
	  </tr>
</table>
