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
$product_id = $config->getProperty("product_id");
$unit_id = $config->getProperty("unit_id");
$attribute_id = $config->getProperty("attribute_id");
$type_id = $config->getProperty("type_id");
$fdate = $config->getProperty("fdate");
$tdate = $config->getProperty("tdate");


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

$sql = "SELECT d.* FROM (SELECT d10.receipt_no, d10.receipt_date, d8.quantity, (SELECT sum(m.quantity) FROM stock_product m left outer join stock n on(m.stock_id = n.id) WHERE m.status =0 AND n.status =0 AND m.rel_id = d8.id";
if($fdate != "")
{
$sql = $sql." AND n.receipt_date<='".$fdate."'";
}
$sql = $sql." ) AS quantity_out, d52.code AS supplier_code, d52.name AS supplier_name, d51.receipt_date AS receive_date, d51.receipt_no AS receive_no, d53.code AS product_code, d53.name AS product_name, d9.name AS warehouse_name, d54.name AS company_name, d55.name AS attribute_name, d56.name AS type_name FROM stock_product d8 LEFT OUTER JOIN stock_warehouse d9 ON(d8.warehouse_id = d9.id) LEFT OUTER JOIN stock d10 ON(d8.stock_id = d10.id) LEFT OUTER JOIN stock_category d11 ON(d10.category_id = d11.id)";

$sql = $sql." LEFT OUTER JOIN stock_product d14 ON(d8.rel_id = d14.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d18 ON(d17.rel_id = d18.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d19 ON(d18.rel_id = d19.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d20 ON(d19.rel_id = d20.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d21 ON(d20.rel_id = d21.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d22 ON(d21.rel_id = d22.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d23 ON(d22.rel_id = d23.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d24 ON(d23.rel_id = d24.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d25 ON(d24.rel_id = d25.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d26 ON(d25.rel_id = d26.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d27 ON(d26.rel_id = d27.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d28 ON(d27.rel_id = d28.id)";
$sql = $sql." LEFT OUTER JOIN stock_product d29 ON(d28.rel_id = d29.id)";
$sql = $sql." LEFT OUTER JOIN purchase_receive_product d50 ON((d8.rel_id = d50.id OR d14.rel_id = d50.id OR d15.rel_id = d50.id OR d16.rel_id = d50.id OR d17.rel_id = d50.id OR d18.rel_id = d50.id OR d19.rel_id = d50.id OR d20.rel_id = d50.id OR d21.rel_id = d50.id OR d22.rel_id = d50.id OR d23.rel_id = d50.id OR d24.rel_id = d50.id OR d25.rel_id = d50.id OR d26.rel_id = d50.id OR d27.rel_id = d50.id OR d28.rel_id = d50.id OR d29.rel_id = d50.id)  AND d50.status =0) LEFT OUTER JOIN purchase_receive d51 ON(d50.receive_id = d51.id) LEFT OUTER JOIN supplier d52 ON(d51.supplier_id = d52.id) LEFT OUTER JOIN product d53 ON(d50.product_id = d53.id) LEFT OUTER JOIN res_company d54 ON(d10.company_id = d54.id) LEFT OUTER JOIN attribute d55 ON(d8.attribute_id = d55.id) LEFT OUTER JOIN product_type d56 ON(d8.type_id = d56.id) ";
$sql = $sql." WHERE d8.product_id ='".$product_id."' AND d8.unit_id ='".$unit_id."'";
if($attribute_id != "")
{
	$sql = $sql." AND d8.attribute_id='".$attribute_id."'";
}
if($type_id != "")
{
	$sql = $sql." AND d8.type_id ='".$type_id."'";
}
if($tdate != "")
{
	$sql = $sql." AND d10.receipt_date <='".$tdate."'";
}
$sql = $sql." AND d9.stock=1 AND d11.deduction=0 ORDER BY d10.receipt_date ASC) d WHERE d.quantity>d.quantity_out OR d.quantity_out IS NULL";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);



?>
<table width="100%" border="0" class="borderLine">

  <tr class="header">
    <td align="center" nowrap style="width:20px"><strong>Stt</strong></td>
    <td align="center" nowrap style="width:80px"><strong>Số lượng</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Số nhập kho</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Ngày nhập kho</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Loại</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Hướng</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Kho</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Nhà kho</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Mã nhày cung cấp</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Tên nhà cung cấp</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Số phiếu nhập</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Ngày nhập</strong></td>
    <td align="center" nowrap style="width:60px"><strong>Mã vật tư</strong></td>
	<td align="center" nowrap style="width:60px"><strong>Tên vật tư</strong></td>
   
    </tr>
  
	<?php
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		
		
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		$supplier_code = $dt->getString($i, "supplier_code");
		$supplier_name = $dt->getString($i, "supplier_name");
		$product_code = $dt->getString($i, "product_code");
		$product_name = $dt->getString($i, "product_name");
		$receive_no = $dt->getString($i, "receive_no");
		$receive_date = $dt->getString($i, "receive_date");
		$attribute_name = $dt->getString($i, "attribute_name");
		$type_name = $dt->getString($i, "type_name");
		$warehouse_name = $dt->getString($i, "warehouse_name");
		$company_name = $dt->getString($i, "company_name");
		$quantity = $dt->getFloat($i, "quantity");
		$quantity_out = $dt->getFloat($i, "quantity_out");
		if($quantity_out>= $quantity)
		{
			continue;
		}
		$quantity = $quantity - $quantity_out;
		if($receipt_date != "")
		{
			$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		}
		if($receive_date != "")
		{
			$receive_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receive_date));
		}
	
	?>
	<tr>
		<td style="vertical-align:middle"><?php echo $no;?>.</td>
		<td style="vertical-align:middle; text-align:center"><?php echo $quantity;?></td>
		<td style="vertical-align:middle"><?php echo $receipt_no;?></td>
		<td style="vertical-align:middle"><?php echo $receipt_date;?></td>
		<td  style="vertical-align:middle"><?php echo $attribute_name;?></td>
		<td  style="vertical-align:middle"><?php echo $type_name;?></td>
		<td  style="vertical-align:middle"><?php echo $warehouse_name;?></td>
		<td  style="vertical-align:middle"><?php echo $company_name;?></td>
		
		<td  style="vertical-align:middle"><?php echo $supplier_code;?></td>
		<td  style="vertical-align:middle"><?php echo $supplier_name;?></td>
		<td  style="vertical-align:middle"><?php echo $receive_no;?></td>
		<td  style="vertical-align:middle"><?php echo $receive_date;?></td>
		<td  style="vertical-align:middle"><?php echo $product_code;?></td>
		<td  style="vertical-align:middle"><?php echo $product_name;?></td>
	
		
	  </tr>
	<?php
	}
	
	?>
</table>
