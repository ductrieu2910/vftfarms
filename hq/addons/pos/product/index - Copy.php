<?php
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}

if($ac == "view")
{
	$sale_product_parent_id = '';
	if(isset($_REQUEST['sale_product_parent_id']))
	{
		$sale_product_parent_id = $_REQUEST['sale_product_parent_id'];
	}
?>
<div class="row ">
	<div class="col m3">
	<div class="input-group">	
		<input type="text" class="input" placeholder="Search..." id="editpos_product_search" value="" onKeyDown="if(event.keyCode == 13){loadProduct();}" />
		<button onclick="loadProduct()" class="button btn-secondary"><?php echo $appSession->getLang()->find("Search");?></button>
	</div>
	</div>
	<div class="col m9">
	<div id="pnCategory"></div>
	</div>
</div>
<div class="row ">
	<div class="col m12">
		<div id="pnProduct"></div>
	</div>
</div>
<script>
	var category_id = "";
	function loadCategory(id)
	{
		category_id = id;
		var _url = '<?php echo URL;?>addons/pos/product/?ac=category&parent_id=' + id;
		loadPage('pnCategory', _url, function(status, message)
		{
			if(status== 0)
			{
				loadProduct();
				
			}
			
		}, false);
	}
	loadCategory("");
	function loadProduct()
	{
		var search = document.getElementById('editpos_product_search').value;
		
		var _url = '<?php echo URL;?>addons/pos/product/?ac=product&category_id=' + category_id;
		_url = _url+ "&search=" + encodeURIComponent(search);
		_url = _url+ "&rel_id=" + encodeURIComponent('<?php echo $rel_id;?>');
		_url = _url+ "&sale_product_parent_id=<?php echo $sale_product_parent_id;?>";
		loadPage('pnProduct', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
</script>

<?php
}else if($ac == "category")
{
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$sql = "SELECT d1.id, d1.name FROM product_category d1 WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') AND d1.parent_id='".$parent_id."'";
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	?>
	<table>
	<tr>
	<?php 
	if($parent_id != "")
	{
		
	?>
	<td>
	<a href="javascript:loadCategory('');"><i class="icon-home"></i></a>
	<?php
	while(true)
	{
		$sql = "SELECT d1.id, d1.parent_id, d1.name FROM product_category d1 WHERE d1.id='".$parent_id."'";
		$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			
			?>
			/ <a href="javascript:loadCategory('<?php echo $arr[0][0];?>');"><?php echo $arr[0][2];?></a>
			<?php
			$parent_id = $arr[0][1];
			if($parent_id == "")
			{
				break;
			}
		}else{
			break;
		}
	}
	?>
	</td>
	<?php
	}
	?>
	<td>
	<select type="select" class="select" onchange="loadCategory(this.value)">
	<option value=""></option>
	<?php
	for($i=0; $i<count($values); $i++)
	{
	?>
	<option value="<?php echo $values[$i][0];?>"><?php echo $values[$i][1];?></option>
	<?php
	}
	?>
	</select>
	</td>
	</tr>
	</table>
	<?php
	
}else if($ac == "product")
{


$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
$category_id = '';
if(isset($_REQUEST['category_id']))
{
	$category_id = $_REQUEST['category_id'];
}
$sale_product_parent_id = '';
if(isset($_REQUEST['sale_product_parent_id']))
{
	$sale_product_parent_id = $_REQUEST['sale_product_parent_id'];
}
$search = '';
if(isset($_REQUEST['search']))
{
	$search = $_REQUEST['search'];
}

function findChild($dt, $parent_id)
{
    $ids = "";
    for ($i = 0; $i < $dt->getRowCount(); $i++) {
        if ($dt->getString($i, "parent_id") == $parent_id) {
            if ($ids != "") {
                $ids = $ids . ",";
            }
            $ids = $ids . $dt->getString($i, "id");
            $s1 = findChild($dt, $dt->getString($i, "id"));
            if ($s1 != "") {
                $ids = $ids . "," . $s1;
            }
        }
    }
    return $ids;
}
$ids = "";
if($category_id != "")
{
	$sql = "SELECT id, parent_id FROM product_category WHERE (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("company_id")."') AND status =0";
	$msg->add("query", $sql);
	$dt_category = $appSession->getTier()->getTable($msg);
	$ids = findChild($dt_category, $category_id);
	if ($ids != "") {
		$ids = $ids . "," . $category_id;
	} else {
		$ids = $category_id;
	}
	$arr = $appSession->getTool()->split($ids, ',');
	$ids = "";
	for ($i = 0; $i < count($arr); $i++) {
		if ($ids != "") {
			$ids = $ids . " OR ";
		}
		$ids = $ids . " d1.category_id='" . $arr[$i] . "'";
	}
}

$sql = "SELECT d1.id, d5.unit_id, d5.attribute_id,	d5.type_id, d1.code, d1.name, lg.description AS name_lg, d2.document_id, d5.unit_price, d5.old_price, d6.name AS unit_name, d5.currency_id";
$sql = $sql.", d9.name AS attribute_name, d10.name attribute_category_name, d5.factor"; 
$sql = $sql.", d5.id AS price_id, d8.document_id AS price_document_id, d5.description, d11.name AS type_name, 0.0 AS unit_in_stock, d12.name AS category_name, d13.name parent_category_name";
$sql = $sql." FROM product d1";
$sql = $sql." LEFT OUTER JOIN poster d2 ON(d1.id = d2.rel_id AND d2.status =0)";
$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='product_name' AND lg.status =0)";
$sql = $sql." LEFT OUTER JOIN product_price d5 ON(d1.id = d5.product_id AND d5.status =0) LEFT OUTER JOIN product_unit d6 ON(d5.unit_id = d6.id)";
$sql = $sql." LEFT OUTER JOIN poster d8 ON(d5.id = d8.rel_id AND d8.publish=1 AND d8.status =0)";
$sql = $sql." LEFT OUTER JOIN attribute d9 ON(d5.attribute_id = d9.id)";
$sql = $sql." LEFT OUTER JOIN attribute_category d10 ON(d9.category_id = d10.id)";
$sql = $sql." LEFT OUTER JOIN product_type d11 ON(d5.type_id = d11.id)";
$sql = $sql." LEFT OUTER JOIN product_category d12 ON(d1.category_id = d12.id)";
$sql = $sql." LEFT OUTER JOIN product_category d13 ON(d12.parent_id = d13.id)";
$sql = $sql." WHERE d1.status =0 AND d5.status =0 AND (d5.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d5.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";

if($ids != "")
{
	$sql = $sql." AND (".$ids.")";
}
if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.name", "d1.code", "d9.name", "d11.name", "lg.description"], $search).")";
}
if($rel_id == "")
{
	$sql = $sql." AND d5.type='PRODUCT'";
}else{
	$arr = $appSession->getTool()->split($rel_id, ";");
	$rels = "";
	for($i=0; $i<count($arr); $i++)
	{
		if($arr[0] == "")
		{
			continue;
		}
		if($rels != "")
		{
			$rels = $rels." OR ";
		}
		$rels = $rels." d5.rel_id='".$arr[$i]."'";
	}
	$sql = $sql." AND (".$rels.")";
}

$sql = $sql." ORDER BY d1.sequence ASC, d5.sequence ASC";
$sql = $sql." LIMIT 100";
$msg->add("query", $sql);
$productList = $appSession->getTier()->getTable($msg);


?>
<div class="row">
	<div class="col m12 responsive" >
<table class="table">
<td></td>
<td><?php echo $appSession->getLang()->find("Code");?></td>
<td><?php echo $appSession->getLang()->find("Name");?></td>
<td><?php echo $appSession->getLang()->find("Attribute");?></td>
<td><?php echo $appSession->getLang()->find("Type");?></td>
<td><?php echo $appSession->getLang()->find("Unit");?></td>
<td><?php echo $appSession->getLang()->find("Unit Price");?></td>
<td><?php echo $appSession->getLang()->find("Factor");?></td>
<td><?php echo $appSession->getLang()->find("Category");?></td>
<?php
for ($i = 0; $i < $productList->getRowCount(); $i++) {

	$product_id = $productList->getString($i, "id");
	$code = $productList->getString($i, "code");
	$document_id = $productList->getString($i, "document_id");
	$name = $productList->getString($i, "name");

	if ($name == "") {
		$name = $productList->getString($i, "name");
	}
	$unit_price = $productList->getFloat($i, "unit_price");
	$old_price = $productList->getFloat($i, "old_price");
	$unit_id = $productList->getFloat($i, "unit_id");
	$currency_id = $productList->getFloat($i, "currency_id");
	$unit_name = $productList->getString($i, "unit_name");
	$rel_id = $productList->getString($i, "price_id");

	$attribute_category_name = $productList->getString($i, "attribute_category_name");
	$attribute_id = $productList->getString($i, "attribute_id");
	$attribute_name = $productList->getString($i, "attribute_name");
	$type_name = $productList->getString($i, "type_name");
	$factor = $productList->getString($i, "factor");
	$type_id = $productList->getString($i, "type_id");	
	if($factor == "")
	{
		$factor = "1";
	}
	$category_name = $productList->getString($i, "category_name");
	$parent_category_name = $productList->getString($i, "parent_category_name");

?>
<tr>
	<td><a href="javascript:addProduct('<?php echo $sale_product_parent_id;?>', '<?php echo $rel_id;?>', 1)"><i class="icon-plus"></i></td>
	<td><?php echo $code;?></td>
	<td><?php echo $name;?></td>
	<td><?php echo $attribute_name;?></td>
	<td><?php echo $type_name;?></td>
	<td><?php echo $unit_name;?></td>
	<td><?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?></td>
	<td><?php echo $appSession->getFormats()->getPERCENT()->format($factor);?></td>
	<td><?php echo $category_name;?><?php if($parent_category_name != ""){ echo " / ".$parent_category_name;}?><td>
</tr>
<?php
	}
?>
</table>
	</div>
</div>
<?php
}
?>
		