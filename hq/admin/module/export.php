<?php
require_once(ABSPATH.'config.php' );

$params = "";
$module_id = "";
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
if(isset($_POST['module_id']))
{
	$module_id = $_POST['module_id'];
}

$root_module_id = "";
if(isset($_REQUEST['root_module_id']))
{
	$root_module_id = $_REQUEST['root_module_id'];
}
if(isset($_POST['root_module_id']))
{
	$root_module_id = $_POST['root_module_id'];
}


$module_model_id = "";
if(isset($_REQUEST['module_model_id']))
{
	$module_model_id = $_REQUEST['module_model_id'];
}
if(isset($_POST['module_model_id']))
{
	$module_model_id = $_POST['module_model_id'];
}
$rel_id = "";
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
if(isset($_POST['rel_id']))
{
	$rel_id = $_POST['rel_id'];
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


$module_caption = "";

$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0";



$result = $appSession->getTier()->getArray($sql);
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
if($module_id != $root_module_id)
{
	$sql = "SELECT name FROM ir_module WHERE id='".$root_module_id."'";
	$module_caption = $appSession->getTier()->getValue($sql);
}


$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";




$result = $appSession->getTier()->getArray($sql);
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
		
		$result_data_sql = $appSession->getTier()->getArray($sql);
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
	
	
	
	$search = "";
	if(isset($_SESSION[$module_id."_search"]))
	{
		$search = $_SESSION[$module_id."_search"];
	}
	$search_columns = "";
	if(isset($_SESSION[$module_id."_search_columns"]))
	{
		$search_columns = $_SESSION[$module_id."_search_columns"];
	}
	$sort_columns = "";
	if(count($lines)>0)
	{
		$sort_columns = $lines[0][5];
	}
	
	$p = 0;
	if(isset($_SESSION[$module_id."_p"]))
	{
		$p = $_SESSION[$module_id."_p"];
	}
	$ps = 30;
	if(isset($_SESSION[$module_id."_ps"]))
	{
		$ps = $_SESSION[$module_id."_ps"];
	}
	$searchs = [];
	if($search_columns != "")
	{
		
		$searchs[0] = str_replace("__", ".", $search_columns);
	}else
	{
		for($c =0; $c<count($columns); $c++)
		{
			if($columns[$c][1] != ""  && $columns[$c][2] == "")
			{
				$searchs[count($searchs)] = str_replace("__", ".", $columns[$c][1]);
			}
			
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
	
	

	
	$result = $appSession->getTier()->getTable($sql);
	$numrows = $result->getRowCount();	
	$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	
	
	header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
	header("Content-type:   application/x-msexcel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$table_name.".xls"); 
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	
	

	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<?php $module_caption;?>
<table class="borderLine">
	<thead>
	<tr class="text-left">
		<th  style="width: 20px">#
		</th>
		
		<?php
		for($c =0; $c<count($columns); $c++)
		{
			$catpion = $columns[$c][0];
			$name = $columns[$c][1];
			$width = $columns[$c][3];
			
			if($name != "")
			{
				$type = $columns[$c][2];
				
		?>
		<th style="text-align:center; <?php if($width != ""){?> width: <?php echo $width;?>px <?php }?>"><?php echo $appSession->getLang()->find($catpion);?></th>
		<?php
			}
		}
		?>
		
	</tr>
	</thead>
	<tbody>
	<?php
	$no = ($p * $ps) + 1;
	$hasTotal = 0;
	for($j =0; $j<$numrows; $j++)
	{
		
		$row = $result->getRow($j);
		$id = $row->getString($table_name."__id");
	?>
	<tr>
		<td style="width:20px">
		<?php echo ($no + $j);?>.
		</td>
		
		<?php
		
		for($c =0; $c<count($columns); $c++)
		{
			
			$catpion = $columns[$c][0];
			$name = $columns[$c][1];
			
			$type = $columns[$c][2];
			$width = $columns[$c][3];
			
			$align = $columns[$c][4];
			$forecolor = $columns[$c][5];
			$backcolor = $columns[$c][6];
			$format = $columns[$c][7];
			if($columns[$c][8] != "")
			{
				$hasTotal = 1;
			}
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
			
			
		?>
		<td  style="<?php if($align != ""){?> ;text-align: <?php echo $align;?> <?php } ?> <?php if($forecolor != ""){?> ;color: <?php echo $forecolor;?> <?php } ?><?php if($forecolor != ""){?> ;background-color: <?php echo $backcolor;?> <?php } ?>" >
			<?php echo $name;?>
		</td>
		<?php
			}
		}
		?>
		
	</tr>
	<?php
	}
	?>
	<?php
	if($hasTotal == 1)
	{
	?>
	<tr>
		<td><b>Total</b></td>
		<?php
		
		for($c =0; $c<count($columns); $c++)
		{
			
			
			$name = $columns[$c][1];
			$type = $columns[$c][2];
			$width = $columns[$c][3];
			
			$align = $columns[$c][4];
			$format = $columns[$c][7];
			$format_value = "";
			if($name != "")
			{
				$total = $columns[$c][8];
				$total_amount = 0;
				if($total != "")
				{
					for($j =0; $j<$numrows; $j++)
					{
						
						$row = $result->getRow($j);
						$total_amount = $total_amount + $row->getFloat($name);
					}
				}
				if($type == "float" || $type == "currency" || $type == "integer" || $type == "percent")
				{
					if($align == "")
					{
						$align = "right";
					}
				}
				if($total == "AVG")
				{
					if($numrows>0)
					{
						$total_amount = $total_amount/$numrows;
					}
				}
				if($type == "float" || $type == "integer")
				{
					$format_value = $appSession->getFormats()->getDOUBLE()->format($total_amount);
				}else if($type == "currency")
				{
					$format_value = $appSession->getCurrency()->format($appSession->getConfig()->getProperty("currency_id"), $total_amount);
				}else if($type == "percent")
				{
					$format_value = $appSession->getFormats()->getDOUBLE()->format($total_amount)."%";
				}
			?>
			<td style="<?php if($align != ""){?> ;text-align: <?php echo $align;?> <?php } ?> <?php if($forecolor != ""){?> ;color: <?php echo $forecolor;?> <?php } ?><?php if($forecolor != ""){?> ;background-color: <?php echo $backcolor;?> <?php } ?>"><?php if($total != ""){ echo $format_value; }?></td>
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
</body>
</html>
<?php
}
?>