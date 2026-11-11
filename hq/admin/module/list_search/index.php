<?php

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}


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
	$pid = "";
	if(isset($_REQUEST['pid']))
	{
		$pid = $_REQUEST['pid'];
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
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$func = "";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	if($func == "")
	{
		$func = "selList";
	}
	
	
	$module_model_id = "";
	if(isset($_REQUEST['module_model_id']))
	{
		$module_model_id = $_REQUEST['module_model_id'];
	}
	
	$cview = "";
	if(isset($_REQUEST['cview']))
	{
		$cview = $_REQUEST['cview'];
	}

	$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0";

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


	$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";

	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$models = [];
	$columns_model_id = "";
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
					
	}
	$columns = [];
	$table_name = "";
	$data_id = "";
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
			$columns[count($columns)] = $arr;
		}

		if($models[$i][3] == "src" || $models[$i][2] == "data")
		{
			$data_id = findValue($values, $models[$i][0]);
			
		}
	}
	$column = "";
	$name = "";

	if($data_id == "")
	{
		echo "Data is not support";
	}else
	{
		$rel_id = "";
		if(isset($_SESSION[$module_id."rel_id"]))
		{
			$rel_id = $_SESSION[$module_id."rel_id"];
			
		}
		
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
		
		
		$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
						
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
		$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
		$sql = str_replace("{user_id}", "'".$appSession->getUserInfo()->getId()."'", $sql);
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
	
		$search = "";
		$search_columns = "";
		$sort_columns = "";
		$sort_type = " ASC ";
		$p = 0;
		$ps = 30;
		$rel_id = "";
		$arr_params = explode("&", $params);
		for($i =0; $i<count($arr_params); $i++)
		{
			$arr = explode("=", $arr_params[$i]);
			if(count($arr)>1)
			{
				if($arr[0] == "search")
				{
					$search = $arr[1];
					
				}else if($arr[0] == "search_columns")
				{
					$search_columns = $arr[1];
					
				}else if($arr[0] == "sort_columns")
				{
					$sort_columns = $arr[1];
				}else if($arr[0] == "sort_type")
				{
					$sort_type = $arr[1];
				}
				else if($arr[0] == "p" && $arr[1] != "")
				{
					$p= $arr[1];
				}else if($arr[0] == "ps" && $arr[1] != "")
				{
					$ps = $arr[1];
				}else if($arr[0] == "rel_id")
				{
					$rel_id = $arr[1];
					
				}
			}
		}
		
		
		$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);

		
		
		$searchs = [];
		if($search_columns != "")
		{
			$searchs[0] = str_replace("__", ".", $search_columns);
		}else
		{
			for($c =0; $c<count($columns); $c++)
			{
				$searchs[$c] = str_replace("__", ".", $columns[$c][1]);
			}
		}
		
		if(count($searchs)>0 && $search != "")
		{
			$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
		}
		if($parent_id != ""){
			if($parent_value != ""){
				$parent_value = str_replace("{rel_id}", "'".$rel_id."'", $parent_value);
				$parent_value = str_replace("{root_company_id}", "'".$appSession->getConfig()->getProperty("root_company_id")."'", $parent_value);
				$parent_value = str_replace("{company_id}", "'".$appSession->getConfig()->getProperty("company_id")."'", $parent_value);
			}
			
			$sql = $sql." AND ".$table_name.".".$parent_id."='".$parent_value."'";
		}
		
		if($group_by != "")
		{
			$sql= $sql. " GROUP BY ".$group_by;
		}
		
		$sort = "";
		
		if($sort_columns != "")
		{
			if($sort_type == "")
			{
				$sort_type = "ASC";
			}
			$sort = $sort_columns." ".$sort_type;
		}
		
	
		
		$arr = $appSession->getTier()->paging($sql, $p, $ps, $sort);
		
		$item_count = 0;
		$sql = $arr[1];
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		if($numrows>0)
		{
			$row = $result[0];
			$item_count = $row[0];
		}
		
		$page_count = (int)($item_count / $ps);
		if ($item_count - ($page_count * $ps) > 0)
		{
			$page_count = $page_count + 1;
		}
		
		$start = 0;
		if($item_count>0)
		{
			$start = ($p * $ps) + 1;
		}
		$end = $p + 1;
		if((($p + 1) * $ps)<$item_count)
		{
			$end = ($p + 1) * $ps;
		}else
		{
			$end = $item_count;
		}

		$sql = $arr[0];
		
		$msg->add("query", $sql);
		
		$result = $appSession->getTier()->getTable( $msg);
		$numrows = $result->getRowCount();
	
	?>
	
<div class="row">
	<div class="col m3">
		<input type="text" class="input" placeholder="Search..." id="editsearch_list" value="<?php echo $search;?>" onKeyDown="if(event.keyCode == 13){loadTableList1(0);}" />
	</div>
	<div class="col m3">
		
		<select class="select" id="editsearch_column_list">
			<option value=""><?php echo $appSession->getLang()->find("All");?></option>
			<?php
			for($c =0; $c<count($columns); $c++)
			{
				$caption = $columns[$c][0];
				$name = $columns[$c][1];
				if($name != "" && $appSession->getTool()->indexOf($name, "_date") == -1)
				{
				
			?>
			<option value="<?php echo $name;?>" <?php if($search_columns == $name) { echo " selected "; }?>><?php echo $caption;?></option>
			<?php
				}
			}
			?>
		</select>
	</div>
	<div class="col m3">
		
		<select class="select" id="editsort_column_list" onchange="loadTableList1(0)">
			<option value=""></option>
			<?php
			for($c =0; $c<count($columns); $c++)
			{
				$caption = $columns[$c][0];
				$name = $columns[$c][1];
				
				if($name != "")
				{
			?>
			<option value="<?php echo $name;?>" <?php if($sort_columns == $name) { echo " selected "; }?>><?php echo $appSession->getLang()->find($caption);?></option>
			<?php
				}
			}
			?>
		</select>
	</div>
	<div class="col m2">
		
		<select class="select" id="editsort_type_list" onchange="loadTableList1(0)">
			<option value="ASC" <?php if($sort_type == "ASC") { echo " selected "; }?>>A-Z</option>
			<option value="DESC" <?php if($sort_type == "DESC") { echo " selected "; }?>>Z-A</option>
		</select>
	</div>
	<div class="col m1">
		<button onclick="loadTableList1(0)" class="button btn-secondary"><?php echo $appSession->getLang()->find("Search");?></button>
	</div>
</div>
<br>	
<div class="row">
	<div class="col m12">
		<a href="javascript:<?php echo $func;?>('', '')"><img src="<?php echo URL;?>assets/img/remove.png"></a>
		<div class="table-responsive">
			<table class="table ">
				<thead>
				<tr class="text-left">
				<th class="pl-0" style="width: 20px">#
				</th>
				<th class="pl-0" style="width: 20px">
				</th>
				<?php
				for($c =0; $c<count($columns); $c++)
				{
					$catpion = $columns[$c][0];
					$name = $columns[$c][1];
					$width = $columns[$c][3];
					if($name != "")
					{
				?>
				<th style="<?php if($width != ""){?> width: <?php echo $width;?>px <?php }?>"><?php echo $catpion;?></th>
				<?php
					}
				}
				?>
				
			</tr>
			</thead>
			<tbody>
				<?php
				$no = ($p * $ps) + 1;
				for($j =0; $j<$numrows; $j++)
				{
					
					$row = $result->getRow($j);
					$id = $row->getString($table_name."__id");
					$value = "";
					for($c =0; $c<count($columns); $c++)
					{
						$name = $columns[$c][1];
						if($name != "")
						{
							$name = $row->getString($name);
							if($value != '')
							{
								$value = $value."/";
							}
							$value = $value.$name;
						}
					}
				?>
				<tr>
					<td class="pl-0" style="width:20px">
					<?php echo ($no + $j);?>.
					</td>
					<td class="pl-0" style="width:30px">
						<a href="javascript:<?php echo $func;?>('<?php echo $id;?>', '<?php echo htmlentities ($value);?>')"><img src="<?php echo URL;?>assets/img/add.png"></a>
					</td>
					<?php
					for($c =0; $c<count($columns); $c++)
					{
						
						$catpion = $columns[$c][0];
						$name = $columns[$c][1];
						$width = $columns[$c][2];
						$type = $columns[$c][3];
						$align = $columns[$c][4];
						$forecolor = $columns[$c][5];
						$backcolor = $columns[$c][6];
						if($name != "")
						{
							$name = $row->getString($name);
						
						
					?>
					<td  style="<?php if($align != ""){?> ;text-align: <?php echo $align;?> <?php } ?> <?php if($forecolor != ""){?> ;color: <?php echo $forecolor;?> <?php } ?><?php if($forecolor != ""){?> ;background-color: <?php echo $backcolor;?> <?php } ?>" >
					<?php 
				if($parent_id != "")
				{
				?>
				<a href="javascript:loadParent('<?php echo $id;?>')"><?php echo $name;?></a></th>
				<?php
				}else{
				?>
				<?php echo $name;?>
				
				<?php
				}
				?>
						
					</td>
					<?php
						}
					}
					?>
				</tr>
				<?php
				}
				?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<div class="col m2">
	<select id="editps_list" onchange="loadTableList1(0)" class="select" style="width: 75px;">
			<option value="10" <?php if($ps == 10){ echo " selected "; }?>>10</option>
			<option value="20" <?php if($ps == 20){ echo " selected "; }?>>20</option>
			<option value="30" <?php if($ps == 30){ echo " selected "; }?>>30</option>
			<option value="50" <?php if($ps == 50){ echo " selected "; }?>>50</option>
			<option value="100" <?php if($ps == 100){ echo " selected "; }?>>100</option>
		</select>
		
	</div>
	<div class="col m6">
		<?php
		if($p>5)
		{
		?>
		<a href="#" >
			<<
		</a>
		<?php
		}
		?>
		<?php
		if($p>0)
		{
		?>
		<a href="javascript:loadTableList1(<?php echo ($p-1);?>)" >
			<
		</a>
		<?php
		}
		?>
		<?php 			
			if($page_count == 0)
			{
				$page_count = 1;
			}
			$start = $p - 3;
			if($start<0)
			{
				$start = 0;
			}
			$end = $start;
			$len = 0;
			for($i =$start; $i<$page_count; $i++)
			{
				$end = $end + 1;
				$len = $len + 1;
				if($len>10)
				{
					break;
					
				}
			?>
			<a href="javascript:loadTableList1(<?php echo $i;?>)" class="<?php if($i == $p){ ?> btn-hover-primary active <?php } ?> "><?php echo ($i + 1);?></a>
			<?php 
			} 
			?>
		<?php
		if($end<$page_count)
		{
		?>
		<a href="javascript:loadTableList1(<?php echo $p + 1;?>)" >
			>
		</a>
		<?php
		}
		?>
		<?php
		if(($end + 1)<$page_count)
		{
		?>
		<a href="javascript:loadTableList1(<?php echo $page_count - 1;?>)">
			>>
		</a>
		<?php
		}
		?>
		
	</div>
	<div class="col m4" style="text-align:right">
		Displaying <?php echo (($p + 1) * $ps);?> of <?php echo ($page_count * $ps);?> records
	</div>
	
</div>

<script>
	function loadTableList1(p)
	{
		var param = "p=" + p;
		var ctr = document.getElementById('editsearch_list');
		param = param + '&search=' + ctr.value;
		ctr = document.getElementById('editsearch_column_list');
		param = param + '&search_columns=' + ctr.value;
		ctr = document.getElementById('editsort_column_list');
		param = param + '&sort_columns=' + ctr.value;
		ctr = document.getElementById('editsort_type_list');
		param = param + '&sort_type=' + ctr.value;
		ctr = document.getElementById('editps_list');
		param = param + '&ps=' + ctr.value;
		var _url = '<?php echo URL;?>admin/module/list_search/?ac=search&module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>&pid=<?php echo $pid;?>&params=' + encodeURIComponent(param);
		_url = _url + '&parent_id=<?php echo $parent_id;?>&parent_value=<?php echo $parent_value;?>&func=<?php echo $func;?>';
		loadPage('pnFullDialogContent', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
		
	}
	function loadParent(id)
	{
		var param = "p=0";
		var ctr = document.getElementById('editsearch_list');
		param = param + '&search=' + ctr.value;
		ctr = document.getElementById('editsearch_column_list');
		param = param + '&search_columns=' + ctr.value;
		ctr = document.getElementById('editsort_column_list');
		param = param + '&sort_columns=' + ctr.value;
		ctr = document.getElementById('editsort_type_list');
		param = param + '&sort_type=' + ctr.value;
		ctr = document.getElementById('editps_list');
		param = param + '&ps=' + ctr.value;
		var _url = '<?php echo URL;?>admin/module/list_search/?ac=search&module_id=<?php echo $module_id;?>&module_model_id=<?php echo $module_model_id;?>&pid=<?php echo $pid;?>&params=' + encodeURIComponent(param);
		_url = _url + '&parent_id=<?php echo $parent_id;?>&func=<?php echo $func;?>&parent_value='+ id;
		loadPage('pnFullDialogContent', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
		
	}
	function selList(id, val)
	{
		var ctr = document.getElementById('<?php echo $pid;?>_value');
		if(ctr != null)
		{
			ctr.value = val;
		}
		ctr = document.getElementById('<?php echo $pid;?>');
		if(ctr != null)
		{
			ctr.value = id;
		}
		closePopup();
	}
</script>
<?php
}
?>