<?php


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



$dt = $appSession->getTier()->getTable($msg);


function buildData($dt, $columns, $table_name, $pid, $start_name, $end_name, $rel_id, $parent_id)
{
	$s = "";
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, $table_name."__".$pid) == $parent_id)
		{
			$row = $dt->getRow($i);
			$id = $row->getString($table_name."__id");
			if($id == "")
			{
				exit;
			}
			if($s != "")
			{
				$s = $s.",";
			}
			$start_date = strtotime($row->getString($table_name."__".$start_name));
			$end_date = strtotime($row->getString($table_name."__".$end_name));
			$diff = abs(strtotime($end_date) - strtotime($start_date));

			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			
			$title = "";
			
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
					if($title != "")
					{
						$title = $title."/";
					}
					$title = $title.$name;
				}
			}
			$p = 0;
			
			$s = $s.'{ id: "'.$id.'", text: "'.$title.'", start_date: "'.date("d-m-Y", $start_date).'", duration: '.$days.', progress: '.$p.', open: true, parent: "'.$parent_id.'" }';
			$s1 =  buildData($dt, $columns, $table_name, $pid, $start_name, $end_name, $rel_id, $id);
			if($s1 != "")
			{
				if($s != "")
				{
					$s = $s.",";
				}
				$s = $s.$s1;
			}
		}
	}
	return $s;
}
function buildLink($i, $appSession, $dt, $table_name, $pid, $parent_id)
{
	$s = "";
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, $table_name."__".$pid) == $parent_id)
		{
			$id = $dt->getString($i, $table_name."__id");
			if($id == "")
			{
				exit;
			}
			if($parent_id != "")
			{
				if($s != "")
				{
					$s = $s.",";
				}
				$s = $s.'{id: "'.($i+1).'", source: "'.$parent_id.'", target: "'.$id.'", type: "1"}';
			}
			$s1 =  buildLink($i, $appSession, $dt, $table_name, $pid, $id);
			if($s1 != "")
			{
				if($s != "")
				{
					$s = $s.",";
				}
				$s = $s.$s1;
			}
			
			
		}
	}
	return $s;
}


?>
<div class="row  padding-small">
	<div class="col m12">
		<div id="gantt_here" style="min-height: 600px;"></div>
	</div>
</div>


	
<script>

gantt.init("gantt_here");
	gantt.clearAll(); 
	gantt.config.columns = [
		{name: "text", label: "Name", tree: true, width: "*", resize: true},
		{name: "start_date", label: "Start", align: "center", resize: true},
		{name: "duration", label: "Days", align: "center", resize: true},
		
	];
	gantt.templates.task_class = function (start, end, task) {
		if(task.progress>0.8)
		{
			return "low";
		}else if(task.progress>0.5)
		{
			return "medium";
		}else if(task.progress>0)
		{
			return "high";
		}
		
	};
	gantt.parse({
		data: [
			<?php echo buildData($dt, $columns, $table_name, $table_parent_id, $start_name, $end_name, $rel_id, "");?>
		],
		links: [
			<?php echo buildLink(1, $appSession, $dt, $table_name, $table_parent_id, "");?>
		]
		
	});
	gantt.attachEvent("onTaskClick", function (id, e) {
		
	  gt('contentView', '<?php echo $module_id;?>', 'form', id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>');
	  return true;
	});


</script>

