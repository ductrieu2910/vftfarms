<?php

require_once('../config.php' );
require_once('../tool.php' );

$line_id = '';
if(isset($_REQUEST['id']))
{
	$line_id = $_REQUEST['id'];
}
$data = "";
$sql = "SELECT data FROM ir_module_line WHERE id='".$line_id."'";
$result = pg_exec($db, $sql);
$numrows = pg_numrows($result);	
if($numrows>0)
{
	$row = pg_fetch_array($result, 0);
	$data = $row["data"];
	
}
$module_id = '';
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
$sql = "SELECT d1.id, d1.parent_id, d2.name, d1.model_id, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.status =0 AND d1.module_id='".$module_id."' ORDER BY d1.create_date ASC";


$result = pg_exec($db, $sql);
$numrows = pg_numrows($result);	
$models = [];
$arrIndex = 0;
for($i =0; $i<$numrows; $i++)
{
	$row = pg_fetch_array($result, $i);
	$arr = [];
	$arr[0] = $row["id"];
	$arr[1] = $row["parent_id"];
	$arr[2] = $row["name"];
	$arr[3] = $row["model_id"];
	$arr[4] = $row["rename"];
	
	$sql = "SELECT data FROM ir_module_model_attribute WHERE module_model_id='".$row["id"]."'";
				
	$result_data_value = pg_exec($db, $sql);
	$numrows_data_value = pg_numrows($result_data_value);
	$data_value = "";
	
	if($numrows_data_value>0)
	{
		$row1 = pg_fetch_array($result_data_value, 0);
		$data_value = $row1["data"];
	}
	$arr[5] = $data_value;
	$models[$arrIndex] = $arr;
	$arrIndex = $arrIndex + 1;
}

function buildModuleTree($lines, $parent_id, $root_id)
{
	
	$count = 0;
	for($j =0; $j<count($lines); $j++)
	{
		if($lines[$j][1] == $parent_id )
		{
			$count += 1;
		}
	}
	for($j =0; $j<count($lines); $j++)
	{
		if($lines[$j][1] == $parent_id )
		{
			$id = $lines[$j][0];
			$name = $lines[$j][2];
			$model_id = $lines[$j][3];
			$rename = $lines[$j][4];
			if($parent_id == "")
			{
				$root_id = $id;
			}
			$data_value = $lines[$j][5];
			if($data_value != "")
			{
				$data_value = " - ".$data_value;
			}
	?>
	<tr>
		<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
		<td style="padding-left:5px; padding-top:5px"><span id="<?php if($rename != ""){ echo $rename; }else{ echo $name;}?>,<?php echo $id;?>" draggable="true" ondragstart="drag(event)"><?php if($rename != ""){ echo $rename." (".$name.")"; }else{ echo $name;}?><?php echo $data_value;?></span></td>
	  </tr>
	  
	  <tr>
		<td width="15" <?php if($j<$count-1){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php }?>></td>
		<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildModuleTree($lines, $id, $root_id);?></table></td>
	</tr>
	<?php
	
		}
	}
}

	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo META_TITLE;?></title>
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" />
<script src="<?php echo URL;?>assets/js/jquery.min.js"></script>
<script src="<?php echo URL;?>assets/js/controller.js?v=2"></script>
<script src="<?php echo URL;?>assets/js/tool.js"></script>
<script src="<?php echo URL;?>assets/js/back_to_top.js"></script>
<link href="<?php echo URL;?>assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
<body>
<?php
$data = str_replace("\n", "", $data);
$arr = explode(':', $data);
?>
<div class="row">
	<div class="col-1"><a href="javascript:saveData()">Save</a></div>
	<div class="col-1"><a href="javascript:clearLine()">Clear</a></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
	<div class="col-1"></div>
</div>

<table width="100%">
<tr>
<td valign="top">

<div id="contentView" class="container" style="height:400px; overflow:auto"></div>
<div class="container">
Value: <span id="item_id"></span> <br><textarea id="editvalue" class="textar" style="width:100%"></textarea><br>
Data: <br><textarea id="editData" class="input" style="width:100%"></textarea><br>
Style: <br><textarea id="editstyle" class="input" style="width:100%"></textarea><br>
<a href="javascript:saveItem()">Save Item</a>
</div>
</td>
<td  valign="top" width="200"  >
<div style="height:400px; overflow:auto">
<table>
	<?php echo buildModuleTree($models, "", "");?>
</table>
			
</div>
</td>
</tr>
</table>

<script type="text/javascript">
 function Item(id, parent_id, type, value, data, style)
 {
	 this.id = id;
	 this.parent_id = parent_id;
	 this.type = type;
	 this.value = value;
	 this.data = data;
	 this.style = style;
 
 }
 var lines = new Array();

 <?php
 for($i=0; $i<count($arr); $i++)
 {
	 
	 $item = explode(';', $arr[$i]);
	 $id = "";
	 $parent_id = "";
	 $type = "";
	 $value = "";
	 $data = "";
	 $style = "";
	 for($j=0; $j<count($item); $j++)
	 {
		 $arrItem = explode('=', $item[$j]);
		 if(count($arrItem)>0)
		 {
			 if($arrItem[0] == "id")
			 {
				 $id = $arrItem[1];
			 }else if($arrItem[0] == "parent_id")
			 {
				 $parent_id = $arrItem[1];
			 }else if($arrItem[0] == "type")
			 {
				 $type = $arrItem[1];
			 }else if($arrItem[0] == "value")
			 {
				 $value = $arrItem[1];
			 }else if($arrItem[0] == "data")
			 {
				 $data = $arrItem[1];
			 }else if($arrItem[0] == "style")
			 {
				 $style = str_replace("~", ";", $arrItem[1]);
				 $style = str_replace("|", ":", $style);
			 }
		 }
	 }
	 if($id != "")
	 {
	?>
	lines.push(new Item('<?php echo $id;?>', '<?php echo $parent_id;?>', '<?php echo $type;?>', '<?php echo $value;?>', '<?php echo $data;?>', '<?php echo $style;?>'));
	
	<?php
	 }
?>

<?php
 }
 ?>
 function addLine(parent_id, type, value, data, style)
 {
	 if(type == 'col')
	 {
		value = prompt("Nhập kích thước (1-12)", "");
		if (value == null || value == "") {
		  return;
		}
		
	 }
	 var id = 0;
	 for(var i=0; i<lines.length; i++)
	 {
		 var item = lines[i];
		 var n = parseInt(item.id);
		 if(n>id)
		 {
			 id = n;
		 }
	 }
	 id = id + 1;

	 var item = new Item(id, parent_id, type, value, data, style);
	 lines.push(item);
	 layout();
 }

 function buildItem(parent_id)
 {
	for(var i=0; i<lines.length; i++)
	{
		var item = lines[i];
		
		if(item.parent_id == parent_id && item.type == 'item')
		{
			
			
			return item.value;
		}
	}
	return "";
 }
 function build(parent_id)
 {
	
	var s = "";
	if(parent_id == "")
	{
		s = '<a href="javascript:addLine(\'\', \'row\', \'\', \'\', \'\', \'\')">+ Thêm dòng</a>';
	}
	for(var i=0; i<lines.length; i++)
	{
		var item = lines[i];
		
		if(item.parent_id == parent_id && item.type != "item")
		{
			
			
			if(item.type == "row")
			{
				s = s + '<div class="row">';
				s = s + '<div class="col-12">';
				s = s + parent_id + "." + item.id;
				s = s + '<a href="javascript:removeLine(\''+ item.id + '\')">- Xóa dòng</a>';
				s = s + '&nbsp;&nbsp;&nbsp;<a href="javascript:addLine(\''+ item.id + '\', \'col\', \'\', \'\', \'\')">+ Thêm cột</a>';
				s = s + '</div>';
				s = s + '</div>';
			}
	
			
			
			if(item.type == "row")
			{
				s = s + '<div id="' + item.id + '" class="row">';
				s = s + build(item.id);
				s = s + '</div>';
			}else if(item.type == "col")
			{
				s = s + '<div  ';
				s = s + ' class="col-' + item.value + '" style="border:1;border-style: solid"';
				s = s + '>';
				s = s + parent_id + "." + item.id;
				s = s + '<a href="javascript:addLine(\'' + item.id + '\', \'row\', \'\', \'\', \'\')">+ Thêm dòng</a>&nbsp;&nbsp;&nbsp;<a href="javascript:removeLine(\''+ item.id + '\')">- Xóa cột</a> &nbsp;&nbsp;&nbsp;<a href="javascript:removeValue(\''+ item.id + '\')">- Clear Value</a>';
				s = s + " w: " + item.value;
				
				s = s + '<div onclick="itemClick('+item.id+')" id="' + item.id + '" style="height:46px; width:100%; border:1;border-style: solid; border-color: coral" ondrop="drop(event)" ondragover="allowDrop(event)">';
				s = s + item.data;
				s= s+ '</div>';
				
				s = s + build(item.id);
				s = s + '</div>';
				
				
			}
			
		}
	}
	return s;
 }
 function layout()
 {
	 var ctr = document.getElementById('contentView'); 
	 ctr.innerHTML = build('');
	 
	 
 }
 function clearLine()
 {
	 lines = new Array();
	 layout();
 }
  function removeValue(id)
 {
	 for(var i=0; i<lines.length; i++)
	{
		if(lines[i].id == id)
		{
			lines[i].data = "";
			break;
		}
	}
	layout();
 }
 function removeLine(id)
 {
	var arr = new Array();
	for(var i=0; i<lines.length; i++)
	{
		if(lines[i].id != id)
		{
			arr.push(lines[i]);
		}
	}
	lines = arr;
	layout();
 }
 function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
  ev.preventDefault();
  var data = ev.dataTransfer.getData("text");
  for(var i=0; i<lines.length; i++)
  {
	  if(lines[i].id == ev.target.id)
	  {
		  lines[i].data = data;
		  break;
	  }
  }
  layout();
}
 
 function saveData()
 {
	var data = '';
	for(var i=0; i<lines.length; i++)
	{
		var item = lines[i];
		if(data != "")
		{
			data += ":";
		}
		var style = item.style.replace(";", "~");
		style = style.replace(":", "|");
		
		data = data + "id=" + item.id + ';parent_id=' + item.parent_id+';type='+ item.type + ';value=' + item.value + ';data=' + item.data.replace(":", "~") + ';style=' + style;
		
		
	}
	
	var _url = '<?php echo URL;?>includes/action.php?ac=saveModuleLineData&id=<?php echo $line_id;?>';	
	_url = _url + '&data=' + encodeURIComponent(data);

	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				alert('Data is saved');
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
	
 }
 var selected_id;
 function itemClick(id)
 {
	 selected_id = id;
	for(var i=0; i<lines.length; i++)
	{
	  if(lines[i].id == id)
	  {
		  document.getElementById('item_id').innerHTML = id;
		  document.getElementById('editData').value = lines[i].data;
		  document.getElementById('editvalue').value = lines[i].value;
		  var value = lines[i].style;
		 
		  document.getElementById('editstyle').value = value;
		  break;
	  }
	 }
 }
 function saveItem()
 {
	 for(var i=0; i<lines.length; i++)
	{
	  if(lines[i].id == selected_id)
	  {
		 
		  lines[i].data = document.getElementById('editData').value;
		  lines[i].value = document.getElementById('editvalue').value ;
		  var value = document.getElementById('editstyle').value;
		  
		  lines[i].style = value;

		  layout();
		  break;
	  }
	 }
 }
 layout();
</script>

</body>
</html>