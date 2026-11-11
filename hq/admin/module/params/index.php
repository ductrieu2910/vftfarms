<?php
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
	
$module_id = "";
if(isset($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
}

$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$msg = $appSession->getTier()->createMessage();


if($ac == "view")
{
	

$id = "";
if(isset($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
}
$filters = "";
if(isset($_REQUEST['filters']))
{
	$filters = $_REQUEST['filters'];
}
$func_apply = "";
if(isset($_REQUEST['func']))
{
	$func_apply = $_REQUEST['func'];
}

$sql = "SELECT d1.name, d1.code, d1.type, d1.value FROM ir_action_param d1 WHERE d1.ir_id='".$id."' AND d1.status =0 ORDER BY d1.sequence ASC";
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
	$arr[3] = $row[3];
	$values[$i] = $arr;
}

$columns = [];
$datas = [];
for($i =0; $i<count($values); $i++)
{
	$caption = $values[$i][0];
	$name = $values[$i][1];
	$type = $values[$i][2];
	$value = $values[$i][3];
	$data = [];
	if(strlen($type) == 36)
	{
		
		$sql = "SELECT group_id, module_id FROM ir_module_model WHERE id ='".$type."'";
		
		$msg->add("query", $sql);
		$list = $appSession->getTier()->getArray($msg);
		if(count($list)>0)
		{
			$sub_module_id = $list[0][1];
			$sub_module_model_id = $list[0][0];
			
			$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$sub_module_id."' AND d2.group_id='".$sub_module_model_id."' AND d1.status =0";
			
			
			$msg->add("query", $sql);
			$sub_result = $appSession->getTier()->getArray($msg);
			$sub_numrows = count($sub_result);
			$sub_values = [];
			for($si =0; $si<$sub_numrows; $si++)
			{
				$row =$sub_result[$si];
				$arr = [];
				$arr[0] = $row[0];
				$arr[1] = $row[1];
				$sub_values[$si] = $arr;
			}
			
			
			$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$sub_module_id."' AND d1.group_id='".$sub_module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
			$msg->add("query", $sql);
			$sub_result = $appSession->getTier()->getArray($msg);
			$sub_numrows = count($sub_result);
			$sub_models = [];
			$sub_columns_model_id = "";
			
			for($si =0; $si<$sub_numrows; $si++)
			{
				$row = $sub_result[$si];
				$arr = [];
				if($row[0] == $sub_module_model_id)
				{
					$type = $row[2];
				}
				$arr[0] = $row[0];
				$arr[1] = $row[1];
				$arr[2] = $row[2];
				$arr[3] = $row[3];
				$sub_models[$si] = $arr;
				if($row[3] == "columns")
				{
					$sub_columns_model_id = $row[0];
				}
				
							
			}
			$sub_columns = [];
			$table_name = "";
			$data_id = "";
			
			
			
			for($si =0; $si<count($sub_models); $si++)
			{
				if($sub_models[$si][1] == $sub_columns_model_id)
				{
					$sub_caption = "";
					$sub_name = "";
					$sub_type = "";
					
					for($sj =0; $sj<count($sub_models); $sj++)
					{
						if($sub_models[$sj][1] == $sub_models[$si][0])
						{
							$value = findValue($sub_values, $sub_models[$sj][0]);
							if($sub_models[$sj][3] == 'caption')
							{
								$sub_caption = $value;
							}
							else if($sub_models[$sj][3] == 'name')
							{
								$sub_name = $value;
							}else if($sub_models[$sj][3] == 'type')
							{
								$sub_type = $value;
								
							}
						}
					}
					$arr = [];
					$arr[0] = $sub_caption;
					$arr[1] = $sub_name;
					$arr[2] = $sub_type;
	
					$sub_columns[count($sub_columns)] = $arr;
				}
				if($sub_models[$si][3] == "src" || $sub_models[$si][2] == "data")
				{
					$data_id = findValue($sub_values, $sub_models[$si][0]);
					
				}
				
			}
			if($data_id != "")
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
				$days = date("Y-m-d", strtotime("-7 days"));
				$sql = str_replace("{7_days}", "'".$days." 00:00:00.000'", $sql);
				$days = date("Y-m-d", strtotime("-1 months"));
				$sql = str_replace("{1_month}", "'".$days." 00:00:00.000'", $sql);
				$days = date("Y-m-d", strtotime("-2 months"));
				$sql = str_replace("{2_month}", "'".$days." 00:00:00.000'", $sql);
				$days = date("Y-m-d", strtotime("-3 months"));
				$sql = str_replace("{3_month}", "'".$days." 00:00:00.000'", $sql);
				
				$sort = "";
				if(count($lines)>0)
				{
					$sort = $lines[0][5];
				}
				if($sort != "")
				{
					$sql = $sql." ORDER BY ".$sort;
				}
				$msg->add("query", $sql);
				$dt = $appSession->getTier()->getTable($msg);
				$dt_numrows = $dt->getRowCount();	
				for($sj =0; $sj<$dt_numrows; $sj++)
				{
					$row = $dt->getRow($sj);
					$id = $row->getString($table_name."__id");
					$arr = [];
					$arr[0] = $id;
					for($c =0; $c<count($sub_columns); $c++)
					{
						$column_name = $sub_columns[$c][1];
						if($column_name != "")
						{
							$column_name = $row->getString($column_name);
							$arr[count($arr)] = $column_name;
						}
					}
					$data[count($data)] = $arr;
				
				}
			}
			
		}
	}else{
		$data[count($data)] = [$value];
	}
	$arr = [];
	$arr[0] = $caption;
	$arr[1] = $name;
	$arr[2] = $type;
	$columns[count($columns)] = $arr;
	$datas[count($datas)] = $data;
	
}
$appSession->getLang()->load($appSession->getTier(), "filters", $appSession->getConfig()->getProperty("lang_id"));

?>

<div class="row">
	<div class="col m12">
	
	<?php echo $appSession->getLang()->find("Filters");?>
	</div>
</div>
<div class="row">
	<div class="col m12">
		<table cellSpacing="0" cellPadding="0" width="100%" border="0">
		
			<tr>
				<td height="23"><a class="button" href="javascript:addCondition(0)"><i class="icon-plus"></i></a> <a class="button" href="javascript:removeConditions()"><i class="icon-minus"></i></a></td>
			</tr>
			
			<tr>
				<td>
					<div id="condition"></div>
				</td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row">
	<div class="col m6">
		<?php 
		if($func_apply == "report")
		{
		?>
		<a href="javascript:applyFilters('')" class="button btn-primary"><?php echo $appSession->getLang()->find("View");?></a> <a href="javascript:applyFilters('word')" class="button"><?php echo $appSession->getLang()->find("Word");?></a> <a href="javascript:applyFilters('excel')" class="button"><?php echo $appSession->getLang()->find("Excel");?></a> <a href="javascript:applyFilters('pdf')" class="button"><?php echo $appSession->getLang()->find("Pdf");?></a>
		<?php
		}else
		{
		?>
		<a href="javascript:applyFilters('filters')" class="button btn-primary"><?php echo $appSession->getLang()->find("Filters");?></a>
		<?php
		}
		?>
		
	</div>
	<div class="col m6" style="text-align:right">
		
		<a onclick="javascript:saveConidition('', '')" class="button btn-default"><?php echo $appSession->getLang()->find("Save");?></a>
	</div>
</div>
<div class="row">
	<div class="col m12" id="pnResource">
		
	</div>
</div>

<script>
var datas = new Array();
<?php
for($i = 0; $i<count($datas); $i++)
{
	$data = $datas[$i];
?>
var items = 
[
	<?php
	for($j = 0; $j<count($data); $j++)
	{
		if($j>0)
		{
			echo ",";
		}
		$items = $data[$j];
	?>
	[
	
		<?php
		for($n =0; $n<count($items); $n++)
		{
			if($n>0)
			{
				echo ",";
			}
			echo "'".str_replace("'", "&#8216;", $items[$n])."'";
		}
		?>
	]
	<?php
	}
	?>
  
];
datas.push(items);
<?php
}
?>

var mColumns = new Array();
function Column(Name, Caption, Type)
{
	this.Name = Name;
	this.Caption = Caption;
	this.Type = Type;
}
<?php
for($i =0; $i<count($columns); $i++)
{
?>
mColumns.push(new Column('<?php echo $columns[$i][1];?>', '<?php echo $appSession->getLang()->find($columns[$i][0]);?>', '<?php echo $columns[$i][2];?>'));
<?php
}
?>
var operator_string=[
["equal", "equal"],
["not_equal", "not equal"],
["in", "in"],
["not_in", "not in"],
["less", "less"],
["less_or_equal", "less or equal"],
["greater", "greater"],
["greater_or_equal", "greater or equal"],
["begins_with", "begins with"],
["not_begins_with", "doesn't begin with"],
["contains", "contains"],
["not_contains", "doesn't contain"],
["ends_with", "ends with"],
["not_ends_with", "doesn't end with"],
["is_empty", "is empty"],
["is_not_empty", "is not empty"],
["is_null", "is null"],
["is_not_null", "is not null"]
];
var operator_number=[
["equal", "equal"],
["not_equal", "not equal"],
["less", "less"],
["less_or_equal", "less or equal"],
["greater", "greater"],
["greater_or_equal", "greater or equal"],
["is_null", "is null"],
["is_not_null", "is not null"]
];

var mFilterLines = new Array();


function FilterLine(filter_id, parent_id, column_name, func, op, value, logic, text)
{
	this.filter_id = filter_id;
	this.parent_id = parent_id;
	this.column_name = column_name;
	this.func = func;
	this.op = op;
	this.value = value;
	this.logic = logic;
	this.text = text;
}
<?php 
$arr_filters = $appSession->getTool()->split($filters, ',');

for($i =0; $i<count($arr_filters); $i++)
{
	$items = explode(";", $arr_filters[$i]);
	$filter_id = "";
	$parent_id = "";
	$column_name = "";
	$func = "";
	$op = "";
	$value = "";
	$logic = "";
	for($j =0; $j<count($items); $j++)
	{
		
		
		$arr = explode("=", $items[$j]);
		if(count($arr)>1)
		{
			if($arr[0] == "i")
			{
				$filter_id = $arr[1];
				
			}else if($arr[0] == "p")
			{
				$parent_id = $arr[1];
			}
			else if($arr[0] == "n")
			{
				$column_name = $arr[1];
			}else if($arr[0] == "f")
			{
				$func = $arr[1];
			}else if($arr[0] == "o")
			{
				$op = $arr[1];
				
			}else if($arr[0] == "v")
			{
				$value = $arr[1];
				
			}else if($arr[0] == "l")
			{
				$logic = $arr[1];
				
			}
		
		}
	}
	if( $filter_id != "" && $column_name != "__create_uid__")
	{
		if($op == "in" || $op == "not-in")
		{
			$value = $appSession->getTool()->replace($value, "~", ",");
		}
	?>
	
	mFilterLines.push(new FilterLine('<?php echo $filter_id;?>', '<?php echo $parent_id;?>', '<?php echo $appSession->getTool()->replace($column_name, ".", "__");?>', '<?php echo $func;?>', '<?php echo $op;?>','<?php echo $value;?>' ,'<?php echo $logic;?>', ''));

<?php	
	}
}
?>
var filterUser = new Array();
function checkUser(theCheckBox, rel_id)
{
	var value = "";
	var checkboxes = document.getElementsByName(theCheckBox.name);
	for (var i= 0; i<checkboxes.length; i++) 
	{
		if(checkboxes[i].checked == true)
		{
			if(checkboxes[i].value == "")
			{
				continue;
			}
			if(value != "")
			{
				value = value + "~";
			}
			value = value + checkboxes[i].value;
		}				
	}
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.column_name == rel_id)
		{
			line.value = value;
			
			break;
		}
	}
}
function checkDepartment(value, rel_id)
{
	
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.column_name == rel_id)
		{
			line.value = value;
			
			break;
		}
	}
}
function findDateValue(type, line)
{
	
	var value = "";
	if(type == "TODAY")
	{
		
		var now = new Date();
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		var hour = now.getHours();
		if (hour < 10) 
			hour = "0" + hour;
		
		var minutes = now.getMinutes();
		if (minutes < 10) 
			minutes = "0" + minutes;
		
		var seconds = now.getSeconds();
		if (seconds < 10) 
			seconds = "0" + seconds;
		
		value = now.getFullYear() + '-' + month + '-' + day + 'T' + hour + ':' + minutes+ ':' + seconds;
	
	}else if(type == "TODAY6AM")
	{
		var now = new Date();
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		var hour = 6;
		if (hour < 10) 
			hour = "0" + hour;
		
		var minutes = 0;
		if (minutes < 10) 
			minutes = "0" + minutes;
		
		var seconds = 0;
		if (seconds < 10) 
			seconds = "0" + seconds;
		
		value = now.getFullYear() + '-' + month + '-' + day + 'T' + hour + ':' + minutes+ ':' + seconds;
	
	}
	else if(type == "YESTERDAY")
	{
		
		var dValue = line.value;
		var index = dValue.indexOf('T');
		
		if(index != -1)
		{
			dValue = dValue.substring(index);
		}else{
			dValue = "T00:00:00";
		}
		
		var now = new Date();
		now.setDate(now.getDate() - 1);

		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		value = now.getFullYear() + '-' + month + '-' + day + dValue;
		
	}else if(type == "LAST_7_DAYS")
	{
		var dValue = line.value;
		var index = dValue.indexOf('T');
		
		if(index != -1)
		{
			dValue = dValue.substring(index);
		}else{
			dValue = "T00:00:00";
		}
		
		
		var now = new Date();
		now.setDate(now.getDate() - 7);

		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		value = now.getFullYear() + '-' + month + '-' + day + dValue;
		
	}else if(type == "LAST_30_DAYS")
	{
		var dValue = line.value;
		var index = dValue.indexOf('T');
		
		if(index != -1)
		{
			dValue = dValue.substring(index);
		}else{
			dValue = "T00:00:00";
		}
		
		var now = new Date();
		now.setDate(now.getDate() - 30);
		
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		value = now.getFullYear() + '-' + month + '-' + day + dValue;
	}else if(type == "MONTH")
	{
		var dValue = line.value;
		var index = dValue.indexOf('T');
		
		if(index != -1)
		{
			dValue = dValue.substring(index);
		}else{
			dValue = "T00:00:00";
		}
		
		var now = new Date();
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		value = now.getFullYear() + '-' + month + dValue;
	}else if(type == "YEAR")
	{
		var dValue = line.value;
		var index = dValue.indexOf('T');
		
		if(index != -1)
		{
			dValue = dValue.substring(index);
		}else{
			dValue = "T00:00:00";
		}
		var now = new Date();
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		value = now.getFullYear() + '-01-01'+ dValue;
	}else{
		var now = new Date();
		var month = (now.getMonth() + 1);               
		var day = now.getDate();
		if (month < 10) 
			month = "0" + month;
		if (day < 10) 
			day = "0" + day;
		var hours = now.getHours();
		if(hours<10)
		{
			hours = "0" + hours;
		}
		var minutes = now.getMinutes();
		if(minutes<10)
		{
			minutes = "0" + minutes;
		}
		var seconds = now.getSeconds();
		if(seconds<10)
		{
			seconds = "0" + seconds;
		}
		
		value = now.getFullYear() + '-' + month + '-' + day + 'T' + hours + ':' + hours + ':' + seconds;
	}
	return value;
}
function TravelColumn(parent_id, space)
{
	var str = "";
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		var selected = "";
		if (line.parent_id == parent_id)
		{
			
			str += "<table border =\"0\" style=\"border:#CCCCCC\" width=\"100%\"><tr>";
			
	
			
			str += "<td valign=\"top\" style=\"min-width:80px\"><select class=\"select\" id=\"cbColumnName_" + line.filter_id + "\" onchange=\"cbColumnNameChanged('" + line.filter_id + "')\">";
			str += "<option value=\"\"></option>";
			var selectedColumn = null;
			var columnIndex = -1;
			for (var j = 0; j < mColumns.length; j++)
			{
				var c = mColumns[j];
				if (c.Name == line.column_name)
				{
					selectedColumn = c;
					columnIndex = j;
					selected = "selected";
				}
				str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
				selected = "";
			}
			str += "</select></td>";
			
				
			if(selectedColumn != null  && selectedColumn.Name != "")
			{
				if(selectedColumn.Type == 'assigned')
				{
				
				
				}else{
					if (selectedColumn.Type == "datetime" || selectedColumn.Type == "date")
					{
						str += "<td valign=\"top\"><select class='select' id=\"cbFunction_" + line.filter_id + "\" onchange=\"cbFunctionChanged('" + line.filter_id + "')\">";

						if (line.func == "")
						{
							selected = "selected ";
							if(line.value == ""){
								line.value = findDateValue("", line);
							}
						}
						str += "<option value = \"\" " + selected + "></opition>";
						selected = "";
						if (line.func == "TODAY")
						{
							selected = "selected ";
							line.value = findDateValue("TODAY", line);
						}
						str += "<option value = \"TODAY\" " + selected + ">Today</opition>";
						selected = "";
						
						
						selected = "";
						if (line.func == "TODAY6AM")
						{
							selected = "selected ";
							line.value = findDateValue("TODAY6AM", line);
						}
						str += "<option value = \"TODAY6AM\" " + selected + ">Today 6AM</opition>";
						selected = "";
						if (line.func == "YESTERDAY")
						{
							selected = "selected ";
							line.value = findDateValue("YESTERDAY", line);
						}
						str += "<option value = \"YESTERDAY\" " + selected + ">Yesterday</opition>";
						
						selected = "";
						if (line.func == "LAST_7_DAYS")
						{
							selected = "selected ";
							line.value = findDateValue("LAST_7_DAYS", line);
						}
						str += "<option value = \"LAST_7_DAYS\" " + selected + ">Last 7 days</opition>";
						
						selected = "";
						if (line.func == "LAST_30_DAYS")
						{
							selected = "selected ";
							line.value = findDateValue("LAST_30_DAYS", line);
						}
						str += "<option value = \"LAST_30_DAYS\" " + selected + ">Last 30 days</opition>";
						
						selected = "";
						if (line.func == "MONTH")
						{
							selected = "selected ";
							line.value = findDateValue("MONTH", line);
						}
						str += "<option value = \"MONTH\" " + selected + ">This month</opition>";
						selected = "";
						
						selected = "";
						if (line.func == "YEAR")
						{
							selected = "selected ";
							line.value = findDateValue("YEAR", line);
						}
						str += "<option value = \"YEAR\" " + selected + ">This year</opition>";
						selected = "";
						str += "</select></td>";
					}
					
					
					var type = selectedColumn.Type;
					
					
					

					
					str += " <td>";
					if (type =="smallint" || type=="number" || type =="int")
					{
						str += "<input type='number' style='text-algin:right' value=\"" + line.value + "\" class='input' id=\"cbColumnValue_1_" + line.filter_id + "\" onblur=\"cbColumnValue_1Changed('" + line.filter_id + "')\"/>";
					}else if (type =="date" || type=="datetime" )
					{
						str += "<input type='datetime-local' value=\"" + line.value + "\" class='input' id=\"cbColumnValue_1_" + line.filter_id + "\" onchange=\"cbColumnValue_1Changed('" + line.filter_id + "')\"/>";
					}else if(type == 'dropdown')
					{
						str += "<select class='select' id=\"cbColumnValue_1_" + line.filter_id + "\" onchange=\"cbColumnValue_1Changed('" + line.filter_id + "')\">";
						if(columnIndex != -1)
						{
							var items = datas[columnIndex];
							str += "<option value=\"\"></option>";
						
							for (var j = 0; j < items.length; j++)
							{
								if (line.value == items[j][0])
								{
									selected = " selected ";
								}
								str += "<option value=\"" + items[j][0] + "\" " + selected + ">" + items[j][1] + "</option>";
								selected = "";
							}
						}
						
						str += "</select>";
					}else if( type == 'list')
					{
						if(columnIndex != -1)
						{
							var items = datas[columnIndex];
							
							for (var j = 0; j < items.length; j++)
							{
								var selected = "";
							
								if (line.value.indexOf(items[j][0]) != -1)
								{
									selected = " checked ";
								}
								if(j>0)
								{
									str = str + "<br>";
								}
								var name = items[j][1];
								for(var k =2; k<items[j].length; k++)
								{
									name = name + "/" + items[j][k];
								}
								str += "<label><input onchange=\"\" type=\"checkbox\" value=\"" + items[j][0] + "\" " +selected + ">" + name + "</label>";
		
							}
							
						}
					}
					
					else{
						var placeholder = "";
						if(line.op == 'in' || line.op == 'not_in')
						{
							placeholder = "Value1, Value2";
						}
						str += "<input type='text' value=\"" + line.value + "\" class='input' id=\"cbColumnValue_1_" + line.filter_id + "\" placeholder=\"" + placeholder + "\" onblur=\"cbColumnValue_1Changed('" + line.filter_id + "')\"/>";
					}
					str += "</td>";
					
					
					
					
					str += "";
				}
				
			}
			str += "<td valign=\"top\"> ";
			str += '<a class="button" href="javascript:addCondition('+line.filter_id+')"><i class="icon-plus"></i></a>';
			str += "</td>";
			str += "<td valign=\"top\">";
			str += '<a class="button" href="javascript:removeCondition('+line.filter_id+')"><i class="icon-minus"></i></a>';
			str += "</td>";
			str += "</tr>";
			
			str += "</table>";
			if(selectedColumn != null  && selectedColumn.Name != "")
			{
				if(selectedColumn.Type == 'assigned')
				{
				
					str = str + "<table><tr><td><div id='pn" + selectedColumn.Name+ "'></div></td></tr></table>";
					filterUser.push(selectedColumn.Name);
				}
			}
			var innerStr = TravelColumn(line.filter_id, space + 20);
			if (innerStr != "")
			{
				str +=  '<div style="margin-left:' + (space +20)+ 'px;">(' + innerStr + ')</div>';
			}
		}
	}
	
	return str;
}
function buildWhere()
{
	filterUser = new Array();
	document.getElementById('condition').innerHTML =TravelColumn(0, 0);
	for(var i=0; i<filterUser.length; i++)
	{
		var value = "";
		for(var i=0; i<mFilterLines.length; i++)
		{
			
			if(mFilterLines[i].column_name == filterUser[i])
			{
				
				value = mFilterLines[i].value;
				
				break;
			}
		}
		
		var _url = "<?php echo URL;?>admin/module/assigned/?func=checkUser&type=checkbox&rel_id=" + filterUser[i];
		_url = _url + "&value=" + encodeURIComponent(value);
		loadPage('pn' + filterUser[i], _url, function(status, message)
		{
			if(status== 0)
			{
			}
			
		}, false);
	}
}

function addCondition(parent_id) {
	var id = 0;
	for(var i=0; i<mFilterLines.length; i++)
	{
		if(mFilterLines[i].filter_id>id)
		{
			id = mFilterLines[i].filter_id;
		}
	}
	id = id + 1;
	var item = new FilterLine(id, parent_id, "", "", "", "", "AND", "");
	mFilterLines.push(item);
	buildWhere();
	
}
function findTree(id)
{
	var s = "";
	for(var i=0; i<mFilterLines.length; i++)
	{
		if(mFilterLines[i].parent_id == id)
		{
			s = s + "[" + mFilterLines[i].filter_id + "]";
			s = s + findTree(mFilterLines[i].filter_id);
		}
	}
	return s;
}
function removeCondition(id)
{
	var result = confirm("Are you sure to remove?");
	if (!result) {
		return;
	}
	var arr = Array();
	var s = "[" + id + "]" + findTree(id);
	for(var i=0; i<mFilterLines.length; i++)
	{
		if(s.indexOf("[" + mFilterLines[i].filter_id + "]") == -1)
		{
			arr.push(mFilterLines[i]);
		}
	}
	mFilterLines = arr;
	buildWhere();
}
function removeConditions()
{
	var result = confirm("Are you sure to clear?");
	if (!result) {
		return;
	}
	mFilterLines = new Array();
	buildWhere();
}
function cbLogicChanged(id)
{
	
	var cb = document.getElementById('cbLogic_' + id);

	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.logic = cb.value;
			buildWhere();
			
			break;
		}
	}
}
function cbColumnNameChanged(id) {
	var cb = document.getElementById('cbColumnName_' + id);

	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.column_name = cb.value;
			buildWhere();
			
			break;
		}
	}
	
}
function cbOperatorChanged(id) {
	var cb = document.getElementById('cbOperator_' + id);
	
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.op = cb.value;
			//buildWhere();
			
		}
	}
}
function cbCheckboxValue_1Changed(theInput, line) {
	var value = theInput.value;
	var arrValues = line.value.split(',');
	if(theInput.checked == true)
	{
		var hasItem= false;
		for(var i =0; i<arrValues.length; i++)
		{
			if(arrValues[i] == value)
			{
				hasItem = true;
			}
		}
		if(hasItem == false)
		{
			arrValues.push(value);
		}
	}else{
		var arr = new Array();
		for(var i =0; i<arrValues.length; i++)
		{
			if(arrValues[i] != value)
			{
				arr.push(arrValues[i]);
			}
		}
		arrValues = arr;
	}
	value = "";
	for(var i =0; i<arrValues.length; i++)
	{
		if(value != "")
		{
			value = value + ",";
		}
		value = value + arrValues[i];
	}
	line.value= value;
}
function cbColumnValue_1Changed(id) {
	var cb = document.getElementById('cbColumnValue_1_' + id);
	
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.value = cb.value;
		
			if(cb.type == "select-one")
			{
				line.text = cb.options[cb.selectedIndex].text;
			}else{
				line.text = "";
			}
			buildWhere();
			
		}
	}
}


function tfFunctionValueBlur(id) {
	var cb = document.getElementById('tfFunctionValue_' + id);
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.func = cb.value;
			//buildWhere();
			
		}
	}
}
function cbFunctionChanged(id) {
	var cb = document.getElementById('cbFunction_' + id);
	
   for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(line.filter_id == id)
		{
			line.func = cb.value;
			buildWhere();
			
		}
	}
}	
function applyFilters(view_type)
{
	
	var data = "";
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(data != "")
		{
			data = data + "&";
		}
		var value  = line.value;
		value = value.trim();
		if(value == "")
		{
			continue;
		}
		if(line.op == 'in' || line.op == 'not_in')
		{
			while(true)
			{
				var index = value.indexOf(",");
				if(index == -1)
				{
					break;
				}
				value = value.replace(",", "~");
			}
			
		}
		
		var type = 's';
		var op = line.op;
		var caption = "";
		for (var j = 0; j < mColumns.length; j++)
		{
			var c = mColumns[j];
			if (c.Name == line.column_name)
			{
				
				var ops = operator_string;

				if (c.Type =="smallint" || c.Type=="number" || c.Type =="int")
				{
					type='n';
				}else if(c.Type == "datetime" || c.Type=="time" || c.Type == "date")
				{
					type='d';
					
					value = value.replace("T", " ");
					var index = value.indexOf(" ");
					if(index != -1)
					{
						var value1 = value.substring(index + 1);
						if(value1.length<=5)
						{
							value = value + ":00";
						}
					}
					//value = value + ":00";
				}
				caption = c.Caption;
			}
			
		}
		
		data = data + line.column_name+ "=" + encodeURIComponent(value);
		
	}
	
	if(view_type == "filters")
	{
		closePopup();
		<?php echo $func_apply;?>(data);
	}else
	{
		window.open('<?php echo URL;?>api/action/?id=<?php echo $id;?>&content_type='+ view_type + '&' + data);
	}
	
	
}
function loadResource()
{
	var _url = '<?php echo URL;?>admin/module/filters/?ac=list_resource';
	_url = _url + '&rel_id=<?php echo $module_id;?>';
	loadPage('pnResource', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
function removeResource(id)
{
	var result = confirm("Are you sure to remove?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/module/filters/?ac=removeResource';
	_url = _url + '&id=' + id;
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadResource();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
}
loadResource();
function selResource(id)
{
	var _url = '<?php echo URL;?>admin/module/filters/?ac=selResource';
	_url = _url + '&id=' + id;
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message != "")
			{
				mFilterLines = new Array();
				var arr_filters = message.split(',');
				for(var i =0; i<arr_filters.length; i++)
				{
					var items = arr_filters[i].split(';');
					var filter_id = "";
					var parent_id = "";
					var column_name = "";
					var func = "";
					var op = "";
					var value = "";
					var logic = "";
					for(var j =0; j<items.length; j++)
					{
						
						
						var arr = items[j].split('=');
						if(arr.length>1)
						{
							if(arr[0] == "i")
							{
								filter_id = arr[1];
								
							}else if(arr[0] == "p")
							{
								parent_id = arr[1];
							}
							else if(arr[0] == "n")
							{
								column_name = arr[1];
							}else if(arr[0] == "f")
							{
								func = arr[1];
							}else if(arr[0] == "o")
							{
								op = arr[1];
								
							}else if(arr[0] == "v")
							{
								value = arr[1];
								
							}else if(arr[0] == "l")
							{
								logic = arr[1];
								
							}
						
						}
					}
					if( filter_id != "")
					{
						if(op == 'in' || op == 'not_in')
						{
							while(true)
							{
								var index = value.indexOf("~");
								if(index == -1)
								{
									break;
								}
								value = value.replace("~", ",");
							}
							
						}
		
						mFilterLines.push(new FilterLine(filter_id, parent_id, column_name, func, op,value ,logic, ''));
					}
				}
				buildWhere();
			}
		}
		
	}, true);
}
function saveConidition(id, name)
{
	var data = "";
	for(var i=0; i<mFilterLines.length; i++)
	{
		var line = mFilterLines[i];
		if(data != "")
		{
			data = data + ",";
		}
		var value  = line.value;
		if(line.op == 'in' || line.op == 'not_in')
		{
			while(true)
			{
				var index = value.indexOf(",");
				if(index == -1)
				{
					break;
				}
				value = value.replace(",", "~");
			}
			
		}
		var caption = "";
		for (var j = 0; j < mColumns.length; j++)
		{
			var c = mColumns[j];
			if (c.Name == line.column_name)
			{
				caption =c.Caption;
			}
			
		}
		data = data + "i=" +line.filter_id + ";p=" +line.parent_id + ";n=" +line.column_name + ";f=" +line.func + ";o=" +line.op + ";v=" + value + ";l=" + line.logic + ";c=" + caption;
	}
	if(data == "")
	{
		alert("Please add condition");
		return;
	}
	var name = prompt("Please enter name", name);
	if (name == null) {
	  return;
	}
	name = name.trim();
	if (name == "") 
	{
		alert("Name is not empty");
		return;
	}
	
	var _url = '<?php echo URL;?>admin/module/filters/?ac=saveConidition';
	_url = _url + '&rel_id=<?php echo $module_id;?>';
	_url = _url + "&data=" + encodeURIComponent(data);
	_url = _url + "&name=" + encodeURIComponent(name);
	_url = _url + "&id=" + id;
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadResource();
				
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
}
			
buildWhere();
</script>
<?php
}else if($ac == "saveConidition")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$data = '';
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$sql = "SELECT id FROM res_resource WHERE id='".$id."'";
	$msg->add("query", $sql);
	$id = $appSession->getTier()->getValue($msg);
	if($id == "")
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO res_resource(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql.", name";
		$sql = $sql.", resource";
		$sql = $sql.", type";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", '".$appSession->getConfig()->getProperty("root_company_id")."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".str_replace("'", "''", $data)."'";
		$sql = $sql.", 'filters'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		
		$appSession->getTier()->exec($msg);
		
	}else
	{
		$msg->add("query", $sql);
		$sql = "UPDATE res_resource SET write_date=".$appSession->getTier()->getDateString();
		$sql = $sql.", resource='".str_replace("'", "''", $data)."'";
		$sql = $sql.", name='".str_replace("'", "''", $name)."'";
		$sql = $sql." WHERE id='".$id."'";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
	
	echo "OK";
}else if($ac == "list_resource")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT id, name FROM res_resource WHERE rel_id='".$rel_id."' AND status =0 AND type='filters'";
	$sql = $sql." ORDER BY name ASC";
	
	$msg->add("query", $sql);
	$list = $appSession->getTier()->getArray($msg);
	if(count($list)>0)
	{
	?>
	<hr>
	<div class="table-responsive">
		<table class="table">
			<tbody>
			<?php
			for($i =0; $i<count($list); $i++)
			{
			?>
			<tr>
				<td><a href="javascript:selResource('<?php echo $list[$i][0];?>')"><?php echo $list[$i][1];?></a></td>
				<td width="64"><a class="button" href="javascript:saveConidition('<?php echo $list[$i][0];?>', '<?php echo $list[$i][1];?>')"><i class="icon-refresh"></i></a> <a class="button" href="javascript:removeResource('<?php echo $list[$i][0];?>')"><i class="icon-minus"></i></a></td>
			</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
	}
}else if($ac == "removeResource")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_resource SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "selResource")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT resource FROM res_resource WHERE id='".$id."'";
	$msg->add("query", $sql);
	echo $appSession->getTier()->getValue($msg);
}
?>
