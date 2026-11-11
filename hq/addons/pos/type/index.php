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
if($ac == "view")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}

	$price_id = '';
	if(isset($_REQUEST['price_id']))
	{
		$price_id = $_REQUEST['price_id'];
	}
?>
<div class="row ">
	<div class="col m3">
	<div class="input-group">	
		<input type="text" class="input" placeholder="Search..." id="editpos_service_search" value="" onKeyDown="if(event.keyCode == 13){loadProduct();}" />
		<button onclick="loadProduct()" class="button btn-secondary"><?php echo $appSession->getLang()->find("Search");?></button>
	</div>
	</div>
	<div class="col m9">
	
	</div>
</div>
<br>
<div class="row ">
	<div class="col m12">
		<div id="pnService"></div>
	</div>
</div>
<script>


	function loadProduct()
	{
		var search = document.getElementById('editpos_service_search').value;
		
		var _url = '<?php echo URL;?>addons/pos/type/?ac=list&price_id=<?php echo $price_id;?>&rel_id=<?php echo $rel_id;?>';
		_url = _url+ "&search=" + encodeURIComponent(search);

		loadPage('pnService', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	loadProduct();
</script>
<?php
}else if($ac == "list")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}

	$price_id = '';
	if(isset($_REQUEST['price_id']))
	{
		$price_id = $_REQUEST['price_id'];
	}
	$search = '';
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$sql = "SELECT product_id FROM product_price WHERE id='".$price_id."'";
	$msg->add("query", $sql);
	$product_id = $appSession->getTier()->getValue($msg);

	
	$sql = "SELECT d1.id, d1.type_id, d2.name, d1.factor FROM product_price d1 LEFT OUTER JOIN product_type d2 ON(d1.type_id = d2.id) WHERE d1.status =0 AND d1.factor !=0 AND d1.type_id != '' AND d1.product_id='".$product_id."'";
	
	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d2.name"], $search).")";
	}
	$sql = $sql." ORDER BY d1.sequence ASC, d2.name ASC";

	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
?>
<div class="row">
	<div class="col m12 responsive" >
		<table class="table">
		<td></td>
		<td><?php echo $appSession->getLang()->find("Name");?></td>
		<td>%</td>
	
		<?php
		for ($i = 0; $i < $dt->getRowCount(); $i++) 
		{

			$id = $dt->getString($i, "id");
			$type_id = $dt->getString($i, "type_id");
			$name = $dt->getString($i, "name");
			$percent = $dt->getFloat($i, "factor");
			

		?>
		<tr>
			<td><a href="javascript:addType('<?php echo $rel_id;?>', '<?php echo $type_id;?>', <?php echo $percent;?>)"><i class="icon-plus"></i></td>
			<td><?php echo $name;?></td>
			<td><?php echo $appSession->getFormats()->getPERCENT()->format($percent);?></td>
		
		</tr>
		<?php
			}
		?>
		</table>
	</div>
</div>
<?php
}
else if($ac == "removeType")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE attribute_line SET status=1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addType")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$type_id = '';
	if(isset($_REQUEST['type_id']))
	{
		$type_id = $_REQUEST['type_id'];
	}
	$percent = '0';
	if(isset($_REQUEST['percent']))
	{
		$percent = $_REQUEST['percent'];
	}
	
	
	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO attribute_line(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", create_uid";
	$sql = $sql.", rel_id";
	$sql = $sql.", type_id";
	$sql = $sql.", factor";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$appSession->getConfig()->getProperty("user_id")."'";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$type_id."'";
	$sql = $sql.", ".$percent;
	$sql = $sql.")";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>

		