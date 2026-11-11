<?php

require_once(ABSPATH.'config.php' );


$ac = '';
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
	
?>
<a href="javascript:addLine('')"><img src="<?php echo URL;?>assets/img/add.png"></a>
<div id="lineView"></div>

	
	
	<script>
	function loadNaviLine()
	{
		var _url = '<?php echo URL;?>admin/designer/?ac=lines&module_id=<?php echo $module_id;?>';	
		loadPage('lineView', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	
	function addLine(pid)
	{
		
		var name = prompt("Please enter your name", "");
		if (name == null || name == "") {
		  return;
		}

		var _url = '<?php echo URL;?>admin/designer/?ac=addLine&module_id=<?php echo $module_id;?>&name=' + encodeURIComponent(name);	
		loadPage('contentViewLine', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadNaviLine();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	
	function removeLine(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/designer/?ac=removeLine&id=' + id;	
		loadPage('contentViewLine', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadNaviLine();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	loadNaviLine();
	
	</script>
<?php
}else if($ac == "lines")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	
	
	$sql = "SELECT d1.id, d1.name, d1.publish, d1.data, d1.type, d1.icon, d1.sequence FROM ir_module_line d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND type='app' ORDER BY d1.create_date ASC";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
?>
<div class="row responsive" style="padding-top:4px">
	<table class="table">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th nowrap="nowrap">Name</th>
			<th style="width:30px" nowrap="nowrap">Type</th>
			<th style="width:80px" nowrap="nowrap">Icon</th>
			<th style="width:30px" nowrap="nowrap">Sequence</th>
			<th  nowrap="nowrap">Content</th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<count($result); $j++)
			{
		
		
				$id = $result[$j][0];
				$name = $result[$j][1];
				$publish = $result[$j][2];
				$data = $result[$j][3];
				$type = $result[$j][4];
				$icon = $result[$j][5];
				$sequence = $result[$j][6];
				if($icon == "")
				{
					$icon = "...";
				}
				
				if($sequence == "")
				{
					$sequence = 0;
				}
				
			
			?>
			<tr>
			<td style="width:20px"><?php echo $j + 1; ?></td>
			<td width="100px" style="width:100px"><input style="width:100px" type="text" class="input" value="<?php echo $name; ?>" onblur="dataChanged('ir_module_line','name', this, '<?php echo $id;?>')" maxlength="36" ></td>
			<td width="100px" style="width:100px"><select style="width:100px" class="select" onchange="dataChanged('ir_module_line','type', this, '<?php echo $id;?>')">
			<option value=""></option>
			<option value="app" <?php if($type == "app"){ echo " selected ";}?>>app</option>
			
			</select>
			</td>
			<td><button style="width:32px;height:32px" id="edit<?php echo $id;?>" onclick="selectIcon(this, function(value){this.value=value; dataChanged('ir_module_line','icon', this, '<?php echo $id;?>') })"><i class="<?php echo $icon;?>" id="edit<?php echo $id;?>_icon"></i></button></td>
			<td width="100px" style="width:100px"><input style="width:80px" type="text" class="input" value="<?php echo $sequence; ?>" onblur="dataChanged('ir_module_line','sequence', this, '<?php echo $id;?>')" maxlength="36" ></td>
			
			<td ><a href="javascript:loadLine('<?php echo $id;?>')">Designer</a>
			
			</td>
			<td  style="text-align:center; vertical-align:middle"><a href="javascript:removeLine('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
			
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
<hr>
<div id="designer"></div>
<script>
	function loadLine(id)
	{
		var _url = '<?php echo URL;?>admin/designer/?ac=line&module_id=<?php echo $module_id;?>&id=' + id;	
		loadPage('designer', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
</script>
<?php
}
else if($ac == "line")
{
	$line_id = '';
	if(isset($_REQUEST['id']))
	{
		$line_id = $_REQUEST['id'];
	}
	$data = "";
	$sql = "SELECT data FROM ir_module_line WHERE id='".$line_id."'";
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getArray($msg);
	
	if(count($result)>0)
	{
		
		$data = $result[0][0];
		
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}

$arr = explode('~', $data);

?>

<div class="row">
	<div class="col m1"><a href="javascript:saveData()">Save</a></div>
	<div class="col m1"><a href="javascript:clearLine()">Clear</a></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
	<div class="col m1"></div>
</div>

<table width="100%">
<tr>
<td valign="top">

<div id="contentViewLine" class="container" style="height:400px; width:100%; overflow:auto"></div>
<div class="container">
Value: <span id="item_id"></span> <br><textarea id="editvalue" class="textar" style="width:100%"></textarea><br>
Data: <br><textarea id="editData" class="input" style="width:100%"></textarea><br>
Style: <br><textarea id="editstyle" class="input" style="width:100%"></textarea><br>
<a href="javascript:saveItem()">Save Item</a>
<div id="dataInfo"></div>
</div>
</td>
<td  valign="top" width="200">
<span id="ui=Button,text=Button Name,cmd=Action_Name" draggable="true" ondragstart="drag(event)">Button</span><br>
<span id="ui=ButtonPrimary,text=Button Name,cmd=Action_Name" draggable="true" ondragstart="drag(event)">ButtonPrimary</span><br>
<span id="ui=Label,text=Label Name" draggable="true" ondragstart="drag(event)">Label</span><br>
<span id="ui=View" draggable="true" ondragstart="drag(event)">View</span><br>
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
 var datas = new Array();
 
 function findDataInfo(parent_id)
 {
	 var s = "";
	 for(i =0; i<datas.length; i++)
	 {
		 if(datas[i][1] == parent_id)
		 {
			 if(s != "")
			 {
				 s = s + ", ";
			 }
			 s = s + datas[i][2];
			 var s1 = findDataInfo(datas[i][0]);
			 if(s1 != "")
			 {
				 if(s != "")
				 {
					 s = s + ", ";
				 }
				 s = s + s1;
			 }
		 }
	 }
	return s;
 }
 var lines = new Array();

 <?php

 for($i=0; $i<count($arr); $i++)
 {
	 
	 $item = $appSession->getTool()->split( $arr[$i], '&');
	
	 $id = "";
	 $parent_id = "";
	 $type = "";
	 $value = "";
	 $data = "";
	 $style = "";
	 for($j=0; $j<count($item); $j++)
	 {
		
		 $index = $appSession->getTool()->indexOf($item[$j], '=');
		
		 if($index != -1)
		 {
			 $key = $appSession->getTool()->substring($item[$j], 0, $index);
			 $v = $appSession->getTool()->substring($item[$j], $index + 1);
			
			 if($key == "id")
			 {
				 $id = $v;
			 }else if($key == "parent_id")
			 {
				 $parent_id = $v;
			 }else if($key == "type")
			 {
				 $type = $v;
			 }else if($key == "value")
			 {
				 $value = $v;
			 }else if($key == "data")
			 {
				 $data = $v;
			 }else if($key == "style")
			 {
				 $style = $v;
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
				s = s + '<div class="row" style="border:1;border-style: solid">';
				s = s + '<div class="col m12">';
				s = s + '<a href="javascript:itemClick('+item.id+')">' + parent_id + "." + item.id + '</a>';
				s = s + ' &nbsp;&nbsp;<a href="javascript:removeLine(\''+ item.id + '\')">- Xóa dòng</a>';
				
				s = s + '&nbsp;&nbsp;&nbsp;<a href="javascript:addLine(\''+ item.id + '\', \'col\', \'\', \'\', \'\')"> + Thêm cột</a>';
				s = s + ' &nbsp;&nbsp;<a href="javascript:moveLine(\''+ item.id + '\', ' + i + ')"> Move</a><br>';
				s = s + build(item.id);
				s = s + '</div>';
				s = s + '</div>';
			}else if(item.type == "col")
			{
				s = s + '<div  ';
				s = s + ' class="col m' + item.value + '" style="border:1;border-style: solid"';
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
	 var ctr = document.getElementById('contentViewLine'); 
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
  function moveLine(id, index)
 {
	 var move_index = prompt("Please enter your index", index);

	if (move_index == null || move_index == "") {
		return;
	}
	var line = lines[index];
	var arr = new Array();
	for(var i=0; i<lines.length; i++)
	{
		if(i == move_index)
		{
			arr.push(line);
		}
		if(i != index)
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
			data += "~";
		}
		
	
		data = data + "id=" + item.id + '&parent_id=' + item.parent_id+'&type='+ item.type + '&value=' + item.value + '&data=' + item.data + '&style=' + item.style;
		
		
	}

	var _url = '<?php echo URL;?>admin/designer/?ac=saveModuleLineData&id=<?php echo $line_id;?>';	
	_url = _url + '&data=' + encodeURIComponent(data);

	loadPage('contentViewLine', _url, function(status, message)
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
		  document.getElementById('item_id').innerHTML = id + "," + lines[i].type;
		  document.getElementById('editData').value = lines[i].data;
		  document.getElementById('editvalue').value = lines[i].value;
		  var value = lines[i].style;
		 
		  document.getElementById('editstyle').value = value;
		  var data = document.getElementById('editData').value;
		  var index = data.indexOf(",");
		  
		  var info = "";
		  if(index != -1)
		  {
				data = data.substring(index + 1);
				data = findDataInfo(data);
				document.getElementById('dataInfo').innerHTML = data;
		  }
		  
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
<?php
}else if($ac == "addLine")
{
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	
	$sql = "INSERT INTO ir_module_line(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", name";
	$sql = $sql.", module_id";
	$sql = $sql.", type";
	$sql = $sql.", company_id";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", 0";
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.", 'app'";
	$sql = $sql.", '".$appSession->getConfig()->getProperty("company_id")."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "saveModuleLineData")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$sql = "UPDATE ir_module_line SET write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", data='".str_replace("'", "''", $data)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeLine")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_module_line SET status=1, write_date=NOW(), write_uid='".$appSession->getUserInfo()->getId()."'";

	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>