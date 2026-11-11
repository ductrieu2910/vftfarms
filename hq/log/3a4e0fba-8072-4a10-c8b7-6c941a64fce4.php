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

$sql = "SELECT d1.create_date, d1.code, d1.name, d1.phone, d1.email, d1.address, d20.birthday, d2.code AS code1, d3.code AS code2, d4.code AS code3, d5.code AS code4, d6.code AS code5, d7.code AS code6, d8.code AS code7, d9.code AS code8 FROM  res_partner d1 LEFT OUTER JOIN res_partner d2 ON(d1.parent_id = d2.id) LEFT OUTER JOIN res_partner d3 ON(d2.parent_id = d3.id) LEFT OUTER JOIN res_partner d4 ON(d3.parent_id = d4.id) LEFT OUTER JOIN res_partner d5 ON(d4.parent_id = d5.id) LEFT OUTER JOIN res_partner d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_partner d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN res_partner d8 ON(d7.parent_id = d8.id) LEFT OUTER JOIN res_partner d9 ON(d8.parent_id = d9.id) LEFT OUTER JOIN customer d20 ON(d1.id = d20.partner_id AND d2.status =0) WHERE d3.status =0";


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
$sql = $sql." ORDER BY d3.create_date DESC";

$msg->add("query", $sql);

$dt = $appSession->getTier()->getTable($msg);

?>

<body leftmargin="0" topmargin="0">

 <strong><?php echo $report_name?></strong><br>
 <strong>Location: <?php echo $company_names;?></strong><br>
 <strong>From Date: <?php if($fdate != "") {
	echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($fdate));
 } ?>
</strong><br>
<strong>To Date: <?php 
if($tdate != "") {
	echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($tdate));
 }?></strong><br>
	
	<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>TT</strong></td>
    <td align="center" nowrap style="width:80px"><strong>Ngày</strong></td>
    <td align="center" nowrap style="width:60px"> <strong>Mã </strong></td>
	<td align="center" nowrap ><strong>Tên </strong></td>
	<td align="center" nowrap  style="width:120px"><strong>Điện thoại </strong></td>
    <td align="center" nowrap style="width:120px"><strong>Email</strong></td>
    <td align="center" nowrap style="width:120px"><strong>Ng&agrave;y sinh </strong></td>
    <td align="center" nowrap  style="width:200px"><strong>Địa chỉ </strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F1</strong></td>
	<td align="center" nowrap  style="width:90px"><strong>F2</strong></td>
	<td align="center" nowrap  style="width:90px"><strong>F3</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F4</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F5</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F6</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F7</strong></td>
    <td align="center" nowrap  style="width:90px"><strong>F8</strong></td>
  </tr>
	<?php
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$create_date = $dt->getString($i, "create_date");
		
		$create_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($create_date));
		$birthday = $dt->getString($i, "birthday");
		if($birthday != "")
		{
			$birthday = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($birthday));
		}
		
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$phone = $dt->getString($i, "phone");
		$email = $dt->getString($i, "email");
		$address = $dt->getString($i, "address");
		$code1 = $dt->getString($i, "code1");
		$code2 = $dt->getString($i, "code2");
		$code3 = $dt->getString($i, "code3");
		$code4 = $dt->getString($i, "code4");
		$code5 = $dt->getString($i, "code5");
		$code6 = $dt->getString($i, "code6");
		$code7 = $dt->getString($i, "code7");
		$code8 = $dt->getString($i, "code8");
		
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $create_date;?></td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="text-align: left;"><?php echo $phone;?></td>
		<td style="text-align: left;"><?php echo $email;?></td>
		<td style="text-align: left;"><?php echo $birthday;?></td>
		<td style="text-align: left;"><?php echo $address;?></td>
		<td style="text-align: left;"><?php echo $code1;?></td>
		<td style="text-align: left;"><?php echo $code2;?></td>
		<td style="text-align: left;"><?php echo $code3;?></td>
	    <td style="text-align: left;"><?php echo $code4;?></td>
	    <td style="text-align: left;"><?php echo $code5;?></td>
	    <td style="text-align: left;"><?php echo $code6;?></td>
	    <td style="text-align: left;"><?php echo $code7;?></td>
	    <td style="text-align: left;"><?php echo $code8;?></td>
	</tr>
	<?php
	}
	?>
</table>
