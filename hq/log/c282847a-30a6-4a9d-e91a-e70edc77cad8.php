<style>
body
{
	font-family: Arial,Courier,Verdana;
	font-size: 10pt;
}
.borderLine 
{
	border-collapse:collapse;
	border:none;
	mso-border-alt:solid #C1C2C3 .5pt;
 	mso-yfti-tbllook:480;
 	mso-padding-alt:0in 5.4pt 0in 5.4pt;
 	mso-border-insideh:.5pt solid #C1C2C3;
	mso-border-insidev:.5pt solid #C1C2C3
	font-family: Arial,Courier,Verdana;
	font-size: 10pt;
}
.borderLine td {
 	border:solid 1.0pt;
  	mso-border-alt:solid #C1C2C3 .5pt;
  	height:24px;
	padding:0in 5.4pt 0in 5.4pt
	font-family: Arial,Courier,Verdana;
	font-size: 10pt;
}
.title
{
	font-family: Arial,Courier,Verdana;
	font-size:14pt;
	font-weight:bold;
	color: #171717;
}
.company
{

}
.date
{

}
.header
{
	background-color:#FFFFA8;
	color:black;
}

.header1
{
	background-color:#F5FEB8;
	
}

.text_form_label 
{
	font-family: Arial,Courier,Verdana;
	font-size: 10pt;
	color: black;
	font-weight: bold;
}
.text_form_content
{
	font-family: Arial,Courier,Verdana;
	font-size: 9pt;
	color: Black;
}
hr
{
	height:1px;
	color: black;
}
.warnning
{
	font-family: Arial,Courier,Verdana;
	font-size: 9pt;
	color:Red;
}
.tbl
{
	border: 1px solid black;
}
p {
  margin: 1.1pt;
  font-family: Arial,Courier,Verdana;
  font-size: 10pt;
}
td {
  
  font-family: Arial,Courier,Verdana;
  font-size: 10pt;
  
}
span {
  margin: 1.1pt;
  font-family: Arial,Courier,Verdana;
  font-size: 10pt;
}
@page {
    margin-top: 2cm;
    margin-bottom: 2cm;
    margin-left: 3cm;
    margin-right: 1.5cm;
}
.title_cert {
	
	font-family: "UTM God's Word", serif;
}
</style>
<?php 
$config = new AppConfig("");
$config->parseWith($data, '&');
$receive_product_id = $config->getProperty("id");



$sql = "SELECT d1.name, lg.description FROM ir_action d1 LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$lang_id."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE  d1.id='".$action_id."'";

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

$sql = "SELECT d1.id FROM stock_product d1 LEFT OUTER JOIN stock d2 ON(d1.stock_id = d2.id) WHERE d1.status =0 AND d2.rel_id ='".$sale_id."'";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$ids = "";
for($i =0; $i<$dt->getRowCount(); $i++)
{
	if($ids != "")
	{
		$ids = $ids ." OR ";
	}
	$ids = $ids ." d8.id ='".$dt->getString($i, "id")."'";
}

$sql = "
SELECT distinct d52.quantity, d51.order_no, d51.receipt_date, d51.order_date, d51.receipt_no, d8.quantity, d53.code AS customer_code, d53.name AS customer_name, p.code AS product_code, p.name AS product_name, u.name AS unit_name, a.name AS attribute_name, t.name AS type_name FROM stock_product d8 LEFT OUTER JOIN stock d10 ON(d8.stock_id = d10.id) LEFT OUTER JOIN stock_category d11 ON(d10.category_id = d11.id) LEFT OUTER JOIN stock_product d14 ON(d8.rel_id = d14.id) LEFT OUTER JOIN stock d14s ON(d14.stock_id = d14s.id) LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id) LEFT OUTER JOIN stock d15s ON(d15.stock_id = d15s.id) LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id) LEFT OUTER JOIN stock d16s ON(d16.stock_id = d16s.id) LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id) LEFT OUTER JOIN stock d17s ON(d17.stock_id = d17s.id) LEFT OUTER JOIN stock_product d18 ON(d17.rel_id = d18.id)  LEFT OUTER JOIN stock d18s ON(d18.stock_id = d18s.id) LEFT OUTER JOIN stock_product d19 ON(d18.rel_id = d19.id) LEFT OUTER JOIN stock d19s ON(d19.stock_id = d19s.id) LEFT OUTER JOIN stock_product d20 ON(d19.rel_id = d20.id) LEFT OUTER JOIN stock d20s ON(d20.stock_id = d20s.id) LEFT OUTER JOIN stock_product d21 ON(d20.rel_id = d21.id) LEFT OUTER JOIN stock d21s ON(d20.stock_id = d21s.id) LEFT OUTER JOIN stock_product d22 ON(d21.rel_id = d22.id) LEFT OUTER JOIN stock d22s ON(d22.stock_id = d22s.id) LEFT OUTER JOIN stock_product d23 ON(d22.rel_id = d23.id) LEFT OUTER JOIN stock d23s ON(d23.stock_id = d23s.id) LEFT OUTER JOIN stock_product d24 ON(d23.rel_id = d24.id) LEFT OUTER JOIN stock d24s ON(d24.stock_id = d24s.id) LEFT OUTER JOIN stock_product d25 ON(d24.rel_id = d25.id) LEFT OUTER JOIN stock d25s ON(d25.stock_id = d25s.id) LEFT OUTER JOIN stock_product d26 ON(d25.rel_id = d26.id) LEFT OUTER JOIN stock d26s ON(d26.stock_id = d26s.id) LEFT OUTER JOIN stock_product d27 ON(d26.rel_id = d27.id) LEFT OUTER JOIN stock d27s ON(d27.stock_id = d27s.id) LEFT OUTER JOIN stock_product d28 ON(d27.rel_id = d28.id) LEFT OUTER JOIN stock d28s ON(d28.stock_id = d28s.id) LEFT OUTER JOIN stock_product d29 ON(d28.rel_id = d29.id) LEFT OUTER JOIN stock d29s ON(d29.stock_id = d29s.id) LEFT OUTER JOIN purchase_receive_product_local d50 ON(d8.rel_id = d50.id OR d14.rel_id = d50.id OR d15.rel_id = d50.id OR d16.rel_id = d50.id OR d17.rel_id = d50.id OR d18.rel_id = d50.id OR d19.rel_id = d50.id OR d20.rel_id = d50.id OR d21.rel_id = d50.id OR d22.rel_id = d50.id OR d23.rel_id = d50.id OR d24.rel_id = d50.id OR d25.rel_id = d50.id OR d26.rel_id = d50.id OR d27.rel_id = d50.id OR d28.rel_id = d50.id OR d29.rel_id = d50.id) LEFT OUTER JOIN sale d51 ON(d10.rel_id = d51.id OR d14s.rel_id = d51.id OR d15s.rel_id = d51.id OR d16s.rel_id = d51.id OR d17s.rel_id = d51.id OR d18s.rel_id = d51.id OR d19s.rel_id = d51.id OR d20s.rel_id = d51.id OR d21s.rel_id = d51.id OR d22s.rel_id = d51.id OR d23s.rel_id = d51.id OR d24s.rel_id = d51.id OR d25s.rel_id = d51.id OR d26s.rel_id = d51.id OR d27s.rel_id = d51.id OR d28s.rel_id = d51.id OR d29s.rel_id = d51.id) LEFT OUTER JOIN sale_product d52 ON(d51.id = d52.sale_id AND d8.product_id = d52.product_id) LEFT OUTER JOIN customer d53 ON(d51.customer_id = d53.id) LEFT OUTER JOIN product p ON(d52.product_id = p.id) LEFT OUTER JOIN product_unit u oN(d52.unit_id = u.id) LEFT OUTER JOIN attribute a ON(d52.attribute_id = a.id) LEFT OUTER JOIN product_type t ON(d52.type_id = t.id) WHERE d8.status =0 AND d53.id IS NOT NULL AND d50.id='".$receive_product_id."' ORDER BY d51.receipt_date ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);

$sql = "SELECT d1.quantity, d2.receipt_no, d2.receipt_date, d3.code AS supplier_code, d3.name AS supplier_name, d4.code AS product_code, d4.name AS product_name, d5.name AS unit_name FROM purchase_receive_product_local d1 LEFT OUTER JOIN purchase_receive_local d2 ON(d1.receive_id = d2.id) LEFT OUTER JOIN supplier d3 ON(d2.supplier_id = d3.id) LEFT OUTER JOIN product d4 ON(d1.product_id = d4.id) LEFT OUTER JOIN product_unit d5 ON(d1.unit_id = d5.id) WHERE d1.id ='".$receive_product_id."'";

$msg->add("query", $sql);
$dt_receive_product = $appSession->getTier()->getTable($msg);
$receive_receipt_no = "";
$receive_receipt_date = "";
$receive_supplier_code = "";
$receive_supplier_name = "";
$receive_product_code = "";
$receive_product_name = "";
$receive_unit_name = "";
$receive_quantity = "";

if($dt_receive_product->getRowCount()>0)
{
	$receive_receipt_no = $dt_receive_product->getString(0, "receipt_no");
	$receive_receipt_date = $dt_receive_product->getString(0, "receipt_date");
	$receive_supplier_code = $dt_receive_product->getString(0, "supplier_code");
	$receive_supplier_name = $dt_receive_product->getString(0, "supplier_name");
	$receive_product_code =  $dt_receive_product->getString(0, "product_code");
	$receive_product_name = $dt_receive_product->getString(0, "product_name");
	$receive_unit_name = $dt_receive_product->getString(0, "unit_name");
	$receive_quantity = $dt_receive_product->getString(0, "quantity");
}
if($receive_receipt_date != "")
{
	$receive_receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receive_receipt_date));
}
?>
 <h1><?php echo $report_name?></h1><br>
 <br>
<strong>Số phiếu nhập:</strong> 
<?php echo $receive_receipt_no;?><br>
<strong>Số phiếu nhập:</strong> 
<?php  echo $receive_receipt_date;?><br>
<strong>Nhà cung cấp:</strong> 
<?php  echo $receive_product_code;?>. <?php echo $receive_supplier_name;?><br>
<strong>Nguyên liệu: </strong>
<?php  echo $receive_product_code;?>. <?php echo $receive_product_name;?><br>
<strong>Số lượng:</strong> 
<?php  echo $receive_quantity;?> <?php echo $receive_unit_name;?><br>
<hr>
<br>

<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>Stt</strong></td>
	 <td align="center" nowrap style="width:30px"><strong>S&#7889; &#273;&#417;n </strong></td>
	  <td align="center" nowrap style="width:30px"><strong>Ng&agrave;y &#273;&#7863;t </strong></td>
	  <td align="center" nowrap style="width:30px"><strong>S&#7889; ch&#7913;ng t&#7915; </strong></td>
	  <td align="center" nowrap style="width:80px"><strong>Ng&agrave;y ch&#7913;ng t&#7915; </strong></td>
    <td align="center" nowrap style="width:80px"><strong>M&atilde; SP </strong></td>
    <td align="center" nowrap style="width:80px"><strong>T&ecirc;n SP</strong></td>
    <td align="center" nowrap style="width:80px"><strong>&#272;&#417;n v&#7883; </strong></td>
    <td align="center" nowrap style="width:80px"><strong>Quy c&aacute;ch </strong></td>
    <td align="center" nowrap style="width:80px"><strong>Lo&#7841;i</strong></td>
    <td align="center" nowrap style="width:80px"><strong>S&#7889; l&#432;&#7907;ng</strong></td>
    <td align="center" nowrap style="width:60px"><strong>M&atilde; kh&aacute;ch </strong></td>
	<td align="center" nowrap style="width:250px"><strong>T&ecirc;n kh&aacute;ch </strong></td>
	</tr>
  
	<?php
	$no = 0;
	$total = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		
		$order_no = $dt->getString($i, "order_no");
		$order_date = $dt->getString($i, "order_date");
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		$customer_code = $dt->getString($i, "customer_code");
		$customer_name = $dt->getString($i, "customer_name");
		$quantity = $dt->getString($i, "quantity");
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$unit_name = $dt->getString($i, "unit_name");
		$attribute_name = $dt->getString($i, "attribute_name");
		$type_name = $dt->getString($i, "type_name");
		
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		if($order_date != "")
		{
			$order_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($order_date));
		}
		$total = $total + $quantity;
	
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td nowrap style="vertical-align:middle;"><?php echo $order_no;?></td>
		<td nowrap style="vertical-align:middle;"><?php echo $order_date;?></td>
		<td nowrap style="vertical-align:middle;"><?php echo $receipt_date;?></td>
		<td nowrap style="vertical-align:middle;"><?php echo $receipt_date;?></td>
		<td nowrap style="vertical-align:middle;"><?php echo $product_code;?></td>
		<td nowrap style="vertical-align:middle; "><?php echo $product_name;?></td>
		<td nowrap style="vertical-align:middle;"><?php echo $unit_name;?></td>
		<td nowrap style="vertical-align:middle; "><?php echo $attribute_name;?></td>
		<td nowrap style="vertical-align:middle; "><?php echo $type_name;?></td>
		<td nowrap style="vertical-align:middle; text-align:center"><?php echo $quantity;?></td>
		<td nowrap style="vertical-align:middle"><?php echo $customer_code;?></td>
		<td nowrap style="vertical-align:middle"><?php echo $customer_name;?></td>
	  </tr>
	
	<?php
	}
	
	?>
	<tr>
	  <td colspan="10" style="vertical-align:middle"><strong>T&#7893;ng:</strong></td>
	  <td nowrap style="vertical-align:middle; text-align:center"><strong><?php echo $total;?></strong></td>
	  <td nowrap style="vertical-align:middle">&nbsp;</td>
	  <td nowrap style="vertical-align:middle">&nbsp;</td>
  </tr>
</table>
