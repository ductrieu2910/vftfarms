<?php
require_once(ABSPATH.'api/User.php' );
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


$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "gettable")
{
	$sql = "";
	if(isset($_REQUEST['q']))
	{
		$sql = $_REQUEST['q'];
	}
	
	if($sql != "")
	{
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		echo $appSession->getTool()->respTable($dt);
	}
	
}else if($ac == "exec")
{
	$sql = "";
	if(isset($_REQUEST['q']))
	{
		$sql = $_REQUEST['q'];
	}
	if($sql != "")
	{
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		echo "1";
	}
	
}else if($ac == "getValue")
{
	$sql = "";
	if(isset($_REQUEST['q']))
	{
		$sql = $_REQUEST['q'];
	}
	if($sql != "")
	{
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		echo $appSession->getTier()->getValue($msg);
	}
}else if($ac == "module")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$root_company_id = "";
	if(isset($_REQUEST['root_company_id']))
	{
		$root_company_id = $_REQUEST['root_company_id'];
	}
	$db_id = "";
	if(isset($_REQUEST['db_id']))
	{
		$db_id = $_REQUEST['db_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$root_module_id = "";
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
	 
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $root_company_id);
	$msg->add("company_id", $company_id);
	$msg->add("user_id", $user_id);
	$msg->add("db_id", $db_id);
	
	$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name, lg.description FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$lang_id."' AND lg.rel_id = d4.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0";
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
			$arr[10] = $models[$i][0];
			$columns[count($columns)] = ["caption"=>$caption, "name" => $name, "type" => $type, "width" => $width, "align" => $align, "forecolor" => $forecolor, "backcolor" => $backcolor, "total" => $total, "model_id" => $models[$i][0]];
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
	if($data_id != "")
	{
		$sql = "SELECT name FROM ir_data WHERE id='".$data_id."'";
		
		$msg->add("query", $sql);
		$table_name = $appSession->getTier()->getValue($msg);
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

	
	

	
	$sql = "SELECT d2.id, d2.name, d2.description, d3.code AS type_code FROM ir_module_rel d1 LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN ir_action_type d3 ON(d2.type_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d1.type='action_view'";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id ='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql." AND d1.module_id='".$module_id."'";
	}
	$sql = $sql." ORDER BY d2.sequence ASC, d2.name ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$actionList =[];
	for($i =0; $i<count($values); $i++)
	{
		$actionList[count($actionList)] = ["id" => $values[$i][0], "name" => $values[$i][1], "description" => $values[$i][2], "type_code" => $values[$i][3]];
	}
	$arr =[];
	$arr[count($arr)] = [["data_id"=> $data_id, "view_type" => $view_type, "start_name" => $start_name, "end_name" => $end_name, "card_columns" => $card_columns, "table_caption" => $table_caption, "table_parent_id" => $table_parent_id, "table_name" => $table_name, "action_ids" => $action_ids ]];
	$arr[count($arr)] = [$actionList];
	$arr[count($arr)] = [$columns];
	echo json_encode($arr);
	
}else if($ac == "module_data")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$root_company_id = "";
	if(isset($_REQUEST['root_company_id']))
	{
		$root_company_id = $_REQUEST['root_company_id'];
	}
	$parent_company_id = "";
	if(isset($_REQUEST['parent_company_id']))
	{
		$parent_company_id = $_REQUEST['parent_company_id'];
	}
	$db_id = "";
	if(isset($_REQUEST['db_id']))
	{
		$db_id = $_REQUEST['db_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$company_ids = "";
	if(isset($_REQUEST['company_ids']))
	{
		$company_ids = $_REQUEST['company_ids'];
	}
	$child_company = "";
	if(isset($_REQUEST['child_company']))
	{
		$child_company = $_REQUEST['child_company'];
	}
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $root_company_id);
	$msg->add("company_id", $company_id);
	$msg->add("user_id", $user_id);
	$msg->add("db_id", $db_id);
	
	$data_id = "";
	if(isset($_REQUEST['data_id']))
	{
		$data_id = $_REQUEST['data_id'];
	}
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$root_module_id = "";
	if(isset($_REQUEST['root_module_id']))
	{
		$root_module_id = $_REQUEST['root_module_id'];
	}
	
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	$cols = "";
	if(isset($_REQUEST['columns']))
	{
		$cols = $_REQUEST['columns'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$table_name = "";
	$column = "";
	$name = "";
	$p = 0;
	$ps = 20;
	$search = "";
	$sort = "";
	$rel_id = "";
	$filters = "";
	$columns = explode("&", $cols);
	if($cols == "")
	{
		$columns = [];
	}
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
				
			}
		}
	}
	$filters_list = $appSession->getTool()->split($filters, ',');
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
	$sql = "SELECT name, type, data FROM parameter WHERE status =0";
    $sql = $sql." AND (rel_id='".$data_id."' OR rel_id='".$module_id."')";
	$sql = $sql." ORDER BY create_date ASC";
	
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
		if($appSession->getTool()->indexOf($filters, "assigned") != -1)
		{
			$arr = [];
			
			$conditions = explode(',', $filters);
			
			for($i =0; $i<count($conditions); $i++)
			{
				if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") == -1)
				{
					
					if($condition != "")
					{
						$condition = $condition.",";
					}
					$condition = $condition.$conditions[$i];
				}
			}
			$condition = $appSession->getTool()->replace($condition, ":", "=");
			
			
		}else
		{
			if($condition != "")
			{
				$condition = $condition.",";
			}
			$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
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
	
	if($id != "")
	{
		$sql = $sql." AND ".$table_name.".id='".$id."'";
	}
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	$sql = str_replace("{user_id}", "'".$user_id."'", $sql);
	
	$sql = str_replace("{company}", "'".$company_id."'", $sql);
	$sql = str_replace("{root_company}", "'".$root_company_id."'", $sql);

	$c_company_id = "";
	if($child_company != "")
	{
		$arr = explode(',', $child_company);
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
	}
	
	$sql = str_replace("{child_company}", $c_company_id, $sql);
	
	
	$c_company_id = "";
	if($parent_company_id != "")
	{
		$arr = explode(',', $parent_company_id);
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
	}
	
	$sql = str_replace("{parent_company}", $c_company_id, $sql);

	$c_company_id = "";
	if($company_ids == "")
	{
		$company_ids = $company_id;
	}
	if($company_ids != "")
	{
		$arr = $arr = explode(',', $company_ids);
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
	}
	
	$sql = str_replace("{companies}", $c_company_id, $sql);
	
	if($sort == ""&& count($lines)>0)
	{
		$sort = $lines[0][5];
	}
	$searchs = [];
	for($c =0; $c<count($columns); $c++)
	{
		if(count($columns[$c])<=2)
		{
			continue;
		}
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
	
	$item_count = 0;
	$page_count = 0;
	$start = 0;
	$end = 0;
	if($sql == "")
	{
	
		exit();
	}

	
	$arr = $appSession->getTier()->paging($sql, $p, $ps, $sort);
	if(count($arr) <=1)
	{
		exit();
	}
	
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

	
	$result = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($result);
	
}else if($ac == "module_data_all")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$root_company_id = "";
	if(isset($_REQUEST['root_company_id']))
	{
		$root_company_id = $_REQUEST['root_company_id'];
	}
	$parent_company_id = "";
	if(isset($_REQUEST['parent_company_id']))
	{
		$parent_company_id = $_REQUEST['parent_company_id'];
	}
	$db_id = "";
	if(isset($_REQUEST['db_id']))
	{
		$db_id = $_REQUEST['db_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$company_ids = "";
	if(isset($_REQUEST['company_ids']))
	{
		$company_ids = $_REQUEST['company_ids'];
	}
	$child_company = "";
	if(isset($_REQUEST['child_company']))
	{
		$child_company = $_REQUEST['child_company'];
	}
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $root_company_id);
	$msg->add("company_id", $company_id);
	$msg->add("user_id", $user_id);
	$msg->add("db_id", $db_id);
	
	$data_id = "";
	if(isset($_REQUEST['data_id']))
	{
		$data_id = $_REQUEST['data_id'];
	}
	
	
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$root_module_id = "";
	if(isset($_REQUEST['root_module_id']))
	{
		$root_module_id = $_REQUEST['root_module_id'];
	}
	
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	$cols = "";
	if(isset($_REQUEST['columns']))
	{
		$cols = $_REQUEST['columns'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$table_name = "";
	$column = "";
	$name = "";
	$p = 0;
	$ps = 20;
	$search = "";
	$sort = "";
	$rel_id = "";
	$filters = "";
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	
	
	$columns = explode("&", $cols);
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
				
			}
		}
	}
	$sql = "SELECT resource FROM res_resource WHERE status =0";
    $sql = $sql." AND rel_id='".$data_id."'";
	
	$msg->add("query", $sql);
	$sql = $appSession->getTier()->getValue($msg);
	if($sql != "")
	{
		$sql = $appSession->getTool()->replace($sql, "{rel_id}", $rel_id);
		$sql = $appSession->getTool()->replace($sql, "{company_id}", $company_id);
		$sql = $appSession->getTool()->replace($sql, "{user_id}", $user_id);
		$sql = $appSession->getTool()->replace($sql, "{id}", $id);
		
		$msg->add("query", $sql);
		
		$result = $appSession->getTier()->getTable($msg);
		echo $appSession->getTool()->respTable($result);
		exit();
		
	}
	
	$filters_list = $appSession->getTool()->split($filters, ',');
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
	$sql = "SELECT name, type, data FROM parameter WHERE status =0";
    $sql = $sql." AND (rel_id='".$data_id."' OR rel_id='".$module_id."')";
	$sql = $sql." ORDER BY create_date ASC";
	
	$sql = "SELECT resource FROM res_resource WHERE status =2";
    $sql = $sql." AND rel_id='".$data_id."' ";
	
	
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
		if($appSession->getTool()->indexOf($filters, "assigned") != -1)
		{
			$arr = [];
			
			$conditions = explode(',', $filters);
			
			for($i =0; $i<count($conditions); $i++)
			{
				if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") == -1)
				{
					
					if($condition != "")
					{
						$condition = $condition.",";
					}
					$condition = $condition.$conditions[$i];
				}
			}
			$condition = $appSession->getTool()->replace($condition, ":", "=");
			
			
		}else
		{
			if($condition != "")
			{
				$condition = $condition.",";
			}
			$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
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
	
	if($id != "")
	{
		$sql = $sql." AND ".$table_name.".id='".$id."'";
	}
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	$sql = str_replace("{user_id}", "'".$user_id."'", $sql);
	
	$sql = str_replace("{company}", "'".$company_id."'", $sql);
	$sql = str_replace("{root_company}", "'".$root_company_id."'", $sql);

	$c_company_id = "";
	if($child_company != "")
	{
		$arr = explode(',', $child_company);
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
	}
	
	$sql = str_replace("{child_company}", $c_company_id, $sql);
	
	
	$c_company_id = "";
	if($parent_company_id != "")
	{
		$arr = explode(',', $parent_company_id);
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
	}
	
	$sql = str_replace("{parent_company}", $c_company_id, $sql);

	$c_company_id = "";
	if($company_ids == "")
	{
		$company_ids = $company_id;
	}
	if($company_ids != "")
	{
		$arr = $arr = explode(',', $company_ids);
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
	}
	
	$sql = str_replace("{companies}", $c_company_id, $sql);
	
	if($sort == ""&& count($lines)>0)
	{
		$sort = $lines[0][5];
	}
	$searchs = [];
	
	
	$search = trim($search);
	if(count($searchs)>0 && $search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
	}
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	
	$msg->add("query", $sql);
	
	
	$result = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($result);
	
}

?>