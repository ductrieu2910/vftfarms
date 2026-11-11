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
if($company_currency_id == "")
{
	$company_currency_id = "23";
}

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

$sql = "SELECT  d1.partner_id, d2.code AS partner_code, d2.name AS partner_name, d2.phone, d2.vat, d2.address, d3.name AS category_name,  d1.receipt_no, d1.receipt_date, d1.currency_id, d1.amount, d1.origin_no, d1.origin_date, d1.description, d4.name AS status_name, d5.name AS payment_term_name, d6.code AS currency_code FROM account_invoice d1 LEFT OUTER JOIN res_partner d2 ON(d1.partner_id = d2.id) LEFT OUTER JOIN account_invoice_category d3 ON(d1.category_id = d3.id) LEFT OUTER JOIN account_invoice_status d4 ON(d1.status_id = d4.id) LEFT OUTER JOIN account_payment_term d5 ON(d1.payment_term_id= d5.id) LEFT OUTER JOIN res_currency d6 ON(d1.currency_id = d6.id) WHERE d1.status =0";

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
$sql = $sql." ORDER BY d1.receipt_date ASC";

$msg->add("query", $sql);
$dt_invoice = $appSession->getTier()->getTable($msg);

$dt_partner = $appSession->getTool()->selectDistinctTable($dt_invoice, ["partner_id","partner_code","partner_name", "phone","address","vat"]);

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
	
	<table width="100%" border="0" cellspacing="1" cellpadding="1" class="borderLine">

  <tr class="header">
    <td width="20" align="center"><strong>Stt</strong></td>
    <td width="80" align="center"><strong>S&#7889; ch&#7913;ng t&#7915; </strong></td>
    <td align="center" width="120"><strong>Ng&agrave;y </strong></td>
	<td  align="center"><strong>Nh&oacute;m</strong></td>
    <td width="150" align="center"><strong>N&#7897;i dung thanh to&aacute;n </strong></td>
    <td width="90" align="center"><strong>T&igrave;nh tr&#7841;ng </strong></td>
    <td width="60" align="center"><strong>Ti&#7873;n t&#7879; </strong></td>
	<td width="120" align="center"><strong>Gi&aacute; tr&#7883; </strong></td>
	<td width="80" align="center"><strong>Ch&#7913;ng t&#7915; g&#7889;c </strong></td>
    <td width="200" align="center"><strong>Ghi ch&uacute; </strong></td>
  </tr>
  <?php
  	$totalAmount = 0;
	for($i =0; $i<$dt_partner->getRowCount(); $i++)
	{
		$no = 0;
		$partner_id = $dt_partner->getString($i, "partner_id");
		$partner_code = $dt_partner->getString($i, "partner_code");
		$partner_name = $dt_partner->getString($i, "partner_name");
		$phone = $dt_partner->getString($i, "phone");
		$address = $dt_partner->getString($i, "address");
		$vat = $dt_partner->getString($i, "vat");
	?>
		
		<tr>
		<td colspan="10"><b>M&atilde;: <?php echo $partner_code;?>;  T&ecirc;n: <?php echo $partner_name;?>; &#272;i&#7879;n tho&#7841;i: <?php echo $phone;?>; Vat: <?php echo $vat;?>; &#272;&#7883;a ch&#7881;: <?php echo $address;?></b></td>
		</tr>
		<?php
		
		$total =0;
		
		for($j =0; $j<$dt_invoice->getRowCount(); $j++)
		{
			
			$invoice_partner_id = $dt_invoice->getString($j, "partner_id");
			if($invoice_partner_id == $partner_id)
			{
				$receipt_no = $dt_invoice->getString($j,"receipt_no");
				$receipt_date = $dt_invoice->getString($j,"receipt_date");
				$category_name = $dt_invoice->getString($j,"category_name");
				$status_name = $dt_invoice->getString($j,"status_name");
				$payment_term_name = $dt_invoice->getString($j,"payment_term_name");
				$currency_code = $dt_invoice->getString($j,"currency_code");
				$amount = $dt_invoice->getString($j,"amount");
				$origin_no = $dt_invoice->getString($j,"origin_no");
				$description = $dt_invoice->getString($j,"description");
				
				$total = $total + $amount;
				
				$no = $no + 1;
				if($receipt_date != ""){ 
					
					$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
				}	
			?>
		
		<tr>
			<td style="vertical-align:middle"><?php echo $no;?></td>
			<td style="vertical-align:middle" nowrap="nowrap"><?php echo $receipt_no;?></td>
			<td style="vertical-align:middle" nowrap="nowrap"><?php echo $receipt_date;?></td>
			<td style="vertical-align:middle" nowrap="nowrap"><?php echo $category_name;?></td>
			<td style="vertical-align:middle"><?php echo $payment_term_name;?></td>
			<td style="vertical-align:middle" nowrap="nowrap"><?php echo $status_name;?></td>
			<td style="vertical-align:middle"><?php echo $currency_code;?></td>
			
			<td style="text-align:right; vertical-align:middle"><?php echo $appSession->getCurrency()->format($company_currency_id, $amount);?></td>
			<td style="vertical-align:middle"><?php echo $origin_no;?></td>
			<td style="vertical-align:middle"><?php echo $description;?></td>

		</tr>
			<?php
			}
			}
			 $totalAmount =  $totalAmount + $total;
			?>
			<tr>
			<td colspan="7" style="vertical-align:middle"><b>T&#7893;ng: </b></td>
			<td style="text-align:right; vertical-align:middle"><b><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></b></td>
			<td style="text-align:right; vertical-align:middle"></td>
			<td style="text-align:right; vertical-align:middle"></td>
			</tr>
			
		
	<?php
	}
	?>
	<tr>
		<td colspan="7" style="vertical-align:middle"><b>T&#7893;ng c&#7897;ng: </b></td>
		<td style="text-align:right; vertical-align:middle"><b><?php echo $appSession->getCurrency()->format($company_currency_id, $totalAmount);?></b></td>
		<td style="text-align:right; vertical-align:middle"></td>
		<td style="text-align:right; vertical-align:middle"></td>
	</tr>
</table>