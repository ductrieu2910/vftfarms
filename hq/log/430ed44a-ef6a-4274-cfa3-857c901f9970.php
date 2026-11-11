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

$sql = "SELECT d2.id, d2.code, d2.name, COUNT(d2.id) AS c FROM res_receipt d1 LEFT OUTER JOIN product_classification d2 ON(d1.res_id = d2.id)";
$sql = $sql." WHERE (d1.status =2 OR d1.status =3) AND d2.id IS NOT NULL";
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
$sql = $sql." GROUP BY d2.id, d2.code, d2.name";
$sql = $sql." ORDER BY d2.code ASC, d2.name ASC";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$sql = "SELECT d6.id AS classification_id, d5.id AS receipt_id, d5.receipt_no, d5.receipt_date, d3.name";
$sql = $sql." FROM res_attribute_line_result d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.line_id = d2.id) LEFT OUTER JOIN res_attribute d3 ON(d2.attribute_id = d3.id) LEFT OUTER JOIN res_receipt d5 ON(d2.rel_id = d5.id) LEFT OUTER JOIN product_classification d6 ON(d5.res_id = d6.id) ";
$sql = $sql." WHERE d1.status =0 AND (d5.status =2 OR d5.status = 3) AND d1.result ='0'";
$sql = $sql." AND d5.receipt_date>='".$fdate."'";
$sql = $sql." AND d5.receipt_date<='".$tdate."'";
if($category_id != "")
{
	$sql = $sql." AND d6.category_id='".$category_id."'";
}

$company_ids = "";
for($ii =0; $ii<$dt_company->getRowCount(); $ii++)
{
	if($company_ids != ""){
		$company_ids = $company_ids." OR ";
	}
	$id = $dt_company->getString($ii, "id");
	$company_ids = $company_ids." d5.company_id='$id'";
}
$sql = $sql." AND (".$company_ids.") ";
$sql = $sql." ORDER BY d5.receipt_date, d5.receipt_no";
$msg->add("query", $sql);

$dt_result = $appSession->getTier()->getTable($msg);



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
	    <td align="center"  nowrap style="width:20px"><strong>Stt</strong></td>
	    <td align="center" nowrap  style="width:40px"><strong>Mã</strong></td>
	    <td align="center"  nowrap><strong>Tên</strong></td>
        <td align="center" style="width:60px" nowrap><strong>Số lần kiểm tra </strong></td>
        <td align="center" style="width:60px" nowrap><strong>Không đạt </strong></td>
        <td align="center" style="width:60px" nowrap><strong>Tỷ lệ </strong></td>
        <td align="center" style="width:300px" nowrap><strong>Bước không đạt </strong></td>
        <td colspan="2" align="center"  nowrap style="width:60px"><strong> </strong></td>
       </tr>
	 
	  <?php
	   $no = 0;
	  for($j =0; $j<$dt->getRowCount(); $j++)
	  {
	
		
		$id = $dt->getString($j, "id");
		$name = $dt->getString($j, "name");
		$code = $dt->getString($j, "code");
		$c = $dt->getFloat($j, "c");
		$no = $no + 1;
		$arr  =[];
		$description = "";
		for($n =0; $n<$dt_result->getRowCount(); $n++)
		{
			if($dt_result->getString($n, "classification_id") == $id)
			{
				$receipt_id = $dt_result->getString($n, "receipt_id");
				$hasItem = false;
				for($m =0; $m<count($arr); $m++)
				{
					if($arr[$m] == $receipt_id)
					{
						$hasItem = true;
						break;
					}
				}
				if($description != "")
				{
					$description = $description."; ";
				}
				$description = $description." ".$dt_result->getString($n, "name");
				if($hasItem == false)
				{
					$arr[count($arr)] = $receipt_id;
				}
			}
		}
		
		$p = count($arr)/$c;
		
	  ?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><a href="<?php echo URL;?>api/action/?action_id=5be9aa90-de20-42ce-e06e-81d7752c0174&data=<?php echo $appSession->getTool()->urlEncode("classification_id=".$id."&fdate=".$fdate."&tdate=".$tdate);?>" target="_blank"><?php echo $code;?></a> </td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $name;?></td>
	    <td align="center" nowrap="nowrap" style="vertical-align:middle"><?php echo $c;?></td>
	    <td align="center" nowrap="nowrap" style="vertical-align:middle"><?php echo count($arr);?></td>
	    <td align="center" nowrap="nowrap" style="vertical-align:middle"><?php echo $appSession->getFormats()->getPERCENT()->format($p);?></td>
	    <td align="left"  style="vertical-align:middle"><?php echo $description;?></td>
	    <td style="vertical-align:middle" nowrap="nowrap"><a href="<?php echo URL;?>api/action/?action_id=5be9aa90-de20-42ce-e06e-81d7752c0174&data=<?php echo $appSession->getTool()->urlEncode("classification_id=".$id."&fdate=".$fdate."&tdate=".$tdate);?>&view_type=excel" target="_blank">Excel</a></td>
	    <td style="vertical-align:middle" nowrap="nowrap"><a href="<?php echo URL;?>api/action/?action_id=5be9aa90-de20-42ce-e06e-81d7752c0174&data=<?php echo $appSession->getTool()->urlEncode("classification_id=".$id."&fdate=".$fdate."&tdate=".$tdate);?>&view_type=word" target="_blank">Word</a></td>
	  </tr>
	  <?php
		
	  }
	  ?>
	  </table>
	 

