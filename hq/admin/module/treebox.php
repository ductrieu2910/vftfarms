<?php
$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$module_caption = "";

$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0 AND d2.status =0";

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());
$msg->add("query", $sql);



$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$values = [];
for($i =0; $i<$numrows; $i++)
{
	$row =$result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$values[$i] = $arr;
	$module_caption = $row[2];
}
if($module_id != $root_module_id && $root_module_id != "")
{
	$sql = "SELECT name FROM ir_module WHERE id='".$root_module_id."'";
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	$msg->add("query", $sql);
	echo $sql;
	$module_caption = $appSession->getTier()->getValue($msg);
}


$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";




$msg->add("query", $sql);


$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
$pid = "";
$pvalue = "";

for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$arr[3] = $row[3];
	$models[$i] = $arr;
	if($row[3] == "columns")
	{
		$columns_model_id = $row[0];
	}
	else if($row[3] == "parent_id")
	{
		$pid = findValue($values, $row[0]);
		
	}
	else if($row[3] == "parent_value")
	{
		$pvalue = findValue($values, $row[0]);
		
	}
				
}

$columns = [];
$table_name = "";
$data_id = "";
$action_ids = "";
$hasStatus = 0;
for($i =0; $i<count($models); $i++)
{
	if($models[$i][1] == $columns_model_id)
	{
		$caption = "";
		$name = "";
		$type = "";
		$align = "";
		$forecolor = "";
		$backcolor = "";
		$width = "";
		$format = "";
		$total = "";
		for($j =0; $j<count($models); $j++)
		{
			if($models[$j][1] == $models[$i][0])
			{
				$value = findValue($values, $models[$j][0]);
				if($models[$j][3] == 'caption')
				{
					$caption = $value;
				}
				else if($models[$j][3] == 'name')
				{
					$name = $value;
				}else if($models[$j][3] == 'type')
				{
					$type = $value;
					if($type == "status")
					{
						$hasStatus = 1;
					}
				}else if($models[$j][3] == 'align')
				{
					$align = $value;
				}else if($models[$j][3] == 'forecolor')
				{
					$forecolor = $value;
				}else if($models[$j][3] == 'backcolor')
				{
					$backcolor = $value;
				}else if($models[$j][3] == 'width')
				{
					$width = $value;
				}else if($models[$j][3] == 'format')
				{
					$format = $value;
				}else if($models[$j][3] == 'total')
				{
					$total = $value;
				}
			}
		}
		$arr = [];
		$arr[0] = $caption;
		$arr[1] = $name;
		$arr[2] = $type;
		
		$arr[3] = $width;
		$arr[4] = $align;
		$arr[5] = $forecolor;
		$arr[6] = $backcolor;
		$arr[7] = $format;
		$arr[8] = $total;
		$columns[count($columns)] = $arr;
	}
	if($models[$i][3] == "src" || $models[$i][2] == "data")
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}
	if($models[$i][3] == "action" || $models[$i][2] == "action")
	{
		$ac_id = findValue($values, $models[$i][0]);
		if($ac_id != "")
		{
			if($action_ids != "")
			{
				$action_ids = " OR ";
			}
			$action_ids = $action_ids." d2.id='".$ac_id."'";
		}
		
	}
}


$column = "";
$name = "";

if($data_id == "")
{
	echo "Data is not support";
}else
{
	
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
	

	$condition = "";
	$group_by = "";
	$order_by = "";
	$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$data_id."' AND status =0";
	$msg->add("query", $sql);

	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	if($numrows_data_sql>0)
	{
		$row1 = $result_data_sql[0];
		$condition = $row1[0];
		$group_by = $row1[1];
		$order_by = $row1[2];
	}
	$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$data_id."' ORDER BY create_date ASC";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);
	$sql_params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$sql_params[$m] = $arr;
		
	}
	if($module_id != $root_module_id && $root_module_id != "")
	{
		$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$root_module_id."' ORDER BY create_date ASC";
		
		$msg->add("query", $sql);
		$result_data_sql = $appSession->getTier()->getArray($msg);
		$numrows_data_sql = count($result_data_sql);
		for($m =0; $m<$numrows_data_sql; $m++)
		{
			$row1 = $result_data_sql[$m];	
			$hasItem = 0;
			for($n = 0; $n<count($sql_params); $n++)
			{
				if($sql_params[$n][0] == $row1[0])
				{
					$hasItem = 1;
					$sql_params[$n][0] = $row1[0];
					$sql_params[$n][1] = $row1[1];
					$sql_params[$n][2] = $row1[2];
				}
			}
			if($hasItem == 0)
			{
				$arr = [];
				$arr[0] = $row1[0];
				$arr[1] = $row1[1];
				$arr[2] = $row1[2];
				$sql_params[$m] = $arr;
			}
			
			
		}
	}
	
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.data, d1.relationship, d2.order_by FROM ir_data_line  d1 LEFT OUTER JOIN ir_data d2 ON(d1.data_id = d2.id) WHERE d1.data_id ='".$data_id."' AND d1.status =0";
	
	
					
	$lines = [];
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$arr[3] = $row1[3];
		$arr[4] = $row1[4];
		$arr[5] = $row1[5];
		$lines[$m] = $arr;
		
		if($table_name == "" && $row1[1] == "")
		{
			$table_name = $row1[2];
		}
		
	}
	
	$conditions = $appSession->getTier()->condition_array($condition);
	
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
	
	
	
	if($condition == "")
	{
		$sql = $sql." WHERE ".$table_name.".status=0";
	}else{
		$sql = $sql." AND ".$table_name.".status=0";
	}
	
	
	
	
	$arr_params = explode("&", $params);
	for($i =0; $i<count($arr_params); $i++)
	{
		$arr = explode("=", $arr_params[$i]);
		if(count($arr)>1)
		{
			if($arr[0] == "search")
			{
				$_SESSION[$module_id."_search"] = $arr[1];
				
			}else if($arr[0] == "search_columns")
			{
				$_SESSION[$module_id."search_columns"] = $arr[1];
				
			}else if($arr[0] == "sort_columns")
			{
				$_SESSION[$module_id."_sort_columns"] = $arr[1];
			}else if($arr[0] == "sort_type")
			{
				$_SESSION[$module_id."_sort_type"] = $arr[1];
			}
			else if($arr[0] == "p")
			{
				$_SESSION[$module_id."_p"] = $arr[1];
			}else if($arr[0] == "ps")
			{
				$_SESSION[$module_id."_ps"] = $arr[1];
			}else if($arr[0] == "rel_id")
			{
				$_SESSION[$module_id."rel_id"] = $arr[1];
				
			}
		}
	}
	$rel_id = "";
	if(isset($_SESSION[$module_id."rel_id"]))
	{
		$rel_id = $_SESSION[$module_id."rel_id"];
		
		
	}
	
	
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	$sql = str_replace("{company}", "'".$appSession->getUserInfo()->getCompanyId()."'", $sql);
	$sql = str_replace("{root_company}", "'".$appSession->getConfig()->getProperty("root_company_id")."'", $sql);
	$arr = explode(',', $appSession->getConfig()->getProperty("child_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{child_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("parent_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{parent_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("company_ids"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{companies}", $c_company_id, $sql);
	
	
	
	
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	$sort_columns = "";
	if(count($lines)>0)
	{
		$sort_columns = $lines[0][5];
	}
	$sort_type = "";
	$sort = "";
	if(isset($_SESSION[$module_id."_sort_columns"]) &&  $_SESSION[$module_id."_sort_columns"] != "")
	{
		$sort_columns = $_SESSION[$module_id."_sort_columns"];
		$sort_type = " ASC";
		if(isset($_SESSION[$module_id."_sort_type"]))
		{
			$sort_type = $_SESSION[$module_id."_sort_type"];
		}
		$sort = $sort_columns." ".$sort_type;
	}else{
		
		if($sort_columns != "")
		{
			$sort = $sort_columns;
		}
	}

	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	
	
	
	function buildCategory($dt, $columns, $cview, $module_id, $root_module_id, $table_name, $pid, $parent_id)
	{
		
			$count = 0;
			$line = 0;
			for($j =0; $j<$dt->getRowCount(); $j++)
			{
				if($dt->getString($j, $table_name."__".$pid) == $parent_id )
				{
					$count += 1;
				}
			}
			for($j =0; $j<$dt->getRowCount(); $j++)
			{
		
				
				if($dt->getString($j, $table_name."__".$pid) == $parent_id )
				{
					$id = $dt->getString($j, $table_name."__id");
					$s = "";
					$line += 1;
					$row = $dt->getRow($j);
					for($c =0; $c<count($columns); $c++)
					{
						
						$catpion = $columns[$c][0];
						$name = $columns[$c][1];
						
						$type = $columns[$c][2];
						if($name != "")
						{
							$name = $row->getString($name);
							if($type == "date" && $name != "")
							{
								
								if($format != "")
								{
									$name = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($name), $format);
								}else{
									$name = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($name));
								}
								
							}else if($type == "float" && $name != "")
							{
								$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name));
								if($align == "")
								{
									$align = "right";
								}
							}
							else if($type == "int" && $name != "")
							{
								if($align == "")
								{
									$align = "right";
								}
								$name = $appSession->getFormats()->getINT()->format($appSession->getTool()->toInt($name));
							}else if($type == "currency")
							{
								if($name == "")
								{
									$name = 0;
								}
								if($align == "")
								{
									$align = "right";
								}
								$currency_id = $appSession->getConfig()->getProperty("currency_id");
								for($c1 =0; $c1<count($columns); $c1++)
								{
									$name1 = $columns[$c1][1];
									
									if($name1 == "currency_id")
									{
										$currency_id = $row->getString($name1);
										break;
									}
								}
								$name = $appSession->getCurrency()->format($currency_id, $appSession->getTool()->toDouble($name));
							}else if($type == "percent" && $name != "")
							{
								$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name)) ."%";
								if($align == "")
								{
									$align = "right";
								}
							}
							if($s != "")
							{
								$s = $s."/";
							}
							$s = $s.$name;
						}
					}
			?>
			<tr >
				
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				
				<td style="padding-left:5px; padding-top:5px"><a href="javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '<?php echo $id;?>', '', '<?php echo $root_module_id;?>')"><?php echo $s;?></a> <a href="javascript:javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '', 'rel_id=<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/add.png" border="0" /></a> <a href="javascript:del('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png" border="0" /></a>
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($dt, $columns, $cview, $module_id, $root_module_id, $table_name, $pid, $id);?></table></td>
			</tr>
			<?php
				
			}
		}
		
	}
	
	
	
	?>

	<div class="row">
		<div class="col m4">
			<?php 
			if($action_ids == "")
			{
			?>
			<a href="javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '', 'rel_id=<?php echo $pvalue;?>', '')"><img src="<?php echo URL;?>assets/img/add.png"><?php echo $module_caption;?></a>
			<?php
			}else{
				$sql = "SELECT d2.name, d1.resource FROM res_resource d1 LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND (".$action_ids.")";
				$actions = $appSession->getTier()->getArray($sql);
				for($a = 0; $a<count($actions); $a++)
				{
					$resources = $appSession->getTool()->split($actions[$a][1], ',');
					$cmd = "";
					$cmd_value = "";
					$cmd_params = "";
					for($n =0; $n<count($resources); $n++)
					{
						$index = $appSession->getTool()->indexOf($resources[$n], '=');
						if($index != -1)
						{
							$key = $appSession->getTool()->substring($resources[$n], 0, $index);
							$value = $appSession->getTool()->substring($resources[$n], $index + 1);
							if($key == "cmd")
							{
								$cmd = $value;
							}else if($key == "value")
							{
								$cmd_value = $value;
							}else if($key == "params")
							{
								$cmd_params = $value;
							}
						}
					}
					$cmd_params = $appSession->getTool()->replace($cmd_params, "{module_id}", $module_id);
					if($cmd == "popup")
					{
						$cmd = "openPopup('".URL.$appSession->getConfig()->getProperty("addons")."/".$cmd_value."/index.php?".$cmd_params."')";
					}
				?>
				<a href="javascript:<?php echo $cmd;?>" class="button btn-default"><?php echo $appSession->getLang()->find($actions[$a][0]);?></a>
				<?php
				}
			}
			?>
			
		</div>
		<div class="col m8" style="text-align:right">
			
			<a href="javascript:delAll()">
				<?php echo $appSession->getLang()->find("Delete");?>
			</a>
			/
			<a href="<?php echo URL;?>addons/module/import.php?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>" class="navi-link" target="_blank">
								<?php echo $appSession->getLang()->find("Import");?>
			</a>
			/
			<a href="<?php echo URL;?>addons/module/export.php?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>&root_module_id=<?php echo $root_module_id?>" class="navi-link" target="_blank">
				<?php echo $appSession->getLang()->find("Export");?>
			</a>
			/
			<a href="javascript:showStatus()" class="navi-link">
				<?php echo $appSession->getLang()->find("Status");?>
			</a>
			/
			<a href="#" class="navi-link">
				<?php echo $appSession->getLang()->find("Help");?>
			</a>
			
		</div>
	</div>
	<div class="row">
		<div class="col m12">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php buildCategory($dt, $columns, $cview, $module_id, $root_module_id, $table_name, $pid, $pvalue);?>
			</table>
		</div>
	</div>
			
	
		
<script>
	function loadTable(p)
	{
		var param = "p=" + p;
		var ctr = document.getElementById('editsearch');
		param = param + '&search=' + ctr.value;
		ctr = document.getElementById('editsearch_column');
		param = param + '&search_columns=' + ctr.value;
		ctr = document.getElementById('editsort_column');
		param = param + '&sort_columns=' + ctr.value;
		ctr = document.getElementById('editsort_type');
		param = param + '&sort_type=' + ctr.value;
		ctr = document.getElementById('editps');
		param = param + '&ps=' + ctr.value;
		gt('<?php echo $cview;?>', '<?php echo $module_id;?>', '', '<?php echo $id;?>', param, '<?php echo $root_module_id;?>');
		
	}
	
	function del(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		delLine(id);
	}
	function delLine(id)
	{
		
		var _url = '<?php echo URL;?>admin/module/action.php?ac=delLine&table_name=<?php echo $table_name;?>';
		_url = _url + '&id=' + encodeURIComponent(id);
		
		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					
					loadTable(<?php echo $p;?>);
				}
				else{
					alert(message);
				}
			}
			
		}, true);
	}
	function showStatus()
	{
		openPopup('<?php echo URL;?>addons/module/status.php?company_id=<?php echo $appSession->getConfig()->getProperty("root_company_id");?>&module_id=<?php echo $module_id;?>');
	}
</script>
<?php
}

?>