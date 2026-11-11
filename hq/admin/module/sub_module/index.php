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
	$sub_parent_id = $config->getProperty("parent_id");
	$sub_rel_id = $config->getProperty("rel_id");
	$sub_parent_table_name = $config->getProperty("parent_table_name");
	$sub_caption = $config->getProperty("caption");
	
?>
<div class="input" style="height:auto; min-height:32px" >
	<a class="margin-left" href="javascript:openPopup('<?php echo URL;?>admin/module/?module_id=<?php echo $sub_module_id;?>&params=<?php echo $appSession->getTool()->urlEncode("selFunc=selItem".$itemIndex);?>')"><i class="icon-plus"></i></a>
	<span id="sub_list<?php echo $itemIndex;?>"></span>
</div>
<script>
function loadSubModule<?php echo $itemIndex;?>()
{
	var _url = "<?php echo URL;?>admin/module/sub_module/?ac=list&itemIndex=<?php echo $itemIndex;?>&id=<?php echo $id;?>&parent_id=<?php echo $sub_parent_id;?>&rel_id=<?php echo $sub_rel_id;?>&table_name=<?php echo $sub_table_name;?>&parent_table_name=<?php echo $sub_parent_table_name;?>&caption=" + encodeURIComponent('<?php echo $sub_caption;?>');
	;
	loadPage('sub_list<?php echo $itemIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
		}
		
	}, false);
}
loadSubModule<?php echo $itemIndex;?>();
function selItem<?php echo $itemIndex;?>(id)
{
	
	if(id == "")
	{
		return;
	}
	var _url = "<?php echo URL;?>admin/module/sub_module/?ac=add&table_name=<?php echo $sub_table_name;?>&parent_id=<?php echo $sub_parent_id;?>&parent_value=<?php echo $id;?>&rel_id=<?php echo $sub_rel_id;?>&id=" + encodeURIComponent(id);
	
	closePopup();
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadSubModule<?php echo $itemIndex;?>();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
	
	
	
}
function removeItem<?php echo $itemIndex;?>(id)
{
	var r = confirm("Are your sure to remove?");
	if (r == false) {
		return;
	} 
	var _url = "<?php echo URL;?>admin/module/sub_module/?ac=del&table_name=<?php echo $sub_table_name;?>&id=" + id;
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadSubModule<?php echo $itemIndex;?>();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
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
	$parent_table_name = "";
	if(isset($_REQUEST['parent_table_name']))
	{
		$parent_table_name = $_REQUEST['parent_table_name'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$parent_id = "";
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
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
		$columns = $columns."".$parent_table_name.".".$c;
		
	}
	
		
	$sql = "SELECT ".$columns." FROM ".$table_name." LEFT OUTER JOIN ".$parent_table_name." ON(".$table_name.".".$rel_id."=".$parent_table_name.".id) WHERE ".$table_name.".status =0 AND ".$table_name.".".$parent_id."='".$id."' ORDER BY ".$table_name.".create_date ASC";
	
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
		if($s != "")
		{
			$s = $s."&nbsp;&nbsp;";
		}
		
		$s = $s.$columns.'<a href="javascript:removeItem'.$itemIndex.'(\''.$_id.'\')"><i class="icon-minus"></i></a>';
	}
	echo $s;
	
	
}else if($ac == "add")
{
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$parent_id = "";
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$parent_value = "";
	if(isset($_REQUEST['parent_value']))
	{
		$parent_value = $_REQUEST['parent_value'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$arr = $appSession->getTool()->split($id, ',');
	for($i =0; $i<count($arr); $i++)
	{
		$sql = "INSERT INTO ".$table_name."(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_uid";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", ".$parent_id;
		$sql = $sql.", ".$rel_id;
		
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$parent_value."'";
		$sql = $sql.", '".$arr[$i]."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		
		$result = $appSession->getTier()->exec($msg);
	}
	echo "OK";
}else if($ac == "del")
{
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ".$table_name." SET status=1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>
