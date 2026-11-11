<?php

require_once(ABSPATH.'config.php' );

$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
$rel_id = "";
$arr_params = explode("&", $params);
for($i =0; $i<count($arr_params); $i++)
{
	$arr = explode("=", $arr_params[$i]);
	if(count($arr)>1)
	{
		if($arr[0] == "rel_id")
		{
			$rel_id = $arr[1];
			
		}
	}
}


$sql = "SELECT module_name, ac_name, ac_value FROM res_user_group_per WHERE status =0 AND group_id='".$rel_id."'";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);
$perListChecked = array();
for($i = 0; $i < $numrows; $i++) 
{
	$row = $result[$i];
	$arr = array();
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$perListChecked[$i] = $arr;
}

$sql = "SELECT module_id FROM ir_module_rel WHERE status =0";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$rels =[];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$module_id = $row[0];
	$rels[count($rels)] = $module_id;
	
}


$sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 ORDER BY sequence ASC";
$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$lines = [];
for($j =0; $j<$numrows; $j++)
{
	$row = $result[$j];
	
	if($row[1] == "")
	{
		$hasItem = false;
		for($n=0; $n<count($rels); $n++)
		{
			if($rels[$n] == $row[0])
			{
				$arr = [];
				$arr[0] = $row[0];
				$arr[1] = $row[1];
				$arr[2] = $row[2];
				$lines[count($lines)] = $arr;
				break;
			}
		}
	}else
	{
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$arr[2] = $row[2];
		$lines[count($lines)] = $arr;
	}
	
}

function per($perList, $module_name, $ac_name)
{
	
	for($i=0; $i<count($perList); $i++)
	{
		if($perList[$i][0] == $module_name && $perList[$i][1] == $ac_name)
		{
			if($perList[$i][2] == "1"){
				return true;
			}
			return false;
		}
	}
	return true;
}

function buildCategory($rel_id, $lines, $parent_id, $perListChecked)
	{
		
			$count = 0;
			$line = 0;
			for($j =0; $j<count($lines); $j++)
			{
				if($lines[$j][1] == $parent_id )
				{
					$count += 1;
				}
			}
			for($j =0; $j<count($lines); $j++)
			{
			
				
				if($lines[$j][1] == $parent_id )
				{
					$id = $lines[$j][0];
					$name = $lines[$j][2];
					$line += 1;
			?>
			<tr >
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				<td style="padding-left:5px; padding-top:5px"><?php echo $name;?> 
				View: <input type="checkbox" class="checkbox" onclick="updateGroupPer('<?php echo $rel_id;?>', this, '<?php echo $lines[$j][0];?>', 'view')" <?php if(per($perListChecked, $lines[$j][0], "view") == true){ echo " checked ";}?> >, 
				New: <input type="checkbox" class="checkbox" onclick="updateGroupPer('<?php echo $rel_id;?>', this, '<?php echo $lines[$j][0];?>', 'new')" <?php if(per($perListChecked, $lines[$j][0], "new") == true){ echo " checked ";}?> >
				Edit: <input type="checkbox" class="checkbox" onclick="updateGroupPer('<?php echo $rel_id;?>', this, '<?php echo $lines[$j][0];?>', 'edit')" <?php if(per($perListChecked, $lines[$j][0], "edit") == true){ echo " checked ";}?> >
				Delete: <input type="checkbox" class="checkbox" onclick="updateGroupPer('<?php echo $rel_id;?>', this, '<?php echo $lines[$j][0];?>', 'delete')" <?php if(per($perListChecked, $lines[$j][0], "delete") == true){ echo " checked ";}?> >
				</td>
			  </tr>
			  
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($rel_id, $lines, $id, $perListChecked);?></table></td>
			</tr>
			<?php
				
			}
		}
		
	}	

?>
<div class="row">
	<div class="col-sm-12">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php echo buildCategory($rel_id, $lines, "", $perListChecked);?>
		</table>
	</div>
	
</div>
<script>
function updateGroupPer(group_id, theInput, module_name, type)
{
	var _url = '<?php echo URL;?>admin/permission/?ac=updateGroupPer&group_id=' + group_id;
	var ac_value= "0";
	if(theInput.checked)
	{
		ac_value= "1";
	}
	_url = _url + '&module_name=' + encodeURIComponent(module_name);
	_url = _url + '&ac_name=' + encodeURIComponent(type);
	_url = _url + '&ac_value=' + encodeURIComponent(ac_value);
	loadPage('gotoTop', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") !=-1)
			{
				
			}
			else{
				alert(message);
			}
		}
		
	}, true);
}
</script>
<?php
}else if($ac == "updateGroupPer")
{
	
	
	$group_id = '';
	if(isset($_REQUEST['group_id']))
	{
		$group_id = $_REQUEST['group_id'];
	}
	$company_id = '';
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$module_name = '';
	if(isset($_REQUEST['module_name']))
	{
		$module_name = $_REQUEST['module_name'];
	}
	$module_name = '';
	if(isset($_REQUEST['module_name']))
	{
		$module_name = $_REQUEST['module_name'];
	}
	$ac_name = '';
	if(isset($_REQUEST['ac_name']))
	{
		$ac_name = $_REQUEST['ac_name'];
	}
	$ac_value = '';
	if(isset($_REQUEST['ac_value']))
	{
		$ac_value = $_REQUEST['ac_value'];
	}
	$sql = "SELECT id FROM res_user_group_per WHERE group_id='".$group_id."' AND module_name='".$module_name."' AND ac_name='".$ac_name."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	if($numrows>0)
	{
		$row = $result[0];
		$id = $row[0];
		$sql = "UPDATE res_user_group_per SET ac_value ='".$ac_value."'";
		$sql = $sql.", write_date=NOW()";
		$sql = $sql." WHERE id='".$id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		
	}else
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO res_user_group_per(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", group_id";
		$sql = $sql.", module_name";
		$sql = $sql.", ac_name";
		$sql = $sql.", ac_value";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$company_id."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".str_replace("'", "''", $group_id)."'";
		$sql = $sql.", '".str_replace("'", "''", $module_name)."'";
		$sql = $sql.", '".str_replace("'", "''", $ac_name)."'";
		$sql = $sql.", '".str_replace("'", "''", $ac_value)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}
	echo "OK";

}
?>
