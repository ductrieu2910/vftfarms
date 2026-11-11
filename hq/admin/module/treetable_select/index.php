<?php

$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
$module_id = '';
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
$root_module_id = '';
if(isset($_REQUEST['root_module_id']))
{
	$root_module_id = $_REQUEST['root_module_id'];
}
$module_model_id = "";
if(isset($_REQUEST['module_model_id']))
{
	$module_model_id = $_REQUEST['module_model_id'];
}
$params = "";
if(isset($_REQUEST['params']))
{
	$params = $_REQUEST['params'];
}
$func = "";
if(isset($_REQUEST['func']))
{
	$func = $_REQUEST['func'];
}

$pageIndex = "0";

$module_caption = "";

function findValue($values, $id)
{
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][0] == $id)
		{
			return $values[$i][1];
		}
	}
	return "";
}

$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$module_id."' AND d1.status =0";

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
	$arr[2] = $row[2];
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
	
	$module_caption = $appSession->getTier()->getValue($msg);
}


$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename, d1.group_id FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.status =0 ORDER BY d1.create_date ASC";


$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
$pid = "parent_id";
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$arr[3] = $row[3];
	$arr[4] = $row[4];
	$models[$i] = $arr;
	if($row[3] == "columns" && $row[4]== $module_model_id)
	{
		$columns_model_id = $row[0];
	}
	
	
				
}
$columns = [];
$table_name = "";
$data_id = "";
$action_ids = "";

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
		$arr[9] = $name;
		$columns[count($columns)] = $arr;
	}
	if(($models[$i][3] == "src" || $models[$i][2] == "data") && $models[$i][4] == $module_model_id)
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}
	if($models[$i][3] == "parent_id")
	{
		$pid = findValue($values, $models[$i][0]);
		
	}
	if(($models[$i][3] == "action" || $models[$i][2] == "action") && $models[$i][4] == $module_model_id)
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
$p = 0;
$ps = 20;
$search = "";
$sort = "";
$rel_id = "";
$filters = "";
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

if($data_id == "")
{
	echo "Data is not support";
}else
{
	
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
	if($module_id != $root_module_id)
	{
		$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$root_module_id."' ORDER BY create_date ASC";
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
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
	if($filters != "")
	{
		if($condition != "")
		{
			$condition = $condition.",";
		}
		$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
	}
	$conditions = $appSession->getTier()->condition_array($condition);

	
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
	
	
	
	if($condition == "")
	{
		$sql = $sql." WHERE ".$table_name.".status=0";
	}else{
		$sql = $sql." AND ".$table_name.".status=0";
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
	
	if($sort == ""&& count($lines)>0)
	{
		$sort = $lines[0][5];
	}
	$searchs = [];
	for($c =0; $c<count($columns); $c++)
	{
		if($columns[$c][1] != ""  && $columns[$c][2] == "")
		{
			$searchs[count($searchs)] = str_replace("__", ".", $columns[$c][1]);
		}
	}
	
	$search = trim($search);
	if(count($searchs)>0 && $search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
	}
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	
	
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	
	if($sort != "")
	{
		$sql = $sql." ORDER BY ".$sort;
	}
	
	
	$msg->add("query", $sql);

	
	$result = $appSession->getTier()->getTable($msg);
	$numrows = $result->getRowCount();	
	$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));

	$sql = "SELECT d1.id, d1.name, d1.type, d1.icon FROM ir_module_line d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.type LIKE 'table%' ORDER BY d1.sequence ASC, d1.name ASC";
	
	
	$msg->add("query", $sql);
	$views = $appSession->getTier()->getArray($msg);
	
	
	function buildCategory($pageIndex, $dt, $columns, $table_name, $pid, $parent_id, $rel_id, $func)
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
					if($id == "")
					{
						exit;
					}
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
				
				<td style="padding-left:5px; padding-top:5px"><a href="javascript:<?php echo $func;?>('<?php echo $id;?>')"><?php echo $s;?></a> 
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($pageIndex, $dt, $columns,$table_name, $pid, $id, $rel_id,$func);?></table></td>
			</tr>
			<?php
				
			}
		}
		
	}
	?>
	
				
<div class="row  padding-small">
	<div class="col m12" >
		<table >
			<?php buildCategory($pageIndex, $result, $columns, $table_name, $pid, "", $rel_id, $func);?>
		</table>
	</div>
</div>
	
<?php
}
}else if($ac == "move")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$parent_id = "";
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	if($parent_id == $id)
	{
		echo "OK";
		exit();
	}
	$table_name = "";
	if(isset($_REQUEST['table_name']))
	{
		$table_name = $_REQUEST['table_name'];
	}
	$sql = "UPDATE ".$table_name." SET parent_id='".$parent_id."', write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->exec($msg);
	echo "OK";
	
}

?>