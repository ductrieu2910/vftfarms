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
$fdate = $config->getProperty("fdate");
$fdate = $appSession->getTool()->urlDecode($fdate);
$tdate = $config->getProperty("tdate");
$tdate = $appSession->getTool()->urlDecode($tdate);
$classification_id = $config->getProperty("classification_id");
$fdate = $appSession->getTool()->replace($fdate, " 00:00:00.000", "");
$tdate = $appSession->getTool()->replace($tdate, " 00:00:00.000", "");
$index = $appSession->getTool()->indexOf($fdate, " ");
$error = $config->getProperty("error");
$rel_id = $config->getProperty("rel_id");
if($index != -1)
{
	$fdate = $appSession->getTool()->subString($fdate, 0, $index);
}
$index = $appSession->getTool()->indexOf($tdate, " ");
if($index != -1)
{
	$tdate = $appSession->getTool()->subString($tdate, 0, $index);
}
if($fdate == "")
{
	$fdate = date("Y-m-d");
}
if($tdate == "")
{
	$tdate = date("Y-m-d");
}
if($appSession->getTool()->indexOf($fdate, " ") == -1)
{
	$fdate = $fdate." 00:00:00.000";
}
if($appSession->getTool()->indexOf($tdate, " ") == -1)
{
	$tdate = $tdate." 23:59:59.999";
}

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

$sql = "SELECT d1.id, d1.parent_id, d1.receipt_no, d1.status, d1.receipt_date, d2.id AS classification_id, d2.code, d2.name, d4.code AS product_code, d4.name AS product_name, d5.name AS warehouse_name, d6.version, d7.user_name, d7.name AS account_name, d8.barcode AS barcode1,   d14.barcode AS barcode2 , d15.barcode AS barcode3, d16.barcode AS barcode4, d17.barcode AS barcode5, d18.barcode AS barcode6 , d19.barcode AS barcode7, d20.barcode AS barcode8, d21.barcode AS barcode9, d22.barcode AS barcode10, d23.barcode AS barcode11, d24.barcode AS barcode12, d25.barcode AS barcode13, d26.barcode AS barcode14 , d27.barcode AS barcode15, d28.barcode AS barcode16, d29.barcode AS barcode17, d8.id AS stock_product_id, d8.stock_id, d40.id AS package_id, d40.rel_id package_stock_id, d41.order_no, d41.expiration_date, d41.manufacture_date, d41.packing_date, d41.lot_number, d41.lot_code, d50.rel_id AS receive_product_rel_id, d52.id AS supplier_id, d52.code AS supplier_code, d52.name AS supplier_name, d53.code AS asset_code, d53.name AS asset_name, d54.name AS asset_location_name FROM res_receipt d1 LEFT OUTER JOIN product_classification d2 ON(d1.res_id = d2.id) LEFT OUTER JOIN stock_product d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN product d4 ON(d3.product_id = d4.id) LEFT OUTER JOIN stock_warehouse d5 ON(d1.rel_id = d5.id) LEFT OUTER JOIN res_version d6 ON(d1.id = d6.rel_id) LEFT OUTER JOIN res_user d7 ON(d1.create_uid = d7.id) LEFT OUTER JOIN stock_product d8 ON(d1.rel_id = d8.id)";

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
$sql = $sql." LEFT OUTER JOIN stock_package d40 ON((d8.stock_id = d40.rel_id OR d14.stock_id = d40.rel_id OR d15.stock_id = d40.rel_id OR d16.stock_id = d40.rel_id OR d17.stock_id = d40.rel_id OR d18.stock_id = d40.rel_id OR d19.stock_id = d40.rel_id OR d21.stock_id = d40.rel_id OR d22.stock_id = d40.rel_id OR d23.stock_id = d40.rel_id OR d24.stock_id = d40.id) AND d40.status =0) LEFT OUTER JOIN stock_package_product d41 ON(d40.id = d41.package_id AND d41.status =0) LEFT OUTER JOIN purchase_receive_product_local d50 ON((d8.rel_id = d50.id OR d14.rel_id = d50.id OR d15.rel_id = d50.id OR d16.rel_id = d50.id OR d17.rel_id = d50.id OR d18.rel_id = d50.id OR d19.rel_id = d50.id OR d20.rel_id = d50.id OR d21.rel_id = d50.id OR d22.rel_id = d50.id OR d23.rel_id = d50.id OR d24.rel_id = d50.id OR d25.rel_id = d50.id OR d26.rel_id = d50.id OR d27.rel_id = d50.id OR d28.rel_id = d50.id OR d29.rel_id = d50.id)  AND d50.status =0) LEFT OUTER JOIN purchase_receive_local d51 ON(d50.receive_id = d51.id) LEFT OUTER JOIN supplier d52 ON(d51.supplier_id = d52.id) LEFT OUTER JOIN asset d53 ON(d1.rel_id = d53.id) LEFT OUTER JOIN asset_location d54 ON(d53.location_id = d54.id)";
$sql = $sql." WHERE (d1.status =2 OR d1.status =3) AND d2.id IS NOT NULL";

if($rel_id != "")
{
	$arr = $appSession->getTool()->split($rel_id, ",");
	$rel_ids = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($arr[$i] == "")
		{
			continue;
		}
		if($rel_ids != "")
		{
			$rel_ids = $rel_ids." OR ";
		}
		$rel_ids = $rel_ids." d1.rel_id='".$arr[$i]."'";
		
	}
	$sql = $sql." AND (".$rel_ids.")";
}else{
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

	
}
if($classification_id != ""){
		$sql = $sql." AND d1.res_id ='".$classification_id."'";
	}else{
		if( $company_ids != "")
		{
			$sql = $sql." AND (".$company_ids.") ";
		}

	}
	
$sql = $sql." ORDER BY  d1.receipt_no ASC, d1.receipt_date ASC";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);


for($i =0; $i<$dt->getRowCount(); $i++)
{
	if($dt->getString($i, "package_id") != "")
	{
		continue;
	}
	$stock_id = $dt->getString($i, "stock_id");
	
	for($j =0; $j<$dt->getRowCount(); $j++)
	{
		
		if($dt->getString($j, "package_stock_id") == $stock_id)
		{
			$dt->setValue($dt->getString($j, "package_id"),$i, "package_id");
			$dt->setValue($dt->getString($j, "order_no"),$i, "order_no");
			$dt->setValue($dt->getString($j, "expiration_date"),$i, "expiration_date");
			$dt->setValue($dt->getString($j, "manufacture_date"),$i, "manufacture_date");
			$dt->setValue($dt->getString($j, "packing_date"),$i, "packing_date");
			$dt->setValue($dt->getString($j, "lot_number"),$i, "lot_number");
			$dt->setValue($dt->getString($j, "lot_code"),$i, "lot_code");
			break;
		}
	}
	
}


for($i =0; $i<$dt->getRowCount(); $i++)
{
	if($dt->getString($i, "supplier_id") != "")
	{
		continue;
	}
	$stock_product_id = $dt->getString($i, "stock_product_id");
	
	for($j =0; $j<$dt->getRowCount(); $j++)
	{
		if($dt->getString($j, "receive_product_rel_id") == $stock_product_id)
		{
			$dt->setValue($dt->getString($j, "supplier_id"),$i, "supplier_id");
			$dt->setValue($dt->getString($j, "supplier_code"),$i, "supplier_code");
			$dt->setValue($dt->getString($j, "supplier_name"),$i, "supplier_name");
			break;
		}
	}
	
}

$dt_classification = $appSession->getTool()->selectDistinctTable($dt, ["classification_id", "code", "name"]);



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
	
  <?php
  
    function childLevel($attribute)
	{
		$c = 0;
		if(count($attribute->children)>0)
		{
			$c = 1;
		}
		$j = 0;
		for($i = 0;$i<count($attribute->children); $i++)
		{
			$j = childLevel($attribute->children[$i]);
			
		}
		return $c + $j;
	}
	function countChild($attribute)
	{
		$c = 0;
		for($j=0; $j<count($attribute->children); $j++)
		{
			$c = $c + $attribute->children[$j]->columns;
		}
		for($i = 0;$i<count($attribute->children); $i++)
		{
			$c = $c + countChild($attribute->children[$i]);
			
		}
		return $c;
	}

	function sortAttribute($dt_att,$parent_id)
	{
		$rows =[];
		for($i =0; $i<$dt_att->getRowCount(); $i++)
		{
			if($dt_att->getString($i, "parent_id") == $parent_id)
			{
				$rows[count($rows)] = $dt_att->getDataRow($i);
				
				$childs = sortAttribute($dt_att, $dt_att->getString($i, "attribute_id"));
				for($j =0; $j<count($childs); $j++)
				{
					$rows[count($rows)] = $childs[$j];
				}
			}
		}
		return $rows;
	}
	class Attribute
	{
		public $id;
		public $parent = NULL;
		public $code;
		public $name;
		public $level = 0;
		public $columns = 1;
		public $children = [];
		public function __construct() {
			
		}
		public function add($attribute)
		{
			$attribute->parent = $this;
			$attribute->level = $this->level + 1;
			$this->children[count($this->children)] = $attribute;
			
		}
		public function findId($id)
		{
			if($this->id == $id)
			{
				return $this;
			}
			for($i =0; $i<count($this->children); $i++)
			{
				$found = $this->children[$i]->findId($id);
				if($found != NULL)
				{
					return $found;
				}
			}
			return NULL;
		}
	}
	function fillAttribute($appSession, $dt, $parent_id, $parent, $dt_condition, $dt_attribute_line_result)
	{
		for($i=0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "parent_id") == $parent_id)
			{
				if($dt->getString($i, "category_id") == "Value")
				{
					continue;
				}
				$attribute = new Attribute();
				$attribute_id = $dt->getString($i, "attribute_id");
				$attribute->id = $attribute_id;
				$attribute->code = $dt->getString($i, "code");
				$attribute->name = $dt->getString($i, "name");
				
				$columns = 1;
				$hasExpress = false;
				for($jj =0; $jj<$dt_attribute_line_result->getRowCount(); $jj++)
				{
					if($dt_attribute_line_result->getString($jj, "attribute_id") != $attribute_id)
					{
						continue;
					}
					
					
					for($rc = 0; $rc<$dt_condition->getRowCount(); $rc++)
					{
						if($dt_condition->getString($rc, "attribute_id") == $attribute_id && $appSession->getTool()->indexOf($dt_condition->getString($rc, "expression"), "{") != -1)
						{
							$hasExpress = true;
							break;
						}
					}
					$arr = $appSession->getTool()->split($dt_attribute_line_result->getString($jj, "condition"), "\t");
					$columns = count($arr);
		
					if($hasExpress == true)
					{
				
						if(count($arr) == 7)
						{
							$columns = 4;
							$value = $arr[0]."\t".$arr[4];
						}else if(count($arr)>2)
						{
							$columns= 2;
							$value = $arr[0];
						}else{
							$columns = 1;
						}
					}else{
						if(count($arr) == 7)
						{
							$columns = 1;
							$value = $arr[0]."\t".$arr[4];
						}else if(count($arr)>2)
						{
							$columns= 1;
							$value = $arr[0];
						}else{
							$columns = 1;
						}
						
					}
					
				}
			
				$attribute->columns= $columns;
				$parent->add($attribute);
				
				
				fillAttribute($appSession, $dt, $attribute->id, $attribute, $dt_condition, $dt_attribute_line_result);
				
			}
		}
		
	}
	function findAttributeLevel($attribute, $level)
	{
		$l =[];
		for($i = 0; $i<count($attribute->children); $i++)
		{
			if($attribute->children[$i]->level == $level)
			{
				$l[count($l)] = $attribute->children[$i];
			}
			$ll = findAttributeLevel($attribute->children[$i], $level);
			for($j =0; $j<count($ll); $j++)
			{
				$l[count($l)] = $ll[$j];
			}
		}
		return $l;
	}
	function findLastAttribute($attribute)
	{
		$l =[];
		for($i = 0; $i<count($attribute->children); $i++)
		{
			if(count($attribute->children[$i]->children) == 0)
			{
				$l[count($l)] = $attribute->children[$i];
			}
			$ll = findLastAttribute($attribute->children[$i]);
			for($j =0; $j<count($ll); $j++)
			{
				$l[count($l)] = $ll[$j];
			}
		}
		return $l;
	}
	function printAttribute($attribute)
	{
		echo $attribute->id.": ".$attribute->code.": oc: ".$attribute->name.", od: ".count($attribute->children).", level: ".$attribute->level."<br>";
		
		for($i =0; $i<count($attribute->children); $i++)
		{
			printAttribute($attribute->children[$i]);
		}
	}
	
  for($i =0; $i<$dt_classification->getRowCount(); $i++)
  {
  	$classification_id = $dt->getString($i, "classification_id");
	
	$classification_code = $dt->getString($i, "code");
	$classification_name = $dt->getString($i, "name");
	
	$sql = "SELECT d5.id AS receipt_id, d.id AS result_id, d1.id AS attribute_id, d1.parent_id, d5.res_id AS classification_id, d1.code, d1.name, d1.value, d2.code AS type_code, d.description, d1.category_id, d.rel_id";
	$sql= $sql." FROM res_attribute_line d LEFT OUTER JOIN res_attribute d1 ON(d.attribute_id = d1.id) LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
	$sql= $sql." LEFT OUTER JOIN res_receipt d5 ON(d.rel_id = d5.id)";
	$sql= $sql." WHERE d1.status =0 AND (d5.status =2 OR d5.status  = 3)";
	$sql = $sql." AND d5.receipt_date>='".$fdate."'";


	$sql = $sql." AND d5.receipt_date<='".$tdate."'";

	$sql = $sql." AND d5.res_id ='".$classification_id."'";
	
	$msg->add("query", $sql);

	$dt_attribute_line = $appSession->getTier()->getTable($msg);
	

	
	$sql = "SELECT d5.id AS receipt_id, d1.condition, d1.result, d2.attribute_id";
	$sql = $sql." FROM res_attribute_line_result d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.line_id = d2.id) LEFT OUTER JOIN res_receipt d5 ON(d2.rel_id = d5.id) WHERE d1.status =0 AND (d5.status =2 OR d5.status = 3)";
	$sql = $sql." AND d5.receipt_date>='".$fdate."'";
	$sql = $sql." AND d5.receipt_date<='".$tdate."'";
	
	
	$sql = $sql." AND d5.res_id ='".$classification_id."'";
	$msg->add("query", $sql);
	
	
	$dt_attribute_line_result = $appSession->getTier()->getTable($msg);	
	
	$sql = "SELECT d2.id AS attribute_id, d1.expression FROM res_condition d1 LEFT OUTER JOIN res_attribute d2 ON(d1.rel_id = d2.id) WHERE d1.status = 0 AND d2.rel_id ='".$classification_id."'";

	$msg->add("query", $sql);
	$dt_condition = $appSession->getTier()->getTable($msg);

	
	$sql = "SELECT d1.rel_id AS classification_id, d1.id AS attribute_id, d1.parent_id, d1.code, d1.name, d1.category_id , d1.value FROM res_attribute d1 WHERE d1.status =0 AND d1.rel_id ='".$classification_id."' ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);

	
	$dt_att = $appSession->getTier()->getTable($msg);
	

	$rows = sortAttribute($dt_att, "");
	$dt_attribute = new DataTable($dt_att->getName());
	$dt_attribute->setColumns($dt_att->getColumns());
	for($r =0; $r<count($rows); $r++)
	{
		$dt_attribute->addArray($rows[$r]);
	}
	$rootAttribute = new Attribute();
	fillAttribute($appSession, $dt_attribute, "", $rootAttribute, $dt_condition, $dt_attribute_line_result);
	$l=findLastAttribute($rootAttribute);
	$rowspan =  1;
	for($j =0; $j<count($l); $j++)
	{
		if($rowspan<$l[$j]->level)
		{
			$rowspan = $l[$j]->level;
		}
	}

	$hasPackage = false;
	for($j =0; $j<$dt->getRowCount(); $j++)
	{
		if($dt->getString($i, "classification_id") == $classification_id && $dt->getString($j, "package_stock_id") != "")
		{
			$hasPackage = true;
			break;
		}
	}
	
	$hasSupplier = false;
	for($j =0; $j<$dt->getRowCount(); $j++)
	{
		if($dt->getString($i, "classification_id") == $classification_id && $dt->getString($j, "supplier_id") != "")
		{
			$hasSupplier = true;
			break;
		}
	}
	
	$listAttribute = [];
	for($j =0; $j<$dt_attribute->getRowCount(); $j++)
	{
		if($dt_attribute->getString($j, "classification_id") == $classification_id)
		{
			$hasItem = false;
			for($k =0; $k<count($listAttribute); $k++)
			{
				if($dt_attribute->getString($j, "attribute_id") == $listAttribute[$k][0])
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
				$listAttribute[count($listAttribute)] = [$dt_attribute->getString($j, "attribute_id"), $dt_attribute->getString($j, "parent_id"), $dt_attribute->getString($j, "code"), $dt_attribute->getString($j, "name"), $dt_attribute->getString($j, "category_id"), 0, 1, $dt_attribute->getString($j, "value")];
			}
			
		}
	}
	
	
	for($ii =0; $ii<count($listAttribute); $ii++)
	{
		$c = 0;
		for($j=0; $j<count($listAttribute); $j++)
		{
			if($listAttribute[$j][1] == $listAttribute[$ii][0])
			{
				$c = $c + 1;
			}
		}
		$listAttribute[$ii][5] = $c;
		
		
	}
	$rows = 1;
	
	
	$arr = [];
	for($ii =0; $ii<count($listAttribute); $ii++)
	{
		 if($listAttribute[$ii][5] == "0")
		 {
			for($jj =0; $jj<count($listAttribute); $jj++)
			{
				if($listAttribute[$ii][1] == $listAttribute[$jj][0])
				{
					$hasItem = false;
					for($jjj =0; $jjj<count($arr); $jjj++)
					{
						if($arr[$jjj][0] == $listAttribute[$jj][0])
						{
							$hasItem = true;
							break;
						}
					}
					if($hasItem == false)
					{
						
						$listAttribute[$jj][1] = "";
						
						$arr[count($arr)] = $listAttribute[$jj];
					}
					break;
				}
			}
			$arr[count($arr)] = $listAttribute[$ii];
			
		 }
		 if($appSession->getTool()->indexOf($listAttribute[$ii][7], "{") != -1 && $appSession->getTool()->indexOf($listAttribute[$ii][7], "data_id") == -1)
		 {
			$p = $listAttribute[$ii];
			$p[0] = "".$ii;
			$p[1] = "";
			$p[3] = "Tổng";
			$p[6] = 1;
			$p[5] = 0;
			$arr[count($arr)] = $p;
			
			$c = $listAttribute[$ii];
			$c[0] = $c[0]."new";
			$c[1] = "".$ii;
			$c[6] = 1;
			$c[5] = 1;
			$arr[count($arr)] = $c;
			
		 }
		 
	}
	
	$listAttribute = $arr;
	
	
	for($ii =0; $ii<count($listAttribute); $ii++)
	{
		for($jj =0; $jj<$dt_attribute_line_result->getRowCount(); $jj++)
		{
			if($dt_attribute_line_result->getString($jj, "condition") == ""  )
			{
				//continue;
			}
			$attribute_id = $listAttribute[$ii][0];
			$attribute_id = $appSession->getTool()->replace($attribute_id, "new", "");
			if($dt_attribute_line_result->getString($jj, "attribute_id") != $attribute_id)
			{
				continue;
			}
			
			$hasExpress = false;
			for($rc = 0; $rc<$dt_condition->getRowCount(); $rc++)
			{
				if($dt_condition->getString($rc, "attribute_id") == $attribute_id && $appSession->getTool()->indexOf($dt_condition->getString($rc, "expression"), "{") != -1)
				{
					$hasExpress = true;
					break;
				}
			}
			$arr = $appSession->getTool()->split($dt_attribute_line_result->getString($jj, "condition"), "\t");
			
			
			$columns = count($arr);
			
			if($hasExpress == true)
			{
				
				if(count($arr) == 7)
				{
					$columns = 4;
					$value = $arr[0]."\t".$arr[4];
				}else if(count($arr)>2)
				{
					$columns= 2;
					$value = $arr[0];
				}else{
					$columns = 1;
				}
			}else{
				if(count($arr) == 7)
				{
					$columns = 1;
					$value = $arr[0]."\t".$arr[4];
				}else if(count($arr)>2)
				{
					$columns= 1;
					$value = $arr[0];
				}else{
					$columns = 1;
				}
				
			}
			if($columns>1)
			{
				for($j =0; $j<count($listAttribute); $j++)
				{
					if($listAttribute[$j][0] == $listAttribute[$ii][1])
					{
						$listAttribute[$j][6] = $columns;
						break;
					}
				}
			}
			$listAttribute[$ii][6] = $columns;
			$listAttribute[$ii][7] = $value;

			break;
		}
	}
	
	
	function printHeader($attribute, $level, $levels)
	{
		if(count($attribute->children) == 0)
		{
			return "";
		}
		$s = "<tr>";
		for($i =0; $i<count($attribute->children); $i++)
		{
			$att = $attribute->children[$i];
			$cols = countChild($att);
			if($cols ==0)
			{
				$cols = 1;
			}
			$rows = 1;
			if(count($attribute->children[$i]->children) == 0)
			{
				$rows = $levels - $level;
			}
			$s = $s."<td colspan=\"". $cols . "\" rowspan=\"". $rows . "\">";
				$s = $s.$attribute->children[$i]->name;
			$s = $s."</td>";
		}	
		$s = $s."</tr>";
		for($i =0; $i<count($attribute->children); $i++)
		{
			$s = $s.printHeader($attribute->children[$i], $level + 1,  $levels);
		}
		return $s;
	}
	
	
	
  ?>
  
	 <b><?php echo $classification_code;?>. <?php echo $classification_name;?></b>
	 <table width="100%" border="0" class="borderLine">
	  <tr class="header">
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap style="width:20px"><strong>Stt</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center" nowrap  style="width:40px"><strong>Số</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap style="width:60px"><strong>Ngày</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap  style="width:150px"><strong>Đối tượng</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center" nowrap  style="width:50px"><strong>Version</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap  style="width:50px"><strong>Vị trí </strong></td>
		<?php
		  if($hasSupplier == true)
		  {
		  ?>
	    <td colspan="2"  align="center"  nowrap  style="width:50px"><strong>Supplier</strong></td>
		<?php
		}
		?>
		<?php
		  if($hasPackage == true)
		  {
		  ?>
	    <td colspan="6" align="center"  nowrap  style="width:50px"><strong>Package</strong></td>
		<?php
		}
		?>
	    <?php
		  
		  $listAttribute = findLastAttribute($rootAttribute);
		  $cols = 0;
		  for($j =0; $j<count($listAttribute); $j++)
		  {
			$cols = $cols + $listAttribute[$j]->columns;
		  }
		  if($cols == 0)
		  {
			$cols= 1;
		  }
		  ?>
		 <td colspan="<?php echo $cols;?>" align="center"><strong>Nội dung kiểm tra</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap  style="width:50px"><strong>Kết luận</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center" nowrap  style="width:120px"><strong>Nhân viên </strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center" nowrap  style="width:30px"><strong>Xuất</strong></td>
	    <td rowspan="<?php echo $rowspan;?>" align="center"  nowrap  style="width:200px"><strong>Ghi chú</strong></td>
       </tr>
	   <tr class="header">
		<?php
		  if($hasSupplier == true)
		  {
		  ?>

		  <td align="center" rowspan="<?php echo $rowspan - 1 ;?>" nowrap  style="width:50px"><strong>Mã nhà cung cấp</strong></td>
		  <td align="center"  rowspan="<?php echo $rowspan - 1 ;?>" nowrap  style="width:50px"><strong>Tên nhà cung cấp</strong></td>
		  
		
		  <?php
		  }
		  ?>
		  <?php
		  if($hasPackage == true)
		  {
		  ?>
		  <td align="center"  rowspan="<?php echo $rowspan - 1 ;?>" nowrap  style="width:50px"><strong>Order No</strong></td>
		  <td align="center" rowspan="<?php echo $rowspan - 1 ;?>" nowrap  style="width:50px"><strong>Packing Date</strong></td>
		  <td align="center" rowspan="<?php echo $rowspan - 1;?>" nowrap  style="width:50px"><strong>Manufacture Date</strong></td>
		  <td align="center"  rowspan="<?php echo $rowspan - 1;?>" nowrap  style="width:50px"><strong>Expiration Date</strong></td>
		  <td align="center"  rowspan="<?php echo $rowspan - 1 ;?>"  nowrap  style="width:50px"><strong>Lot Number</strong></td>
		  <td align="center" rowspan="<?php echo $rowspan - 1;?>"  nowrap  style="width:50px"><strong>Lot Code</strong></td>
		<?php
		}
		$attributeList = findAttributeLevel($rootAttribute, 2);
		?>
		<?php
			for($rr = 0; $rr<count($attributeList); $rr++)
			{
				$attribute = $attributeList[$rr];
				
				
				$atts = $attribute->children;
				
				 $lastList = findLastAttribute($attribute);
				  $cols = 0;
				  for($j =0; $j<count($lastList); $j++)
				  {
					$cols = $cols + $lastList[$j]->columns;
				  }
				  if($cols == 0)
				  {
					$cols= 1;
				  }
				$rows = 1;
				if(count($attribute->children) == 0)
				{
					$rows = $rowspan - 1;
				}
				echo "<td colspan=\"". $cols . "\" rowspan=\"". $rows . "\" align=\"center\">";
					echo "<strong>".$attribute->name."</strong>";
				echo "</td>";
			}
		?>
		
		</tr>
		
	   <?php
	   for($r =3; $r<=$rowspan; $r++)
	   {
		 $attributeList = findAttributeLevel($rootAttribute, $r);
	   ?>
	   <tr class="header">
		<?php
			for($rr = 0; $rr<count($attributeList); $rr++)
			{
				$attribute = $attributeList[$rr];
				
				
				$atts = $attribute->children;
				
				$lastList = findLastAttribute($attribute);
				$cols = 0;
				for($j =0; $j<count($lastList); $j++)
				{
					$cols = $cols + $lastList[$j]->columns;
				}
				if(count($lastList) == 0)
				{
					$cols = $attribute->columns;
				}
				if($cols == 0)
				{
					$cols= 1;
				}
				$rows = 1;
				if(count($attribute->children) == 0)
				{
					$rows = $rowspan - $r+ 1;
				}
				echo "<td colspan=\"". $cols . "\" rowspan=\"". $rows . "\" align=\"center\">";
					echo "<strong>".$attribute->name."</strong>";
				echo "</td>";
			}
		?>
	   </tr>
	  
	   <?php
	   }
	   
	   ?>
	   
		 <?php
	
	$listAttribute = findLastAttribute($rootAttribute);
	   $no = 0;
	  for($j =0; $j<$dt->getRowCount(); $j++)
	  {
		if($dt->getString($j, "classification_id") != $classification_id)
		{
			continue;
		}
		
		$id = $dt->getString($j, "id");
		$receipt_no = $dt->getString($j, "receipt_no");
		
		$receipt_date = $dt->getString($j, "receipt_date");
		$receipt_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($receipt_date));
		$name = $dt->getString($j, "name");
		$code = $dt->getString($j, "code");
		

		$rel_name = $dt->getString($j, "product_name");
		if($rel_name != "")
		{
			$rel_name = $dt->getString($j, "product_code")." ". $rel_name;
		}else{
			$rel_name = $dt->getString($j, "warehouse_name");
		}
		if($dt->getString($j, "asset_code") != "")
		{
			$rel_name = $dt->getString($j, "asset_code")." ". $dt->getString($j, "asset_name");
		}
		$version = $dt->getString($j, "version");
		$user_name = $dt->getString($j, "user_name");
		$account_name = $dt->getString($j, "account_name");
		$barcode = $dt->getString($j, "barcode1");
		if($dt->getString($j, "barcode2") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode2");
		}
		if($dt->getString($j, "barcode3") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode3");
		}
		if($dt->getString($j, "barcode4") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode4");
		}
		if($dt->getString($j, "barcode5") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode5");
		}
		if($dt->getString($j, "barcode6") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode6");
		}
		if($dt->getString($j, "barcode7") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode7");
		}
		if($dt->getString($j, "barcode8") != ""){
			$barcode = $barcode."/".$dt->getString($j, "barcode8");
		}
		if($dt->getString($j, "barcode9") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode9");
		}
		if($dt->getString($j, "barcode10") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode10");
		}
		if($dt->getString($j, "barcode11") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode11");
		}
		if($dt->getString($j, "barcode12") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode12");
		}
		if($dt->getString($j, "barcode13") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode13");
		}
		if($dt->getString($j, "barcode14") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode14");
		}
		if($dt->getString($j, "barcode15") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode15");
		}
		if($dt->getString($j, "barcode16") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode16");
		}
		if($dt->getString($j, "barcode17") != ""){
			if($barcode!= "")
			{
				$barcode = $barcode."/";
			}
			$barcode = $barcode.$dt->getString($j, "barcode17");
		}
		if($dt->getString($j, "asset_location_name") != "")
		{
			$barcode = $dt->getString($j, "asset_location_name");
		}
		$result_receipt_yes = "";
		$result_receipt_no = "";
		
		for($jj =0; $jj<$dt_attribute_line_result->getRowCount(); $jj++)
		{
			if($dt_attribute_line_result->getString($jj, "receipt_id") == $id)
			{
				if($dt_attribute_line_result->getString($jj, "result") == "1")
				{
					$result_receipt_yes = "1";
				}else if($dt_attribute_line_result->getString($jj, "result") == "0")
				{
					$result_receipt_no = "1";
					break;
				}
			}
		}
		if($version == "")
		{
			$version = "0";
		}
		if($error == "1" && $classification_id != "1d9de800-17f3-4eea-c7a9-46e3d2ef1da8")
		{
			if($result_receipt_no != "1" )
			{
				continue;
			}
		}
		$no = $no + 1;
		
	  ?>
	  <tr >
		<td style="vertical-align:middle" align="center"><?php echo $no;?>.</td>
		<td  style="vertical-align:middle"><a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>" target="_blank"><?php echo $receipt_no;?></a> </td>
		<td style="vertical-align:middle" nowrap="nowrap"><?php echo $receipt_date;?></td>
		<td style="vertical-align:middle"><?php echo $rel_name;?></td>
		 <td style="vertical-align:middle; text-align:center"><?php echo $version;?></td>
		  <td style="vertical-align:middle;"><?php echo $barcode;?></td>
		  <?php
		  if($hasSupplier == true)
		  {
			$supplier_code = $dt->getString($j, "supplier_code");
			$supplier_name = $dt->getString($j, "supplier_name");
		  ?>
		  <td  style="vertical-align:middle"><?php echo $supplier_code;?></td>
		  <td  style="vertical-align:middle"><?php echo $supplier_name;?></td>
		  <?php
		  }
		  ?>
		  
		  <?php
		  if($hasPackage == true)
		  {
			$order_no = $dt->getString($j, "order_no");
			$packing_date = $dt->getString($j, "packing_date");
			$manufacture_date = $dt->getString($j, "manufacture_date");
			$expiration_date = $dt->getString($j, "expiration_date");
			$lot_number = $dt->getString($j, "lot_number");
			$lot_code = $dt->getString($j, "lot_code");
			
		  ?>
		  <td  style="vertical-align:middle"><?php echo $order_no;?></td>
		  <td  style="vertical-align:middle"><?php echo $packing_date;?></td>
		  <td  style="vertical-align:middle"><?php echo $manufacture_date;?></td>
		  <td  style="vertical-align:middle"><?php echo $expiration_date;?></td>
		  <td  style="vertical-align:middle"><?php echo $lot_number;?></td>
		  <td  style="vertical-align:middle"><?php echo $lot_code;?></td>
		  <?php
		  }
		  ?>
		  <?php
		  for($jjj =0; $jjj<count($listAttribute); $jjj++)
		  {
			$attribute_id = $listAttribute[$jjj]->id;
			$attribute_code = $listAttribute[$jjj]->code;
			
			$description = "";
			
			for($n = 0;$n<$dt_attribute_line->getRowCount(); $n++)
			{
				if($dt_attribute_line->getString($n, "attribute_id") == $attribute_id && $dt_attribute_line->getString($n, "receipt_id") == $id)
				{
					$description = $dt_attribute_line->getString($n, "description");
					break;
				}
			}
			$resultList = [];
			for($n = 0;$n<$dt_attribute_line_result->getRowCount(); $n++)
			{
				if($dt_attribute_line_result->getString($n, "attribute_id") == $attribute_id && $dt_attribute_line_result->getString($n, "receipt_id") == $id)
				{
					
					$resultList =  $appSession->getTool()->split($dt_attribute_line_result->getString($n, "condition"), "\t");
				}
			}
			
			$index = $appSession->getTool()->indexOf($description, ";");
			if($index != -1)
			{
				$listDescript = $appSession->getTool()->split($description, ";");
				$description = "";
				for($kk = 0; $kk<count($listDescript); $kk++)
				{
					$index = $appSession->getTool()->indexOf($listDescript[$kk], "=");
					if($description != "")
					{
						$description = $description."; ";
					}
					if($index != -1)
					{
						$description = $description.$appSession->getTool()->subString($listDescript[$kk], $index + 1);
					}else{
						$description = $description.$listDescript[$kk];
					}
				}
			}
			
			$index = $appSession->getTool()->indexOf($description, "=");
			if($index != -1)
			{
				$description = $appSession->getTool()->subString($description, $index + 1);
			}
			$index = $appSession->getTool()->indexOf($description, ";");
			if($index != -1)
			{
				$description = $appSession->getTool()->subString($description, $index + 1);
			}
			
			
		  ?>
			<td><?php echo $description;?></td>
			<?php
			$arr = [];
			if(count($resultList)== 7 )
			{
				$arr = [$resultList[0]];
			}else if(count($resultList)>2)
			{
				$arr = [$resultList[0]];
			}else if(count($resultList)>0)
			{
				$arr = [$resultList[0]];
			}
			if($attribute_code == "new")
			{
				print_r($arr);
			}
			
			for($cc =0; $cc<$listAttribute[$jjj]->columns-1; $cc++)
			{
				
				$v1 = "";
				if(count($arr)>$cc)
				{
					$v1 = $arr[$cc];
				}
				if($appSession->getTool()->isNumber($v1))
				{
					$v1 = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($v1));
				}
					
					
				?>
				
				<td style="text-align:center" ><?php echo $v1;?></td>
				
				<?php
				
				
				
			?>
		  <?php
				}
			}
			
		  ?>
		  <td style="vertical-align:middle; text-align:center"><?php if($result_receipt_no == "1"){ echo " Không đạt";}else if($result_receipt_yes == "1"){ echo "Đạt"; }?></td>
		 <td style="vertical-align:middle; text-align:left"><?php echo $user_name;?>. <?php echo $account_name;?></td>
		 <td style="vertical-align:middle; text-align:left" nowrap="nowrap"><a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>&view_type=excel" target="_blank">Excel</a> | <a href="<?php echo URL;?>api/action/?action_id=3efb7875-2126-4eb2-fe47-47df8897570d&data=<?php echo $appSession->getTool()->urlEncode("id=".$id);?>&view_type=word" target="_blank">Word</a></td>
		
		 <td style="vertical-align:middle"></td>
	  </tr>
	 
	  <?php
		
	  }
	  
	  ?>

	  </table>
	  <?php
	  }
	  ?>
	 

