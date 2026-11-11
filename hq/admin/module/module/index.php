<?php
$msg = $appSession->getTier()->createMessage();
$ac = "";
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
	
	$config = new AppConfig("");
	$config->parseWith($default, '&');
	$sub_module_id = $config->getProperty("module_id");
	$sub_table_name = $config->getProperty("table_name");
	$sub_caption = $config->getProperty("caption");
	
	
?>
<input type="hidden" id="<?php echo $name;?>" name="<?php echo $name;?>" value="<?php echo $value;?>">
<div class="input" style="height:auto; min-height:32px" >
	<a class="margin-left" href="javascript:openPopup('<?php echo URL;?>admin/module/?module_id=<?php echo $sub_module_id;?>&params=<?php echo $appSession->getTool()->urlEncode("selFunc=selItem".$itemIndex);?>')"><i class="icon-plus"></i></a>
	<span id="sub_list<?php echo $itemIndex;?>"></span>
</div>
<script>
function loadSubModule<?php echo $itemIndex;?>(id)
{
	var _url = "<?php echo URL;?>admin/module/module/?ac=list&itemIndex=<?php echo $itemIndex;?>&table_name=<?php echo $sub_table_name;?>&caption=" + encodeURIComponent('<?php echo $sub_caption;?>') + "&id=" + id;
	loadPage('sub_list<?php echo $itemIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
		}
		
	}, false);
}
loadSubModule<?php echo $itemIndex;?>('<?php echo $value;?>');
function selItem<?php echo $itemIndex;?>(id)
{
	
	if(id == "")
	{
		return;
	}
	var ctr = document.getElementById('<?php echo $name;?>');
	if(ctr != null)
	{
		ctr.value = id;
	}
	closePopup();
	loadSubModule<?php echo $itemIndex;?>(id);
}
function removeItem<?php echo $itemIndex;?>()
{
	var ctr = document.getElementById('<?php echo $name;?>');
	if(ctr != null)
	{
		ctr.value = "";
	}
	loadSubModule<?php echo $itemIndex;?>('');
}


</script>
<?php
}else if($ac == "list")
{
	$itemIndex = "";
	if(isset($_REQUEST['itemIndex']))
	{
		$itemIndex = $_REQUEST['itemIndex'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	
	$caption = "";
	if(isset($_REQUEST['caption']))
	{
		$caption = $_REQUEST['caption'];
	}
	$columns = $table_name.".id";
	$s = $caption;
	
	while(true)
	{
		$index= $appSession->getTool()->indexOf($s, "{");
		if($index == -1)
		{
			break;
		}
		$s = $appSession->getTool()->substring($s, $index + 1);
		$index= $appSession->getTool()->indexOf($s, "}");
		if($index == -1)
		{
			break;
		}
		$c = $appSession->getTool()->substring($s, 0, $index);
		$s = $appSession->getTool()->substring($s, $index + 1);
		if($columns != "")
		{
			$columns = $columns.", ";
		}
		$columns = $columns."".$table_name.".".$c;
		
	}
	
		
	$sql = "SELECT ".$columns." FROM ".$table_name." WHERE ".$table_name.".id ='".$id."'";
	
	$s = "";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$_id = $dt->getString($i, "id");
		$columns = $caption;
		for($j =0; $j<$dt->getColumnCount(); $j++)
		{
			$columns = $appSession->getTool()->replace($columns , "{".$dt->getColumns()[$j]->getName()."}", $dt->getString($i, $dt->getColumns()[$j]->getName()));
		}
		$s = $s.$columns.'<a href="javascript:removeItem'.$itemIndex.'(\''.$_id.'\')"><i class="icon-minus"></i></a>';
	}
	echo $s;
}
?>
