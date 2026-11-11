<?php
$msg = $appSession->getTier()->createMessage();
$line_id = "";
if(isset($_REQUEST['line_id']))
{
	$line_id = $_REQUEST['line_id'];
}
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$pageIndex = "1";
if(isset($_REQUEST['pageIndex']))
{
	$pageIndex = $_REQUEST['pageIndex'];
}
if($ac == "view")
{

?>
<a href="javascript:addMeta<?php echo $pageIndex;?>();"><i class="icon-plus"></i></a>
<br>
<div id="pnMetaList<?php echo $pageIndex;?>" style="padding-top:5px"></div>
<script>
function loadMetaList<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/attribute_meta/?ac=list&line_id=<?php echo $line_id;?>&pageIndex=<?php echo $pageIndex;?>';
	loadPage('pnMetaList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
loadMetaList<?php echo $pageIndex;?>();
function addMeta<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/attribute_meta/?ac=add&line_id=<?php echo $line_id;?>&pageIndex=<?php echo $pageIndex;?>';
	loadPage('pnMetaList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadMetaList<?php echo $pageIndex;?>();
		}
		
	}, false);
}
function removeMeta<?php echo $pageIndex;?>(id)
{
	var _url = '<?php echo URL;?>addons/attribute_meta/?ac=del&line_id=<?php echo $line_id;?>&pageIndex=<?php echo $pageIndex;?>&id=' + id;
	
	loadPage('pnMetaList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadMetaList<?php echo $pageIndex;?>();
		}
		
	}, false);
}
</script>
<?php
}else if($ac == "list")
{
	$sql = "SELECT id, name FROM res_meta WHERE status =0 AND rel_id='".$line_id."' ORDER BY create_date ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	?>
	<table class="table">
		<?php
		for($i =0; $i<count($values); $i++)
		{
			$_id = $values[$i][0];
			$_name =$values[$i][1];
		?>
		<tr>
			<td><input type="text" class="input" value="<?php echo $_name;?>" onblur="saveLine('res_meta', 'name', '', '<?php echo $_id;?>', this, function(status, message){})"></td>
			<td width="30"><a href="javascript:removeMeta<?php echo $pageIndex;?>('<?php echo $_id;?>');"><i class="icon-minus"></i></a></td>
		</tr>
		<?php
		}
		?>
	</table>
	<?php
	
	
}else if($ac == "add")
{
	$builder = $appSession->getTier()->createBuilder("res_meta");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $line_id);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_meta SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}

?>