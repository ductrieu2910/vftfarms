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

	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$service_category_id = '';
	if(isset($_REQUEST['service_category_id']))
	{
		$service_category_id = $_REQUEST['service_category_id'];
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
		
		var _url = '<?php echo URL;?>addons/pos/service/?ac=list&category_id=<?php echo $category_id;?>&rel_id=<?php echo $rel_id;?>&$service_category_id=<?php echo $service_category_id;?>';
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

	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$search = '';
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$service_category_id = '';
	if(isset($_REQUEST['service_category_id']))
	{
		$service_category_id = $_REQUEST['service_category_id'];
	}
	
	$sql = "SELECT d1.id, d1.name, d1.percent, d1.value, d2.name AS category_name FROM account_service d1 LEFT OUTER JOIN account_service_category d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='ROOT')";
	if($service_category_id != "")
	{
		$sql = $sql." AND d2.name ='".$service_category_id."'";
	}
	
	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.name", "d2.name"], $search).")";
	}
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	$sql = $sql." LIMIT 100";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
?>
<div class="row">
	<div class="col m12 responsive" >
		<table class="table">
		<td></td>
		<td><?php echo $appSession->getLang()->find("Name");?></td>
		<td><?php echo $appSession->getLang()->find("Percent");?></td>
		<td><?php echo $appSession->getLang()->find("Value");?></td>
		
		<?php
		for ($i = 0; $i < $dt->getRowCount(); $i++) 
		{

			$id = $dt->getString($i, "id");
			$name = $dt->getString($i, "name");
			$percent = $dt->getFloat($i, "percent");
			$value = $dt->getFloat($i, "value");
			$category_name = $dt->getString($i, "category_name");

		?>
		<tr>
			<td><a href="javascript:addService('<?php echo $rel_id;?>', '<?php echo $id;?>', <?php echo $percent;?>, <?php echo $value;?>, '<?php echo $category_id;?>')"><i class="icon-plus"></i></td>
			<td><?php echo $name;?></td>
			<td><?php echo $appSession->getFormats()->getPERCENT()->format($percent);?></td>
			<td><?php echo $appSession->getFormats()->getDOUBLE()->format($value);?></td>
			

		</tr>
		<?php
			}
		?>
		</table>
	</div>
</div>
<?php
}
else if($ac == "removeService")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE account_service_line_local SET status=1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addService")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$service_id = '';
	if(isset($_REQUEST['service_id']))
	{
		$service_id = $_REQUEST['service_id'];
	}
	$percent = '0';
	if(isset($_REQUEST['percent']))
	{
		$percent = $_REQUEST['percent'];
	}
	$value = '0';
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$operator = "+";
	$sequence = 0;
	$sql = "SELECT d2.operator, d2.sequence FROM account_service d1 LEFT OUTER JOIN account_service_category d2 ON(d1.category_id = d2.id) WHERE d1.id='".$service_id."'";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if(count($arr)>0)
	{
		$operator =$arr[0][0];
		$sequence =$arr[0][1];
	}
	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO account_service_line_local(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", create_uid";
	$sql = $sql.", rel_id";
	$sql = $sql.", service_id";
	$sql = $sql.", percent";
	$sql = $sql.", value";
	$sql = $sql.", operator";
	$sql = $sql.", sequence";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$appSession->getConfig()->getProperty("user_id")."'";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$service_id."'";
	$sql = $sql.", ".$percent;
	$sql = $sql.", ".$value;
	$sql = $sql.", '".$operator."'";
	$sql = $sql.", ".$sequence;
	$sql = $sql.")";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>

		