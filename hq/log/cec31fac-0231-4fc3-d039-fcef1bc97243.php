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

$sql = "SELECT d3.id, d3.create_date AS dd, d3.code, d3.name, d3.phone, d3.email,  d3.address AS customer_address, d5.name AS ward_name, d6.name AS dist_name, d7.name AS city_name, d3.birthday, d3.gender, d9.code AS register_code, d9.name AS register_name FROM  customer d3 LEFT OUTER JOIN res_address d5 ON(d3.address_id = d5.id) LEFT OUTER JOIN res_address d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_address d7 ON(d6.parent_id = d7.id) LEFT OUTER JOIN res_partner d8 ON(d3.partner_id = d8.id) LEFT OUTER JOIN res_partner d9 ON(d8.parent_id = d9.id) WHERE d3.status =0";

$sql = $sql." AND d3.create_date>='".$fdate."'";
$sql = $sql." AND d3.create_date<='".$tdate."'";

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
$sql = $sql." ORDER BY d3.create_date ASC";

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
    <td align="center" nowrap style="width:80px"><strong>Ng&agrave;y</strong></td>
    <td align="center" nowrap style="width:60px"> <strong>M&atilde; </strong></td>
	<td align="center" nowrap ><strong>T&ecirc;n </strong></td>
	<td align="center" nowrap ><strong>Giới tính </strong></td>
	<td align="center" nowrap ><strong>Ngày sinh </strong></td>
	<td align="center" nowrap  style="width:120px"><strong>&#272;i&#7879;n tho&#7841;i </strong></td>
    <td align="center" nowrap style="width:120px"><strong>Email</strong></td>
    <td align="center" nowrap  style="width:200px"><strong>&#272;&#7883;a ch&#7881; </strong></td>
    <td align="center" nowrap  style="width:90px"><strong>Ph&#432;&#7901;ng/x&atilde;</strong></td>
	<td align="center" nowrap  style="width:90px"><strong>Qu&#7853;n/huy&#7879;n</strong></td>
	<td align="center" nowrap  style="width:90px"><strong>T&#7881;nh/th&agrave;nh</strong></td>
    <td align="center" nowrap  style="width:90px">M&atilde; gi&#7899;i thi&#7879;u </td>
    <td align="center" nowrap  style="width:90px">T&ecirc;n gi&#7899;i thi&#7879;u </td>
  </tr>
	<?php
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$create_date = $dt->getString($i, "dd");
		
		$create_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($create_date));
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$phone = $dt->getString($i, "phone");
		$email = $dt->getString($i, "email");
		$address = $dt->getString($i, "address");
		$ward_name = $dt->getString($i, "ward_name");
		$dist_name = $dt->getString($i, "dist_name");
		$city_name = $dt->getString($i, "city_name");
		$birthday = $dt->getString($i, "birthday");
		$register_code = $dt->getString($i, "register_code");
		$register_name = $dt->getString($i, "register_name");
		if($birthday != "")
		{
			$birthday = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($birthday));
		}
		$gender = $dt->getString($i, "gender");
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle "><?php echo $create_date;?></td>
		<td style="vertical-align:middle"><?php echo $code;?></td>
		<td style="vertical-align:middle"><?php echo $name;?></td>
		<td style="vertical-align:middle"><?php echo $birthday;?></td>
		<td style="vertical-align:middle"><?php echo $gender;?></td>
		<td style="text-align: left;"><?php echo $phone;?></td>
		<td style="text-align: left;"><?php echo $email;?></td>
		<td style="text-align: left;"><?php echo $address;?></td>
		<td style="text-align: left;"><?php echo $ward_name;?></td>
		<td style="text-align: left;"><?php echo $dist_name;?></td>
		<td style="text-align: left;"><?php echo $city_name;?></td>
	    <td style="text-align: left;"><?php echo $register_code;?></td>
	    <td style="text-align: left;"><?php echo $register_name;?></td>
	</tr>
	<?php
	}
	?>
</table>
