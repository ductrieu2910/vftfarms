<?php

require_once(ABSPATH.'config.php' );

$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."'";


$result = pg_exec($db, $sql);
$numrows = pg_numrows($result);	
$values = [];
for($i =0; $i<$numrows; $i++)
{
	$row = pg_fetch_array($result, $i);
	$arr = [];
	$arr[0] = $row["module_model_id"];
	$arr[1] = $row["data"];
	$values[$i] = $arr;
}


$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' ORDER BY d1.create_date ASC";



$result = $appSession->getTier()->getArray($sql);
$numrows = pg_numrows($result);	
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

$data_id = "";
$table_name_rel = "";
$column_src = "";
$column_rel = "";

for($i =0; $i<count($models); $i++)
{
	if($models[$i][3] == "table_name")
	{
		$table_name_rel = findValue($values, $models[$i][0]);
		
	}
	else if($models[$i][3] == "src" || $models[$i][2] == "data")
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}if($models[$i][3] == "column_src")
	{
		$column_src = findValue($values, $models[$i][0]);
		
	}if($models[$i][3] == "column_rel")
	{
		$column_rel = findValue($values, $models[$i][0]);
		
	}
}
$column = "";
$name = "";
$action = "";
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

	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
	

	$condition = "";
	$group_by = "";
	$order_by = "";
	$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$data_id."' AND status =0";
	$result_data_sql = $appSession->getTier()->getArray($sql);
	$numrows_data_sql = count($result_data_sql);	
	if($numrows_data_sql>0)
	{
		$row1 = $result_data_sql[0];
		$condition = $row1[0];
		$group_by = $row1[1];
		$order_by = $row1[2];
	}
	$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$data_id."' ORDER BY create_date ASC";
	$result_data_sql = $appSession->getTier()->getArray($sql);
	$numrows_data_sql = count($result_data_sql);
	$sql_params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[0];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$sql_params[$m] = $arr;
	}
	
	
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
					
	$lines = [];
	$table_name = "";
	
	$result_data_sql = $appSession->getTier()->getArray($sql);
	$numrows_data_sql = count($result_data_sql);	
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$arr[3] = $row1[3];
		$arr[4] = $row1[3];
		$lines[$m] = $arr;
		if($table_name == "" && $row1[1] == "")
		{
			$table_name = $row1[2];
		}
	}
	$conditions = $appSession->getTier()->condition_array($condition);
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
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
	
	
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	
	
	
	$result = $appSession->getTier()->getArray($sql);
	$numrows = count($result);	

	$arr_params = explode("&", $params);
	for($i =0; $i<count($arr_params); $i++)
	{
		$arr = explode("=", $arr_params[$i]);
		if(count($arr)>1)
		{
			if($arr[0] == "name")
			{
				$name = $arr[1];
			}else if($arr[0] == "action")
			{
				$action = $arr[1];
			}
		}
	}
	if($table_name_rel == "")
	{
		echo "Table is not empty<br>";
	}
	if($column_src == "")
	{
		echo "Column src is not empty<br>";
	}
	if($column_rel == "")
	{
		echo "Column rel is not empty<br>";
	}
	
	$sql = "SELECT ".$column_rel." FROM ".$table_name_rel." WHERE ".$column_src."='".$id."' AND status =0";
	
	$result_selected = $appSession->getTier()->getArray($sql);
	$numrows_selected = count($result_selected);	
	$arr_selected = [];
	for($i =0; $i<$numrows_selected; $i++)
	{
		$row = $result_selected[$i];
		$arr_selected[count($arr_selected)] = $row[0];
	}
	?>
	
	<select class="form-control" id="<?php echo $name;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this)" <?php } ?> multiple="multiple" onchange="multipleRel(this, '<?php echo $table_name_rel;?>', '<?php echo $column_src;?>', '<?php echo $column_rel;?>', '<?php echo $id;?>')" >
		<option value=""></option>
		<?php
		for($i =0; $i<$numrows; $i++)
		{
			$row = pg_fetch_array($result, $i);
			$_id = $row[$table_name."__id"];
			$_name = $row[1];
			$selected = "";
			for($j=0; $j<count($arr_selected); $j++)
			{
				if($arr_selected[$j] == $_id)
				{
					$selected = " selected ";
					break;
				}
			}
		?>
		<option value="<?php echo $_id;?>" <?php echo $selected;?> ><?php echo $_name;?></option>
		<?php
		}
		?>
	</select>
	<script>
		 $('#<?php echo $name;?>').select2({
            placeholder: '',
			allowClear: true,
        });
	</script>
	<?php
}

?>