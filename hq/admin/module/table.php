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

$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name, lg.description FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d4.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0";
if($root_module_id != "")
{
	$sql = $sql. " AND (d2.module_id='".$module_id."' OR d2.module_id='".$root_module_id."')";
}else
{
	$sql = $sql. " AND d2.module_id='".$module_id."'";
}

$msg->add("query", $sql);

$values = $appSession->getTier()->getArray($msg);
if(count($values)>0)
{
	$row =$values[0];
	$module_caption = $row[2];
	if($row[3] != "")
	{
		$module_caption = $row[3];
	}
}

$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename, d1.group_id FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.status =0";
if($root_module_id != "")
{
	$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
}else
{
	$sql = $sql." AND d1.module_id='".$module_id."'";
}

$sql = $sql." ORDER BY d1.sequence ASC, d1.create_date ASC";


$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
$columns = [];
$table_name = "";
$data_id = "";
$action_ids = "";
$hasStatus = 0;
$view_type = "table";
$start_name = "start_date";
$end_name = "end_date";
$card_columns = 3;
$table_caption = "";
$table_parent_id = "";
$actions = "";

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
	}else if($row[3] == "actions")
	{
		$actions = findValue($values,  $row[0]);
	}
	
				
}


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
		$default = "";
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
				}else if($models[$j][3] == 'default')
				{
					$default = $value;
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
		$arr[10] = $models[$i][0];
		$arr[11] = $default;
		$columns[count($columns)] = $arr;
	}
	if(($models[$i][3] == "src" || $models[$i][2] == "data") && $models[$i][4] == $module_model_id)
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}
	if(($models[$i][3] == "view_type") && $models[$i][4] == $module_model_id)
	{
		$view_type = findValue($values, $models[$i][0]);
		
	}
	if(($models[$i][3] == "start_date") && $models[$i][4] == $module_model_id)
	{
		$start_name = findValue($values, $models[$i][0]);
		
	}
	if(($models[$i][3] == "end_date") && $models[$i][4] == $module_model_id)
	{
		$end_name = findValue($values, $models[$i][0]);
		
	}if(($models[$i][3] == "card_columns") && $models[$i][4] == $module_model_id)
	{
		$sValue = findValue($values, $models[$i][0]);
		if($sValue != "")
		{
			$card_columns = $appSession->getTool()->toInt($sValue);
		}
		
	}
	if(($models[$i][3] == "table_caption") && $models[$i][4] == $module_model_id)
	{
		$table_caption = findValue($values, $models[$i][0]);
		
	}
	if(($models[$i][3] == "parent_id") && $models[$i][4] == $module_model_id)
	{
		$table_parent_id = findValue($values, $models[$i][0]);
		
	}
	
	if(($models[$i][3] == "action" || $models[$i][2] == "action") && $models[$i][4] == $module_model_id)
	{
		$action_ids = findValue($values, $models[$i][0]);
		
		
	}
}
if($card_columns>12)
{
	$card_columns = 12;
}


$column = "";
$name = "";
$p = 0;
$ps = 20;
$search = "";
$sort = "";
$rel_id = "";
$filters = "";
$selFunc = "";
$arr_params = explode("&", $params);
for($i =0; $i<count($arr_params); $i++)
{
	$arr = explode("=", $arr_params[$i]);
	if(count($arr)>1)
	{
		if($arr[0] == "search")
		{
			$search = $arr[1];
			
		}else if($arr[0] == "sort")
		{
			$sort = $arr[1];
		}
		else if($arr[0] == "p")
		{
			$p = $arr[1];
		}else if($arr[0] == "ps")
		{
			$ps = $arr[1];
		}else if($arr[0] == "rel_id")
		{
			$rel_id = $arr[1];
			
		}else if($arr[0] == "filters")
		{
			$filters = $arr[1];
			
		}else if($arr[0] == "selFunc")
		{
			$selFunc = $arr[1];
			
		}
	}
}


	
	$sql = "SELECT d1.id, d1.name, d1.type, d1.icon FROM ir_module_line d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.type LIKE 'table%' ORDER BY d1.sequence ASC, d1.create_date ASC";
	$msg->add("query", $sql);
	$views = $appSession->getTier()->getArray($msg);

	$sql = "SELECT d1.id, d1.name, d1.rel_id, d1.icon, d2.icon, lg.description, d1.type, d2.type FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND d1.publish=1 AND d1.grouped = 1 AND (d1.type='parent' OR d2.type='parent' OR d1.type='report' OR  d2.type='report')";
	if($root_module_id != "")
	{
		$sql = $sql." AND (d1.parent_id='".$module_id."' OR d1.parent_id='".$root_module_id."')";
	}else{
		$sql = $sql." AND d1.parent_id='".$module_id."'";
	}
	
	$sql = $sql." ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$result_grouped = $appSession->getTier()->getArray($msg);

	
	$appSession->getLang()->load($appSession->getTier(), "table", $appSession->getConfig()->getProperty("lang_id"));
	$filters_list = $appSession->getTool()->split($filters, ',');
	
	$sql = "SELECT d2.id, d2.name, d2.description, d3.code FROM ir_module_rel d1 LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN ir_action_type d3 ON(d2.type_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d1.type='action_view'";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id ='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql." AND d1.module_id='".$module_id."'";
	}

	$sql = $sql." ORDER BY d2.sequence ASC, d2.name ASC";


	$msg->add("query", $sql);
	

	$actionList = $appSession->getTier()->getArray($msg);
	
	?>
	<div class="content-header">
		<div class="row ">
			<div class="col m6" id ="navTop">
				
			</div>
			<div class="col m3" style="text-align:right">
				&nbsp;
				<?php
					for($i=0; $i<count($filters_list); $i++)
					{
						$line =$filters_list[$i];
						$index = $appSession->getTool()->indexOf($line, "c:");
						if($index != -1)
						{
							$line = $appSession->getTool()->substring($line, $index + 2);
							?>
							
							<a class="padding" href="javascript:removeFilter<?php echo $pageIndex;?>(<?php echo $i;?>)"><?php echo $line;?> <i class="icon-minus"></i></a>
							<?php
							
						}
					}
					?>
				
			</div>
			<div class="col m3">
				
				<div class="input-group">
					
					<input type="text" class="input" placeholder="Search..." id="editsearch" value="<?php echo $search;?>" onKeyDown="if(event.keyCode == 13){loadTable<?php echo $pageIndex;?>(0,'<?php echo $sort;?>', '<?php echo $filters;?>');}" />
					<button onclick="loadTable<?php echo $pageIndex;?>(0,'<?php echo $sort;?>', '<?php echo $filters;?>')" class="button btn-secondary"><?php echo $appSession->getLang()->find("Search");?></button>
				</div>
				
			</div>
		</div>
		
		<div class="row padding-top-small">
			<div class="col m6">
				<?php 
				if($view_type== "document")
				{
				?>
					<input id="files<?php echo $pageIndex;?>" type="file" multiple  style="display:none" onchange="readFile<?php echo $pageIndex;?>('', this);">
					
					<a href="javascript:document.getElementById('files<?php echo $pageIndex;?>').click();" class="button btn-primary"><i class="icon-cloud-upload"></i> <?php echo $appSession->getLang()->find("New");?></a><span id="progress_upload<?php echo $pageIndex;?>"></span>
					
				<?php
				}
				else
				{
				?>
				<button onclick="gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '', '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>')" class="button btn-primary"><?php echo $appSession->getLang()->find("New");?></button>
				<?php
				}
				if($action_ids!= "")
				{
					
					$actions = $appSession->getTool()->split($action_ids, ';');
					for($a = 0; $a<count($actions); $a++)
					{
						
						$resources = $appSession->getTool()->split($actions[$a], ',');
						$caption = "";
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
								}else if($key == "caption")
								{
									$caption = $value;
								}
							}
						}
						$cmd_params = $appSession->getTool()->replace($cmd_params, "{module_id}", $module_id);
						if($cmd == "popup")
						{
							if($cmd_params == ""){
								$cmd_params = "rel_id=".$rel_id."&module_id=".$module_id."&pageIndex=".$pageIndex;
							}else{
								$cmd_params = "&rel_id=".$rel_id."&module_id=".$module_id."&pageIndex=".$pageIndex;
							}
							$cmd = "openPopup('".URL.$appSession->getConfig()->getProperty("addons")."/".$cmd_value."/?".$cmd_params."')";
						}
					?>
					<a href="javascript:<?php echo $cmd;?>" class="button btn-default"><?php echo $appSession->getLang()->find($caption);?></a>
					<?php
					}
				}
				?>
				
				
			</div>
			<div class="col m6" style="text-align:right">
			<table width="100%">
				<tr>
					<td>
					<div class="pagination">
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
						<a class ="<?php if($views[$v][0]==$view_module_id){ echo " active ";}?>" href="javascript:clearNav(navList.length -1);gt('<?php echo $cview;?>', '<?php echo $module_id;?>', '<?php echo $views[$v][0];?>', '', '<?php echo $params;?>', '<?php echo $root_module_id;?>')"><?php if($icon !=""){ echo $icon; }else{ echo $views[$v][1]; } ?></a>
						<?php
						}
					}
					?>
					</div>
					</td>
					<td style="text-align:right"><div class="dropdown-hover">
				  <span><i class="icon-options-vertical"></i></span>
				  <div class="dropdown-content" style="right:0; text-align:left">
				  <?php
				  if($selFunc != "")
				  {
					 ?>
					 <a href="javascript:selAll<?php echo $pageIndex;?>()">
					<i class="icon-plus"></i> <?php echo $appSession->getLang()->find("Select");?>
					</a>
					 <?php
				  }
				  ?>
				    <?php if(canPer($perList, "delete")){ ?>
					<a href="javascript:delAll<?php echo $pageIndex;?>()">
					<i class="icon-trash"></i> <?php echo $appSession->getLang()->find("Delete");?>
					</a>
					<?php
					}
					?>
					<?php
					for($a = 0; $a<count($actionList); $a++)
					{
					?>
					<a href="javascript:irAction('<?php echo $actionList[$a][0]; ?>', '<?php echo $actionList[$a][3]; ?>', 'rel_id=<?php echo $rel_id;?>&module_id=<?php echo $module_id;?>',  on_action<?php echo $pageIndex;?>)"><i class="icon-arrow-right"></i> <?php echo $actionList[$a][1]; ?></a>
					<?php
					}
					?>
					<a href="javascript:openPopup('<?php echo URL;?>admin/module/filters/?module_id=<?php echo $module_id;?>&root_module_id=<?php echo $root_module_id;?>&module_model_id=<?php echo $module_model_id;?>&filters=<?php echo $appSession->getTool()->urlEncode($filters);?>&pageIndex=<?php echo $pageIndex;?>')" class="navi-link">
					 <i class="icon-crop"></i>  <?php echo $appSession->getLang()->find("Filters");?>
					</a>
					<a href="javascript:openPopup('<?php echo URL;?>admin/module/sort/?module_id=<?php echo $module_id;?>&root_module_id=<?php echo $root_module_id;?>&module_model_id=<?php echo $module_model_id;?>&sort=<?php echo $appSession->getTool()->urlEncode($sort);?>&pageIndex=<?php echo $pageIndex;?>')" class="navi-link">
					 <i class="icon-equalizer"></i>  <?php echo $appSession->getLang()->find("Sort");?>
					</a>
					<a href="<?php echo URL;?>admin/module/import/index.php?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>" class="navi-link" target="_blank"><i class="icon-arrow-right-circle"></i> <?php echo $appSession->getLang()->find("Import");?>
					</a>
					<a href="<?php echo URL;?>admin/module/export/?module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>&root_module_id=<?php echo $root_module_id?>&rel_id=<?php echo $rel_id?>" class="navi-link" target="_blank">
					<i class="icon-arrow-left-circle"></i> <?php echo $appSession->getLang()->find("Export");?>
					</a>
					<a href="javascript:openPopup('<?php echo URL;?>admin/module/lang_module/?rel_id=<?php echo $module_id;?>')" class="navi-link">
					<i class="icon-arrow-left-circle"></i> <?php echo $appSession->getLang()->find("Language");?>
					</a>
					
	
					
					<?php
					for($g =0; $g<count($result_grouped); $g++)
					{
					
					  $row_group = $result_grouped[$g];
					  if($row_group[6] == "parent" && $row_group[6] == "parent")
					  {
						  $group_id = $row_group[0];
						  $group_name = $row_group[5];
						  if($group_name == "")
						  {
							  $group_name = $row_group[1];
						  }
						  $rel_id1 = $row_group[2];
						  $icon = $row_group[3];
						  if($icon == "")
						  {
							  $icon = $row_group[4];
						  }
						  
						  if($icon != "")
						  {
							  $icon = '<i class="'.$icon.'"></i> ';
						  }
						  if($rel_id1 != "")
						  {
							  $group_id = $rel_id1;
							  $rel_id1 = $row_group[0];
						  }
					  ?>
					  <a class="navi-link" href="javascript:gt('contentView', '<?php echo $group_id;?>', '', '', '', '<?php echo $rel_id1;?>');"><?php echo $icon;?><?php echo $group_name;?></a>
					  
					  <?php
						}
					  }
					  ?>
					<?php
					for($g =0; $g<count($result_grouped); $g++)
					{
					
					  $row_group = $result_grouped[$g];
					  if($row_group[6] == "report" || $row_group[6] == "report")
					  {
						  $group_id = $row_group[0];
						  $group_rel_id = $row_group[2];
						  $group_name = $row_group[5];
						  if($group_name == "")
						  {
							  $group_name = $row_group[1];
						  }
						  $rel_id1 = $row_group[2];
						  $icon = $row_group[3];
						  if($icon == "")
						  {
							  $icon = $row_group[4];
						  }
						  
						  if($icon != "")
						  {
							  $icon = '<i class="'.$icon.'"></i> ';
						  }
						  if($rel_id1 != "")
						  {
							  $group_id = $rel_id1;
							  $rel_id1 = $row_group[0];
						  }
					  ?>
					  <a class="navi-link" href="javascript:openPopup('<?php echo URL;?>admin/module/filters/?module_id=<?php echo $group_id;?>&root_module_id=<?php echo $group_rel_id;?>&module_model_id=&filters=<?php echo $appSession->getTool()->urlEncode($filters);?>&pageIndex=<?php echo $pageIndex;?>&type=report')"><?php echo $icon;?><?php echo $group_name;?></a>
					  
					  <?php
						}
					  }
					  ?>
					<a href="#" class="navi-link">
						<i class="icon-question"></i> <?php echo $appSession->getLang()->find("Help");?>
					</a>
				  </div>
				</div></td>
				</tr>
			</table>
				
				
			</div>
		</div>
	</div>
	<div class="content-body">
		<div class="content-body-backcolor content-body-forecolor border shadow--1dp">
			<?php
			function findLayout($layouts, $parent_id, $name)
			{
				
				for($i =0; $i<count($layouts); $i++)
				{
					if($layouts[$i][1]== $parent_id)
					{
						$_id = $layouts[$i][0];
						$type = $layouts[$i][2];
						$value = $layouts[$i][3];
						$data = $layouts[$i][4];
						if($data != "")
						{
							$pos = strpos($data, ',');
							if ($pos !== false)
							{
								$model_name = substr($data, 0, $pos);
								if($model_name == $name)
								{
									return [substr($data, $pos + 1), $_id];
								}
							}
						}
						$found_id = findLayout($layouts, $_id, $name);
						if(count($found_id )>0)
						{
							return $found_id;
						}
					}
				}
				return [];
			}
			
			if($view_type == "collapsible")
			{
				include( ABSPATH .'admin/module/table_collapsible.php' );
			}else if($view_type == "card")
			{
				include( ABSPATH .'admin/module/table_card.php' );
			}else if($view_type == "grantt")
			{
				include( ABSPATH .'admin/module/table_grantt.php' );
			}else if($view_type == "calendar")
			{
				include( ABSPATH .'admin/module/table_calendar.php' );
			}else{
				include( ABSPATH .'admin/module/table_table.php' );
			}
			?>
		</div>
	</div>
	
	
		
<script>
	function loadTable<?php echo $pageIndex;?>(p, sort, filters)
	{
		clearNav(navList.length -1);
		var param = "p=" + p;
		var ctr = document.getElementById('editsearch');
		if(ctr != null)
		{
			param = param + '&search=' + ctr.value;
		}
		
		param = param + '&sort=' + sort;
		param = param + '&filters=' + filters;
		param = param + '&selFunc=<?php echo $selFunc;?>';
		
		ctr = document.getElementById('editps');
		if(ctr != null)
		{
			param = param + '&ps=' + ctr.value;
		}
		
		param = param + '&rel_id=<?php echo $rel_id;?>';
		
		gt('<?php echo $cview;?>', '<?php echo $module_id;?>', '<?php echo $view_module_id;?>', '<?php echo $id;?>', param, '<?php echo $root_module_id;?>');
		
	}
	function loadSort<?php echo $pageIndex;?>(sort)
	{
		loadTable<?php echo $pageIndex;?>(0, sort, '<?php echo $filters;?>');
	}
	function loadFilter<?php echo $pageIndex;?>(filters)
	{
		loadTable<?php echo $pageIndex;?>(0, '<?php echo $sort;?>', filters);
	}
	function relLoadTable<?php echo $pageIndex;?>()
	{
		loadTable<?php echo $pageIndex;?>(0, '<?php echo $sort;?>', '<?php echo $filters;?>');
	}
	function goto<?php echo $pageIndex;?>(id)
	{
		
		gt('contentView', '<?php echo $module_id;?>', 'form', id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>');
	}
	function view<?php echo $pageIndex;?>(id)
	{
		
		gt('contentView', '<?php echo $module_id;?>', 'view', id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>&id=' +id , '<?php echo $root_module_id;?>');
	}
	
	function removeFilter<?php echo $pageIndex;?>(index)
	{
		var arrFilters = new Array();
		<?php
		for($i=0; $i<count($filters_list); $i++)
		{
			$line =$filters_list[$i];
		?>
		arrFilters.push('<?php echo $filters_list[$i];?>');
		<?php
		}
		?>
		var filters = "";
		for(var i =0; i<arrFilters.length; i++)
		{
			if(i == index)
			{
				continue;
			}
			if(filters != "")
			{
				filters = filters + ",";
			}
			filters = filters + arrFilters[i];
		}
		loadTable<?php echo $pageIndex;?>(0, '<?php echo $sort;?>', filters);
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
					
					loadTable<?php echo $pageIndex;?>(<?php echo $p;?>, '<?php echo $sort;?>', '<?php echo $filters;?>');
				}
				else{
					alert(message);
				}
			}
			
		}, true);
	}
	addNav('<?php echo $module_caption;?>', 'contentView', '<?php echo $module_id;?>', '<?php echo $model_name;?>', '', '<?php echo $params;?>', '<?php echo $root_module_id;?>');
	<?php if($selFunc == ""){
		?>
	printNav("navTop");
	<?php
	}
	?>
	
	
	var reader<?php echo $pageIndex; ?>;
	var files<?php echo $pageIndex; ?>;
	var names<?php echo $pageIndex; ?> = new Array();
	var file_index<?php echo $pageIndex; ?> = 0;
	
	function readFile<?php echo $pageIndex; ?>(parent_id, theFiles) 
	{
		
		files<?php echo $pageIndex; ?> = theFiles.files;
		if (!files<?php echo $pageIndex; ?>.length) {
		  alert('Please select a file!');
		  return;
		}
		names<?php echo $pageIndex; ?> = new Array();
		for(var i =0; i<files<?php echo $pageIndex; ?>.length; i++)
		{
			var name = files<?php echo $pageIndex; ?>[i].name;
			var index = name.lastIndexOf(".");
			if(index != -1)
			{
				name = name.substr(0, index);
			}
			names<?php echo $pageIndex; ?>.push(name);
			
		}
		uploadDoc<?php echo $pageIndex; ?>();
		
		
	}
	function uploadDoc<?php echo $pageIndex; ?>()
	{
		
		
		file_index<?php echo $pageIndex; ?> = 0;
		upByFile<?php echo $pageIndex; ?>();
	}

	function postDataPage<?php echo $pageIndex; ?>(_url, params, complete)
	{
		  
		var xmlHttp = GetXmlHttpObject();
		if (xmlHttp == null) {
				alert("Browser does not support HTTP Request");
				return;
		}
		xmlHttp.open("POST", _url, true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=UTF-8");
		xmlHttp.onreadystatechange = function() 
		{
			if ((xmlHttp.readyState == 4) || (xmlHttp.readyState == "complete")) 
			{
				var responseMsg =  xmlHttp.responseText;
				if (xmlHttp.status == 200) 
				{
					complete(0, responseMsg);	
				}
			}
		};
		
		xmlHttp.send(params);
		
	}

	function upByFile<?php echo $pageIndex; ?>()
	{
		var file = files<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>];
		file.name = "";
	
		var _url = '<?php echo URL;?>document/?ac=createFile';

		loadPage('pnFileManager<?php echo $pageIndex; ?>', _url, function(status, message)
		{
			if(status  == 0)
			{
				if(message.length == 36)
				{
					var file_id = message;
					reader<?php echo $pageIndex; ?> = new FileReader();
					
					var byteRead = 1024 * 100;
					
					
					var start = 0;
					var stop = byteRead -1;
					var sizes = file.size;
					
					if(sizes<=byteRead)
					{
						stop = sizes - 1;
					}
					
					var baseUrl = '<?php echo URL;?>document/?ac=writeFile&file_id=' + file_id ;
					var proccess = document.getElementById('progress_upload<?php echo $pageIndex; ?>');
					reader<?php echo $pageIndex; ?>.onloadend = function(evt) {
					
					  if (evt.target.readyState == FileReader.DONE) { 
						
		
						var _url = baseUrl;
						if(start == 0)
						{
							_url = _url + '&n=1' 
						}
						
						var base64String = btoa(String.fromCharCode.apply(null, new Uint8Array(evt.target.result)));
					
						base64String = 'sData=' + encodeURIComponent(base64String);
						
						if(evt.target.result.byteLength>0)
						{
							postDataPage<?php echo $pageIndex; ?>(_url, base64String, function(status, message)
							{
								if(status  == 0)
								{
									if(message == "OK")
									{
										var p = (start/sizes) * 100;
										p = parseInt(p);
										proccess.innerHTML  = p + "%";
										stop = stop + 1;
										start = stop ;
										if((stop + 1)<sizes)
										{
							
											if((stop + byteRead)>=sizes)
											{
												byteRead = (sizes - stop);
											}
											stop = stop + byteRead;
											stop = stop -1;
											readBytes<?php echo $pageIndex; ?>(file, start, stop);
										}else
										{
											var extension = files<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>].name;
											var index = extension.lastIndexOf(".");
											if(index != -1)
											{
												extension = extension.substr(index);
											}else{
												extension = "";
											}
											_url = '<?php echo URL;?>document/?ac=commitDocument&file_id=' + file_id ;
											_url = _url + '&name=' + encodeURIComponent(names<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>] + extension);
											_url = _url + '&rel_id=<?php echo $rel_id; ?>';
											_url = _url + '&extension=' + extension;
											
											loadPage('pnFileManager<?php echo $pageIndex; ?>', _url, function(status, message)
											{
												if(status  == 0)
												{
													if(message == "OK")
													{
														file_index<?php echo $pageIndex; ?> += 1;
														if(files<?php echo $pageIndex; ?>.length>file_index<?php echo $pageIndex; ?>)
														{
															
										
															upByFile<?php echo $pageIndex; ?>();
														}
														else{
														
															proccess.setAttribute("class", "");
															proccess.innerHTML = "";
															loadTable<?php echo $pageIndex;?>(<?php echo $p;?>, '<?php echo $sort;?>', '<?php echo $filters;?>')
														}
														
													}else
													{
														alert(message);
													}
												}
											}, true);
											
										}
									}else
									{
										alert(message);
									}
								}
								
							});
						}
						
					  }
					};
					
					readBytes<?php echo $pageIndex; ?>(file, start, stop);
					
				}else
				{
					alert(message);
				}
			}
		}, true);
	}
	function readBytes<?php echo $pageIndex; ?>(file, start, stop)
	{
	 
		var blob = file.slice(start, stop + 1);
		reader<?php echo $pageIndex; ?>.readAsArrayBuffer(blob);
	}

	function viewAssigned(id)
	{
		openPopup('<?php echo URL;?>admin/module/assigned/?ac=assign_view&assign_id=' + id);
	}
	function addStatus(status_id, assign_id)
	{
		var ctr = document.getElementById('editdescription_assign_status');
		var description = '';
		if(ctr != null)
		{
			description = ctr.value;
		}
		var _url = "<?php echo URL;?>admin/module/assigned/?ac=add_status&status_id=" + status_id + "&assign_id=" + assign_id;
		_url = _url + '&description=' + encodeURIComponent(description);
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					closePopup();
					loadTable<?php echo $pageIndex;?>(<?php echo $p;?>, '<?php echo $sort?>', '<?php echo $filters;?>');
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function on_action<?php echo $pageIndex;?>(type, status, message){
		
		if(type == "load")
		{
			
			loadTable<?php echo $pageIndex;?>(<?php echo $p;?>, '<?php echo $sort?>', '<?php echo $filters;?>');
		}else
		{
			alert(message);
		}
	}

	
</script>
