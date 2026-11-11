<?php
$config = new AppConfig("");
$config->parseWith($data, "&");
$ac = $config->getProperty("ac");
if($ac == "")
{
	$ac = "view";
}

if($ac == "view")
{

	$sql = "SELECT name FROM res_meta WHERE rel_id='".$config->getProperty("id")."' AND type='Sticker'";
	$msg->add("query", $sql);
	$name = $appSession->getTier()->getValue($msg);
	
?>
	<div class="row">
		<div class="col m12">
			<select class="select" onchange="saveSticker(this)">
				<option value=""></option>
				<option value="new" <?php if($name== "new"){ echo " selected "; }?>>New</option>
				<option value="sale" <?php if($name== "sale"){ echo " selected "; }?>>Sale</option>
			</select>
		</div>
	</div>
	<br />
	
	<script>
		function saveSticker(theInput)
		{
			var _url ="<?php echo URL;?>api/action/?action_id=<?php echo $action_id;?>&data=" + encodeURIComponent('ac=add&rel_id=<?php echo $config->getProperty("id");?>&name=' + theInput.value);
			loadPage('pnListProduct', _url, function(status, message)
			{
				if(status== 0)
				{
				
					
				}
				
			}, true);
		}
		
	</script>
	<?php
	
}else if($ac == "add")
{
	$rel_id = $config->getProperty("rel_id");
	$name = $config->getProperty("name");
	
	$sql = "SELECT id FROM res_meta WHERE rel_id='".$rel_id."' AND type='Sticker'";
	$msg->add("query", $sql);
	$id = $appSession->getTier()->getValue($msg);
	if($id == "")
	{
		$builder = $appSession->getTier()->createBuilder("res_meta");
		$id = $appSession->getTool()->getId();
		
		$builder->add("id", $id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$builder->add("rel_id", $rel_id);
		$builder->add("name", $name);
		$builder->add("type", "Sticker");
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}else{
		$sql = "UPDATE res_meta SET name='".$name."' WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}

	
	
	echo "OK";
}
?>