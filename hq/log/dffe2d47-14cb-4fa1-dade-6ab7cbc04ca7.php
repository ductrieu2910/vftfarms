<!-- INFO: theo dõi bán hàng (tổng): seq 16 -->
<?php
$report = new Report();
$report->parse($appSession, $data);
$fdate = $report->getParamValueCaption("From Date");
$tdate = $report->getParamValueCaption("To Date");
$customer_category_id = $report->getParamValueCaption("Category");
$company_id = $report->getParamValueCaption("Company");
$search = $report->getParamValueCaption("Search");

if ($fdate == "") {
  $fdate = date('Y-m-d');
}
if ($tdate == "") {
  $tdate = date('Y-m-d');
}
if ($appSession->getTool()->indexOf($fdate, " ") == -1) {
  $fdate = $fdate . " 00:00:00";
}
if ($appSession->getTool()->indexOf($tdate, " ") == -1) {
  $tdate = $tdate . " 23:59:59.999";
}
$company_id = $report->getParamValueCaption("Company");

if ($company_id == "") {
  $company_id = $appSession->getConfig()->getProperty("company_id");
}
$company_currency_id = $appSession->getConfig()->getProperty("currency_id");

$sql = "SELECT d1.name, lg.description FROM ir_module_report d1 LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='" . $lang_id . "' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE  d1.id='" . $report_id . "'";

$report_name = "";
$module_id = "";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

for ($i = 0; $i < $dt->getRowCount(); $i++) {
  $report_name = $dt->getString($i, "description");
  $module_id = $dt->getString($i, "module_id");
  if ($report_name == "") {
    $report_name = $dt->getString($i, "name");
  }
}

$sql = "SELECT id, code, name FROM res_company WHERE (id='" . $company_id . "' OR parent_id='" . $company_id . "') AND status =0";
$msg->add("query", $sql);
$dt_company = $appSession->getTier()->getTable($msg);

$company_names = "";
for ($i = 0; $i < $dt_company->getRowCount(); $i++) {
  if ($company_names != "") {
    $company_names = $company_names . ", ";
  }
  $code = $dt_company->getString($i, "code");
  $name = $dt_company->getString($i, "name");
  $company_names = $company_names . $code . "." . $name;
}

$company_ids = "";
for ($i = 0; $i < $dt_company->getRowCount(); $i++) {
  if ($company_ids != "") {
    $company_ids = $company_ids . " or ";
  }
  $id = $dt_company->getString($i, "id");
  $company_ids = $company_ids . " d2.company_id='$id'";
}

$dynamicSQL = " and s.start_date>='" . $fdate . "' and s.start_date<='" . $tdate . "' and (" . $company_ids . ") group by d3.code, d3.name, d4.name";

$sql = "select d3.code as product_code, d3.name as product_name, d4.name as unit_name,  sum(d1.quantity) as quantity, sum(d1.quantity * d1.unit_price) - sum(d1.discount_amount) as total,
	sum(d5.dividend / nullif(d5.divisor, 0) * d1.quantity) as weight
	from sale_product d1
	left join sale d2 on(d1.sale_id = d2.id)
	left join product d3 on(d1.product_id = d3.id)
	left join product_unit d4 on(d3.unit_id = d4.id)
	left join sale_shipping s on(d2.id = s.sale_id and s.status = 0)
	left join attribute d5 on(d1.attribute_id = d5.id)
	left join res_company d6 on(d2.company_id = d6.id)
	where  d1.quantity > 0 and d1.status = 0 and d2.status = 0";


$sql .= $dynamicSQL . " order by product_code, product_name;";

$msg->add("query", $sql);


$dt = $appSession->getTier()->getTable($msg);

?>

<strong><?php echo $report_name ?></strong><br>
<strong>Cửa hàng: <?php echo $company_names; ?></strong><br>
<strong>Từ ngày:
  <?php if ($fdate != "") {
    echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($fdate));
  } ?>
</strong><br>
<strong>Đến ngày:
  <?php
  if ($tdate != "") {
    echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($tdate));
  } ?></strong><br>

<table width="100%" border="0" class="borderLine">
  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>No</strong></td>
    <td align="center" nowrap style="width:80px"><strong>Mã SP</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Tên SP  </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Đơn vị tính web bán </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số lượng theo túi </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số lượng quy đổi theo kg</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Đơn giá túi </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Đơn giá quy đổi theo kg </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Thành tiền túi </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Thành tiền theo kg </strong></td>
    <td align="center" nowrap style="width:60px"><strong>Ghi chú</strong></td>
  </tr>

  <?php
  $total = 0;
	$total_weight = 0;
  for ($i = 0; $i < $dt->getRowCount(); $i++) {
    $product_code = $dt->getString($i, "product_code");
    $product_name = $dt->getString($i, "product_name");
    $unit_name = $dt->getString($i, "unit_name");
	
    $weight = $dt->getFloat($i, "weight");
    $quantity = $dt->getString($i, "quantity");
    $totalSub = $dt->getString($i, "total");
	$factor = 1;
	if($weight == 0){
		$weight = $quantity;
	}
	if($weight != 0){
		$factor = $quantity/$weight;
	} 
	
    $unit_price = $totalSub / $quantity;
	$unit_price_weight = $unit_price * $factor;
	$totalSub_Weight = $unit_price_weight * $weight;
	
    $total += $totalSub;
	$total_weight = $total_weight + $totalSub_Weight;
  ?>
    <tr>
      <td style="text-align:center; padding: 3px;"><?php echo $i + 1; ?></td>
      <td style="padding: 3px;"><?php echo $product_code; ?></td>
      <td style="padding: 3px;"><?php echo $product_name; ?></td>
      <td style="padding: 3px;"><?php echo $unit_name; ?></td>
      <td style="padding: 3px; text-align:right"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity); ?></td>
      <td style="padding: 3px; text-align:right"><?php echo $appSession->getFormats()->getDOUBLE()->format($weight); ?></td>
      <td style="text-align:right; padding: 3px;"><?php echo $appSession->getFormats()->getINT()->format($unit_price); ?></td>
      <td style="text-align:right; padding: 3px;"><?php echo $appSession->getFormats()->getINT()->format($unit_price_weight); ?></td>
      <td style="text-align:right; padding: 3px;"><?php echo $appSession->getFormats()->getINT()->format($totalSub); ?></td>
      <td style="text-align:right; padding: 3px;"><?php echo $appSession->getFormats()->getINT()->format($totalSub_Weight); ?></td>
      <td></td>
    </tr>
  <?php
  }
  ?>
  <tr>
    <td colspan="5"><strong>Tổng cộng:</strong><strong></strong></td>
    <td></td>
    <td></td>
    <td></td>
    <td style="text-align:right; padding: 3px;"><strong><?php echo $appSession->getFormats()->getINT()->format($total); ?></strong></td>
    <td style="text-align:right; padding: 3px;"><strong><?php echo $appSession->getFormats()->getINT()->format($total_weight); ?></strong></td>
    <td></td>
  </tr>
</table>
