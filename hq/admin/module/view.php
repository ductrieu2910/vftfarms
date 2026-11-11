<?php
$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$pageIndex = $appSession->getId();

$saved = "0";
$rel_id = "";
$prev_view_module_id = "";
$arr_params = explode('&', $params);
$paramList =[];
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$rel_id = $arrItem[1];
		}
		if($arrItem[0] == "view_module_id")
		{
			$prev_view_module_id = $arrItem[1];
		}
		if($arrItem[0] == "saved")
		{
			$saved = $arrItem[1];
		}
		
		$paramList[count($paramList)] = [$arrItem[0], $arrItem[1]];
	}
}


?>
<div class="content-header">
	<div class="row">
		<div class="col m12" id ="navTop">
		</div>
	</div>
	<div class="row padding-top-small">
		<div class="col m12">
		<a href="javascript:goto<?php echo $pageIndex;?>('<?php echo $id;?>')" class="button btn-primary"><?php echo $appSession->getLang()->find("Edit");?></a>
		</div>
	
	</div>
</div>
<div class="content-body" >
	<div class="content-body-backcolor content-body-forecolor border shadow--1dp">
	<?php
	if(count($layouts)>1)
	{
		if(count($layouts[1])>4)
		{
			$action_id = $layouts[1][4];
			$index = $appSession->getTool()->indexOf($action_id, ",");
			if($index !=-1)
			{
				$action_id = substr($action_id, $index + 1);
				$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d1.status =0 AND d2.group_id='".$action_id."'";
				if($root_module_id != "")
				{
					$sql = $sql. " AND (d2.module_id='".$module_id."' OR d2.module_id='".$root_module_id."')";
				}else
				{
					$sql = $sql. " AND d2.module_id='".$module_id."'";
				}


				$msg->add("query", $sql);

				$result = $appSession->getTier()->getArray($msg);
				$numrows = count($result);	
				$values = [];
				for($i =0; $i<$numrows; $i++)
				{
					$row = $result[$i];
					$arr = [];
					$arr[0] = $row[0];
					$arr[1] = $row[1];
					$values[$i] = $arr;
				}

				$action_id = findValue($values, $action_id);
				if($action_id != ""){
					$appSession->getTool()->httpGet(URL."api/action/?action_id=".$action_id."&data=".$appSession->getTool()->urlEncode($params));
				}
			}
		}
	}
	?>
	</div>
</div>
<script>
function goto<?php echo $pageIndex;?>(id)
	{
		
		gt('contentView', '<?php echo $module_id;?>', 'form', id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>');
	}
</script>