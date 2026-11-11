<?php
$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$pageIndex = $appSession->getId();

$module_caption = "";

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

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
$pvalue = "";
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
	if($models[$i][3] == "parent_value")
	{
		$pvalue = findValue($values, $models[$i][0]);
		
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
$func = "";
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
		if($arr[0] == "func")
		{
			$func = $arr[1];
			
		}
		if($arr[0] == "parent_id")
		{
			$pvalue = $arr[1];
			
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
	if($pvalue != "")
	{
		$index = $appSession->getTool()->indexOf($sql, "WHERE");
		if($index != -1)
		{
			$sql = $appSession->getTool()->substring($sql, 0, $index);
			$sql = $sql." WHERE ".$table_name.".status=0 AND ".$table_name.".".$pid."='".$pvalue."'";
		}
	}
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
	
	$sql = "SELECT ".$pid.", COUNT(id) AS c FROM ".$table_name." WHERE status =0";
	$ids = "";
	$parent_value = $pvalue;
	for($i = 0; $i<$numrows; $i++)
	{
		$id = $result->getString($i, $table_name."__id");
		if($id == $pvalue)
		{
			$pvalue = $result->getString($i, $table_name."__".$pid);
			
		}
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." parent_id='".$id."'";
	}
	if($ids != "")
	{
		$sql = $sql." AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY ".$pid;

	$msg->add("query", $sql);
	$childList = $appSession->getTier()->getArray($msg);
	

	$sql = "SELECT d1.id, d1.name, d1.type, d1.icon FROM ir_module_line d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.type LIKE 'table%' ORDER BY d1.sequence ASC, d1.name ASC";
	
	
	$msg->add("query", $sql);
	$views = $appSession->getTier()->getArray($msg);
	
	$hasItem = false;
	for($j =0; $j<$result->getRowCount(); $j++)
	{
		if($result->getString($j, $table_name."__".$pid) == $pvalue )
		{
			$hasItem = true;
		}
	}
	if($hasItem == false)
	{
		if($result->getRowCount()>0)
		{
			$pvalue = $result->getString(0, $table_name."__".$pid);
		}
	}
	for($j =0; $j<$result->getRowCount(); $j++)
	{
		if($result->getString($j, $table_name."__id") == $pvalue )
		{
			$pvalue = $result->getString($j, $table_name."__id");
		}
	}
	function buildCategory($appSession, $pageIndex, $dt, $columns, $table_name, $pid, $parent_id, $rel_id, $func, $childList)
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
				$pvalue = $dt->getString($j, $table_name."__".$pid);
				$id = $dt->getString($j, $table_name."__id");
				if($id == "")
				{
					continue;
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
				
				$count = 0;
				for($k =0; $k<count($childList); $k++)
				{
					if($childList[$k][0] == $id)
					{
						$count = $appSession->getTool()->toInt($childList[$k][1]);
					}
				}
			?>
			<tr >
				
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				
				<td style="padding-left:5px; padding-top:5px"><a href="javascript:goto<?php echo $pageIndex;?>('<?php echo $id;?>', '<?php echo $pvalue;?>')"><?php echo $s;?></a> <?php if($count>0){?> (<a href="javascript:childs<?php echo $pageIndex;?>('<?php echo $id;?>', '<?php echo $pvalue;?>')"><?php echo $count;?></a>) <?php }?><a href="javascript:add<?php echo $pageIndex;?>('<?php echo $id;?>')">&nbsp;<i class="icon-plus"></i>&nbsp;</a> &nbsp;&nbsp; <a href="javascript:del<?php echo $pageIndex;?>('<?php echo $id;?>')">&nbsp;<i class="icon-minus"></i>&nbsp;</a> &nbsp;&nbsp; <a href="javascript:move<?php echo $pageIndex;?>('<?php echo $id;?>')">&nbsp;<i class="icon-arrow-right-circle"></i>&nbsp;</a> <?php if($func != ""){?> &nbsp;&nbsp; <a href="javascript:<?php echo $func;?>('<?php echo $id;?>', '<?php echo $pvalue;?>')"><i class="icon-check"></i>&nbsp;</a> <?php } ?>
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $pageIndex, $dt, $columns,$table_name, $pid, $id, $rel_id, $func, $childList);
				?></table></td>
			</tr>
			<?php
				
			}
		}
		
	}
	
	
	?>
	<div class="content-header">
		<div class="row ">
			<div class="col m6" id ="navTop">
				&nbsp;
			</div>
			<div class="col m5" style="text-align:right">
				<div class="pagination">&nbsp;
					<?php 
					if(count($views)>1)
					{
						for($v=0; $v<count($views); $v++)
						{
							$icon = $views[$v][3];
							if($icon != "")
							{
								$icon = '<i class="'.$icon.'"></i>';
							}
							
						?>
						<a  class ="<?php if($views[$v][0]==$view_module_id){ echo " active ";}?>" href="javascript:clearNav(navList.length -1);gt('<?php echo $cview;?>', '<?php echo $module_id;?>', '<?php echo $views[$v][0];?>', '', '<?php echo $params;?>', '<?php echo $root_module_id;?>')"><?php if($icon != ""){ echo $icon; }else{ echo $views[$v][1];}?></a>
						<?php
						}
					}
					?>
					</div>
			</div>
			<div class="col m1" style="text-align:right">
				<div class="dropdown-hover">
				  <span><i class="icon-options-vertical"></i></span>
				  <div class="dropdown-content" style="right:0; text-align:left">
					<a href="<?php echo URL;?>admin/module/import/index.php?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>" class="navi-link" target="_blank"><i class="icon-arrow-right-circle"></i> <?php echo $appSession->getLang()->find("Import");?>
					</a>
					<a href="<?php echo URL;?>admin/module/export.php?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>&root_module_id=<?php echo $root_module_id?>" class="navi-link" target="_blank">
					<i class="icon-arrow-left-circle"></i> <?php echo $appSession->getLang()->find("Export");?>
					</a>
					
					<a href="#" class="navi-link">
						<i class="icon-question"></i> <?php echo $appSession->getLang()->find("Help");?>
					</a>
				  </div>
				</div>
			</div>
		</div>

	</div>
	<div class="content-body">
		<div class="content-body-backcolor content-body-forecolor border shadow--1dp">
				
				<div class="row  padding-small">
					<div class="col m12" >
						<a href="javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '', 'rel_id=<?php echo $rel_id;?>&parent_id=<?php echo $pvalue;?>', '')"><i class="icon-plus"></i></a>
						<?php
							for($c =0; $c<count($columns); $c++)
							{
								
								$catpion = $columns[$c][0];
								if($c>0){
									echo "/";
								}
								echo $catpion;
							}
							?>
						<table >
							
							<?php buildCategory($appSession, $pageIndex, $result, $columns, $table_name, $pid, $pvalue, $rel_id, $func, $childList);?>
						</table>
					</div>
				</div>
		</div>
	</div>
	

		
<script>
	
	function goto<?php echo $pageIndex;?>(id, pvalue)
	{
		
		gt('contentView', '<?php echo $module_id;?>', 'form', id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>&parent_id=<?php echo $parent_value;?>', '<?php echo $root_module_id;?>');
	}
	function childs<?php echo $pageIndex;?>(id, pvalue)
	{
		gt('contentView', '<?php echo $module_id;?>', 'table', '', 'rel_id=<?php echo $rel_id;?>&view_module_id=<?php echo $view_module_id;?>&parent_id='+ id, '<?php echo $root_module_id;?>');
	}
	function add<?php echo $pageIndex;?>(id)
	{
		
		gt('contentView', '<?php echo $module_id;?>', 'form', '', 'rel_id=' + id + '&<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>');
	}
	
	function checkAll<?php echo $pageIndex;?>(theInput)
	{
		var checkboxes = document.getElementsByName(theInput.name);
		for (var i= 0; i<checkboxes.length; i++) {
			if(checkboxes[i].type == 'checkbox')
			{
				checkboxes[i].checked = theInput.checked;
			}
		}
	}
	function delAll<?php echo $pageIndex;?>()
	{
		var count = 0;
		var ids = "";
		var checkboxes = document.getElementsByName("[]");
		for (var i= 0; i<checkboxes.length; i++) {
			if(checkboxes[i].type == "checkbox" && checkboxes[i].checked && checkboxes[i].value != "")
			{
				if(ids != "")
				{
					ids += ",";
				}
				ids += checkboxes[i].value;
				count = count + 1;
			}
		}
		if(ids == "")
		{
			alert("<?php echo $appSession->getLang()->find('Please check to delete');?>");
			return;
		}
		var result = confirm(count + ". Want to delete?");
		if (!result) {
			return;
		}
		delLine(ids);
	}
	function del<?php echo $pageIndex;?>(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		delLine<?php echo $pageIndex;?>(id);
	}
	function delLine<?php echo $pageIndex;?>(id)
	{
		
		var _url = '<?php echo URL;?>admin/module/action/?ac=delLine&table_name=<?php echo $table_name;?>';
		_url = _url + '&id=' + encodeURIComponent(id);
		
		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					
					menuItem('<?php echo $module_id;?>', '');
				
				}
				else{
					alert(message);
				}
			}
			
		}, true);
	}
	var selectedId;
	function move<?php echo $pageIndex;?>(id)
	{
		selectedId = id;
		var _url = "<?php echo URL;?>admin/module/treetable_select/?ac=view&module_id=<?php echo $module_id;?>&root_module_id=<?php echo $root_module_id;?>&module_model_id=<?php echo $module_model_id;?>&func=selectLine&params=" + encodeURIComponent('<?php echo $params;?>');
		
		openPopup(_url);
	}
	function selectLine(id)
	{
		closePopup();
		if(id != selectedId)
		{
			var _url = "<?php echo URL;?>admin/module/treetable_select/?ac=move&id="+ selectedId;
			_url = _url + "&parent_id=" + id + "&table_name=<?php echo $table_name;?>";
			loadPage('gotoTop', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						
						menuItem('<?php echo $module_id;?>', '');
					}
					else{
						alert(message);
					}
				}
				
			}, true);
		}
	}
	addNav('<?php echo $module_caption;?>', 'contentView', '<?php echo $module_id;?>', '<?php echo $view_module_id;?>', '', '<?php echo $params;?>', '<?php echo $root_module_id;?>');
	printNav("navTop");
	

</script>
<?php
}

?>