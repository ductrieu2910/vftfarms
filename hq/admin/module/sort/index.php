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
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
	
$pageIndex = "0";
if(isset($_REQUEST['pageIndex']))
{
	$pageIndex = $_REQUEST['pageIndex'];
}
$sort = "";
if(isset($_REQUEST['sort']))
{
	$sort = $_REQUEST['sort'];
}

$module_caption = "";



$sql = "SELECT d1.module_model_id, d1.data, d4.name AS module_name FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_module d4 ON(d2.module_id = d4.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0";


$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$values = [];
$module_caption = "";
for($i =0; $i<$numrows; $i++)
{
	$row =$result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$values[$i] = $arr;
	$module_caption = $row[2];
}


$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
$filters_model_id = "";
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
for($i =0; $i<count($models); $i++)
{
	if($models[$i][1] == $columns_model_id)
	{
		$caption = "";
		$name = "";
		$type = "";
		
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
					
				}
			}
		}
		
		$arr = [];
		$arr[0] = $caption;
		$arr[1] = $name;
		$arr[2] = $type;
		$columns[count($columns)] = $arr;
	}
}

?>
<div class="row">
	<div class="col m12">
	Sort <?php echo $module_caption;?>
	</div>
</div>
<div class="row">
	<div class="col m12">
		<table cellSpacing="0" cellPadding="0" width="100%" border="0">
			<tr>
				<td height="23"> <a class="button" href="javascript:addSort()"><i class="icon-plus"></i></a> <a class="button" href="javascript:removeSorts()"><i class="icon-minus"></i></a></td>
			</tr>
			
			<tr>
				<td>
					<div id="pnSort"></div>
				</td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row">
	<div class="col m6">
		<a href="javascript:applySort()" class="button btn-primary"><?php echo $appSession->getLang()->find("Sort");?></a>
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

var mLines = new Array();
var mColumns = new Array();
function Column(Name, Caption, Type)
{
	this.Name = Name;
	this.Caption = Caption;
	this.Type = Type;
}
function Line(id, name, type)
{
	this.id = id;
	this.name = name;
	this.type = type;
}
<?php
for($i =0; $i<count($columns); $i++)
{
?>
mColumns.push(new Column('<?php echo $appSession->getTool()->replace($columns[$i][1], "__", ".");?>', '<?php echo $columns[$i][0];?>', '<?php echo $columns[$i][2];?>'));
<?php
}
?>

<?php 
$arr_sort = $appSession->getTool()->split($sort, ',');

for($i =0; $i<count($arr_sort); $i++)
{
	$items = explode(" ", $arr_sort[$i]);
	$id = 0;
	$name = "";
	$type = "";
	if(count($items)>1)
	{
		$id = $id + 1;
		$name = $items[0];
		$type = $items[1];
	}
	
	
	if( $name != "")
	{
	?>
	
	mLines.push(new Line('<?php echo $id;?>', '<?php echo $name;?>', '<?php echo $type;?>'));

<?php	
	}
}
?>
function TravelColumn()
{
	var str = "";
	str += "<table border =\"0\" style=\"border:#CCCCCC\"><tr>";
	for(var i=0; i<mLines.length; i++)
	{
		var line = mLines[i];
		str += "<td><select class=\"select\" id=\"cbColumnName_" + line.id + "\" onchange=\"cbColumnNameChanged('" + line.id + "')\">";
		str += "<option value=\"\"></option>";
		var selectedColumn = null;
		var columnIndex = -1;
		var selected = "";
		for (var j = 0; j < mColumns.length; j++)
		{
			var c = mColumns[j];
			if (c.Name == line.name)
			{
				selectedColumn = c;
				columnIndex = j;
				selected = "selected";
			}
			str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
			selected = "";
		}
		str += "</select></td>";
		
			
		str += "<td><select class='select' id=\"cbTypeChanged_" + line.id + "\" onchange=\"cbTypeChanged('" + line.id + "')\">";
		str += "<option value=''></option>";
		selected = "";
		if (line.type == "ASC")
		{
			selected = "selected ";
		}
		str += "<option value='ASC' " + selected + ">ASC</option>";
		selected = "";
		if (line.type == "DESC")
		{
			selected = "selected ";
		}
		str += "<option value='DESC' " + selected + ">DESC</option>";
		selected = "";
		str += "</select></td>";
		str += "<td>";
		str += '<a class="button" href="javascript:removeSort('+line.id+')"><i class="icon-minus"></i></a>';
		str += "</td>";
		str += "</tr>";
	}
	str += "</table>";
	
	return str;
}
function buildWhere()
{
	
	document.getElementById('pnSort').innerHTML =TravelColumn(0, 0);
}

function addSort() {
	var id = 0;
	for(var i=0; i<mLines.length; i++)
	{
		if(mLines[i].id>id)
		{
			id = mLines[i].id;
		}
	}
	id = id + 1;
	
	var item = new Line(id, "", "ASC");
	mLines.push(item);
	buildWhere();
	
}

function removeSort(id)
{
	var result = confirm("Are you sure to remove?");
	if (!result) {
		return;
	}
	var arr = Array();
	for(var i=0; i<mLines.length; i++)
	{
		if(mLines[i].id != id)
		{
			arr.push(mLines[i]);
		}
	}
	mLines = arr;
	buildWhere();
}
function removeSorts()
{
	var result = confirm("Are you sure to clear?");
	if (!result) {
		return;
	}
	mLines = new Array();
	buildWhere();
}
function cbTypeChanged(id)
{
	
	var cb = document.getElementById('cbTypeChanged_' + id);

	for(var i=0; i<mLines.length; i++)
	{
		var line = mLines[i];
		if(line.id == id)
		{
			line.type = cb.value;
			buildWhere();
			
			break;
		}
	}
}
function cbColumnNameChanged(id) {
	var cb = document.getElementById('cbColumnName_' + id);

	for(var i=0; i<mLines.length; i++)
	{
		var line = mLines[i];
		if(line.id == id)
		{
			line.name = cb.value;
			
			buildWhere();
			
			break;
		}
	}
	
}


function applySort()
{
	var data = "";
	for(var i=0; i<mLines.length; i++)
	{
		var line = mLines[i];
		if(line.name == "" || line.type == "")
		{
			continue;
		}
		if(data != "")
		{
			data = data + ",";
		}
		data = data + "" +line.name + " " +line.type;
	}
	
	closePopup();
	loadSort<?php echo $pageIndex;?>(data);
	
}
function loadResource()
{
	var _url = '<?php echo URL;?>admin/module/sort/?ac=list_resource';
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
	var _url = '<?php echo URL;?>admin/module/sort/?ac=removeResource';
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
	var _url = '<?php echo URL;?>admin/module/sort/?ac=selResource';
	_url = _url + '&id=' + id;
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message != "")
			{
				mLines = new Array();
				var arr_filters = message.split(',');
				for(var i =0; i<arr_filters.length; i++)
				{
					var items = arr_filters[i].split(' ');
					var id = "";
					var name = "";
					var type = "";
					if(items.length>2)
					{
						id = items[0];
						name = items[1];
						type = items[2];
					}
					
	
					if( id != "")
					{
						
		
						mLines.push(new Line(id, name, type));
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
	for(var i=0; i<mLines.length; i++)
	{
		var line = mLines[i];
		if(data != "")
		{
			data = data + ",";
		}
		
		data = data + line.id + " " +line.name + " " +line.type;
	}
	if(data == "")
	{
		alert("Please add Sort");
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
	var _url = '<?php echo URL;?>admin/module/sort/?ac=saveConidition';
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
		$sql = $sql.", 'sort'";
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
	$sql = "SELECT id, name FROM res_resource WHERE rel_id='".$rel_id."' AND create_uid='".$appSession->getUserInfo()->getId()."' AND status =0 AND type='sort'";
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
