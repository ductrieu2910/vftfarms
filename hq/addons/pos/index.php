<?php
$msg = $appSession->getTier()->createMessage();

$sale_id = "";
if(isset($_REQUEST['sale_id']))
{
	$sale_id = $_REQUEST['sale_id'];
}
$rel_id = $sale_id;
$sql = "SELECT rel_id FROM sale_local WHERE id='".$sale_id."'";
$msg->add("query", $sql);
$sValue = $appSession->getTier()->getValue($msg);
if($sValue != "")
{
	$rel_id = $rel_id.";".$sValue;
}
$sql = "SELECT d1.id, d1.name FROM product_category d1 WHERE d1.status =0 AND d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' AND (d1.parent_id='' OR d1.parent_id IS NULL)";
$sql = $sql." ORDER BY d1.sequence ASC";

$msg->add("query", $sql);
$dt_category = $appSession->getTier()->getTable($msg);

?>
<?php
for($i =0; $i<$dt_category->getRowCount(); $i++)
{
	$category_id = $dt_category->getString($i, "id");
	echo "<br>";
?>
<a href="javascript:productList('', '<?php echo $category_id;?>');"><?php echo $dt_category->getString($i, "name");?> <i class="icon-plus"></i></a>
<br>
<div id="pnSale<?php echo $category_id;?>" style="padding-top:5px"></div>
<?php
}
?>
<script>
function loadSale(category_id)
{
	var _url = '<?php echo URL;?>addons/pos/line/?sale_id=<?php echo $sale_id;?>&category_id=' + category_id;
	
	loadPage('pnSale' + category_id, _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
<?php
for($i =0; $i<$dt_category->getRowCount(); $i++)
{
	$category_id = $dt_category->getString($i, "id");
	
?>
loadSale('<?php echo $category_id;?>');
<?php
}
?>
var sale_product_id = "";
function productList(parent_id, category_id)
{
	sale_product_id = "";
	var _url = '<?php echo URL;?>addons/pos/product/?rel_id=<?php echo $appSession->getTool()->urlEncode($rel_id);?>&sale_product_parent_id=' + parent_id;
	_url = _url + '&category_id=' + category_id;
	openPopup(_url);
}
function productListEdit(saleproductid, category_id)
{
	sale_product_id = saleproductid;
	var _url = '<?php echo URL;?>addons/pos/product/?rel_id=<?php echo $appSession->getTool()->urlEncode($rel_id);?>&sale_product_parent_id=';
	_url = _url + '&category_id=' + category_id;
	openPopup(_url);
}
function addProduct(parent_id, price_id, quantity, category_id)
{
	if(sale_product_id == "")
	{
		if(parent_id == "")
		{
			var quantity = prompt("Please enter your quantity", quantity);

			if (quantity == null) {
			  return;
			}
		}
		if(numeric(quantity) == false)
		{
			quantity =1
		}
		var _url = '<?php echo URL;?>addons/pos/action/?ac=addProduct&sale_id=<?php echo $sale_id;?>';
		_url = _url + "&parent_id=" + parent_id;
		_url = _url + "&price_id=" + price_id;
		_url = _url + "&quantity=" + quantity;

		loadPage('pnSale', _url, function(status, message)
		{
			if(status== 0)
			{
				loadSale(category_id);
				
			}
			
		}, true);
	}else{
		var _url = '<?php echo URL;?>addons/pos/action/?ac=updateProduct&id=' + sale_product_id;
		_url = _url + "&price_id=" + price_id;

		
		loadPage('pnSale', _url, function(status, message)
		{
			if(status== 0)
			{
				closePopup();
				loadSale(category_id);
				
			}
			
		}, true);
	}
	
}
function quantityChanged(theInput, id, qty, category_id)
{
	
	var quantity = theInput.value;
	if(numeric(quantity) == false)
	{
		quantity =1
	}
	if(quantity <0)
	{
		quantity = 1;
	}
	if(qty == quantity)
	{
		return;
	}
	var _url = '<?php echo URL;?>addons/pos/action/?ac=updateProductQuantity&id=' + id;
	_url = _url + "&quantity=" + quantity;
	
	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadSale(category_id);
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function delProduct(id, category_id)
{
	var result = confirm("Want to delete?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>addons/pos/action/?ac=delProduct&id=' + id;

	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadSale(category_id);
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function typeChanged(theInput, sale_product_id)
{
	var _url = '<?php echo URL;?>addons/pos/action/?ac=typeChanged&sale_product_id=' + sale_product_id;
	_url= _url + '&price_id=' + theInput.value;
	
	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadSale();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function showService(rel_id, category_id, service_category_id)
{
	var _url = '<?php echo URL;?>addons/pos/service/?rel_id=' + rel_id + '&category_id=' + category_id;
	_url = _url + '&service_category_id=' + service_category_id;
	openPopup(_url);
}
function addService(rel_id, service_id, percent, value, category_id)
{
	var _url = '<?php echo URL;?>addons/pos/service/?ac=addService&rel_id=' + rel_id + '&service_id=' + service_id;
	_url = _url + '&percent=' + encodeURIComponent(percent);
	_url = _url + '&value=' + encodeURIComponent(value);
	
	loadPage('gotoTop', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") !=-1 )
			{
				
				closePopup();
				loadSale(category_id);
				
			}else{
				alert(message);
			}
		}
		
	}, true);
}
function delService(id, category_id)
{
	var _url = '<?php echo URL;?>addons/pos/service/?ac=removeService&id=' + id;
	
	loadPage('gotoTop', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") != -1)
			{
				loadSale(category_id);
				
			}else{
				alert(message);
			}
		}
		
	}, true);
}
function showType(rel_id, price_id)
{
	var _url = '<?php echo URL;?>addons/pos/type/?rel_id=' + rel_id + '&price_id=' + price_id;
	openPopup(_url);
}
function addType(rel_id, type_id, percent)
{
	var _url = '<?php echo URL;?>addons/pos/type/?ac=addType&rel_id=' + rel_id + '&type_id=' + type_id;
	_url = _url + '&percent=' + encodeURIComponent(percent);
	
	
	loadPage('gotoTop', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") !=-1 )
			{
				
				
				loadSale();
				
			}else{
				alert(message);
			}
		}
		
	}, true);
}
function delType(id)
{
	var _url = '<?php echo URL;?>addons/pos/type/?ac=removeType&id=' + id;
	
	loadPage('gotoTop', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") != -1)
			{
				loadSale();
				
			}else{
				alert(message);
			}
		}
		
	}, true);
}

</script>