<?php


$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$line_id = "";
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
	$id = "";
	$arr_params = explode("&", $params);
	for($i =0; $i<count($arr_params); $i++)
	{
		$arr = explode("=", $arr_params[$i]);
		if(count($arr)>1)
		{
			if($arr[0] == "rel_id")
			{
				$id = $arr[1];
				
			}
		}
	}
	
	$sql = "SELECT d1.id, d1.name, d1.publish, d1.sequence, d1.grouped, d1.type, d1.rel_id, d2.name AS rel_name FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d1.id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	
	if($numrows>0)
	{
	
		?>
		
	

		<div class="row">
				<div class="col m12">
					<div id="pnRel"></div>
				</div>
			</div>
		</div>
		<br>
		<div class="row">
				<div class="col m12">
					<div id="pnView"></div>
				</div>
			</div>
		</div>
		<br>
		<div class="row">
				<div class="col m12">
					<div id="pnStatus"></div>
				</div>
			</div>
		</div>
		<br>
		<br>
		<div class="row">
				<div class="col m12">
					<div id="pnParams"></div>
				</div>
			</div>
		</div>
		
		<script>
			var rel_parent_id = '';
			function loadRel(parent_id)
			{
				rel_parent_id = parent_id;
				var _url = '<?php echo URL;?>admin/developer/module/?ac=model&module_id=<?php echo $id;?>&parent_id=' + parent_id;
				
				loadPage('pnRel', _url, function(status, message)
				{
					if(status== 0)
					{
						
					}
					
				}, false);
			}
			loadRel('');
			
			function loadNaviLine()
			{
				var _url = '<?php echo URL;?>admin/developer/module/?ac=navi_list&module_id=<?php echo $id;?>';
				
				loadPage('pnView', _url, function(status, message)
				{
					if(status== 0)
					{
						
					}
					
				}, false);
			}
			loadNaviLine();
			
			function loadStatus()
			{
				var _url = '<?php echo URL;?>admin/developer/module/?ac=status_list&module_id=<?php echo $id;?>';
				
				loadPage('pnStatus', _url, function(status, message)
				{
					if(status== 0)
					{
						
					}
					
				}, false);
			}
			l//oadStatus();
			
			function relModule()
			{
				var _url = '<?php echo URL;?>admin/developer/module/?ac=module_list1&func=relSelected';	
				openPopup(_url);
			}
			function relSelected(id, name)
			{
				closePopup();
				document.getElementById('editrel_id').value = id;
				document.getElementById('editrel_name').value = name;
			}
			function saveMoule(id)
			{
				var ctr = document.getElementById('editname');
				if(ctr.value == '')
				{
					alert('Name is not empty');
					ctr.focus();
					return;
				}
				var name = ctr.value;
				ctr = document.getElementById('editsequence');
				var sequence = ctr.value;
				ctr = document.getElementById('editpublish');
				var publish = 0;
				if(ctr.checked)
				{
					publish = 1;
				}
			
				ctr = document.getElementById('editgrouped');
				var grouped = 0;
				if(ctr.checked)
				{
					grouped = 1;
				}
				
				if(sequence == "")
				{
					sequence= "0";
				}
				var type =  document.getElementById('edittype').value;
				var rel_id =  document.getElementById('editrel_id').value;
				
				var _url = '<?php echo URL;?>admin/developer/module/?ac=save&id=' + id;	
				_url = _url + '&name=' + encodeURIComponent(name);
				_url = _url + '&publish=' + encodeURIComponent(publish);
				_url = _url + '&grouped=' + encodeURIComponent(grouped);
				_url = _url + '&sequence=' + encodeURIComponent(sequence);
				_url = _url + '&type=' + encodeURIComponent(type);
				_url = _url + '&rel_id=' + rel_id;
				
				loadPage('contentView', _url, function(status, message)
				{
					if(status== 0)
					{
						if(message == "OK")
						{
							alert("Data is saved");
						}else
						{
							alert(message);
						}
					}
					
				}, true);
			}
			function loadParams()
			{
				var _url = '<?php echo URL;?>addons/data/index.php?ac=params&id=<?php echo $id;?>';
				
				
				loadPage('pnParams', _url, function(status, message)
				{
					if(status== 0)
					{
						
					}
					
				}, false);
			}
			//loadParams();
		</script>

		<?php
			
	}
}else if($ac == "navi_list")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$sql = "SELECT d1.id, d1.name, d1.data, d1.type FROM ir_module_line d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
?>
View: <a href="javascript:addLine()"><img src="<?php echo URL;?>assets/img/add.png"></a>
	<div class="row">
		<div class="col container m12">
			
		</div>
	</div>
	<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th style="width:30px" nowrap="nowrap"></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
			
				$id = $row[0];
				$name = $row[1];
				$data = $row[2];
				$type = $row[3];
				
			
			?>
			<tr>
			<td style="width:20px"><?php echo $j + 1; ?></td>
			<td width="100px" style="width:100px"><input style="width:100px" type="text" class="input" value="<?php echo $name; ?>" onblur="dataChanged('ir_module_line','name', this, '<?php echo $id;?>')" maxlength="36" ></td>
			<td width="100px" style="width:100px"><select style="width:100px" class="select" onchange="dataChanged('ir_module_line','type', this, '<?php echo $id;?>')">
			<option value=""></option>
			<option value="table" <?php if($type == "table"){ echo " selected ";}?>>table</option>
			<option value="form" <?php if($type == "form"){ echo " selected ";}?>>form</option>
		
			</select>
			</td>
			<td ><a href="<?php echo URL;?>admin/developer/module/designer?id=<?php echo $id;?>&module_id=<?php echo $module_id;?>" target="_blank">Designer</a>
			<?php echo htmlentities($data);?>
			</td>
			<td  style="text-align:center; vertical-align:middle"><a href="javascript:removeLine('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
			
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
	<script>
	function addLine(pid)
	{
		
		var name = prompt("Please enter your name", "");
		if (name == null || name == "") {
		  return;
		}

		var _url = '<?php echo URL;?>admin/developer/module/?ac=addLine&module_id=<?php echo $module_id;?>&name=' + encodeURIComponent(name);	
		loadPage('contentView', _url, function(status, message)
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
		var _url = '<?php echo URL;?>admin/developer/module/?ac=removeLine&id=' + id;	
		loadPage('contentView', _url, function(status, message)
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
	</script>
<?php
	
}else if($ac == "model")
{
	function buildModuleTree($lines, $parent_id, $root_id)
	{
		
		$count = 0;
		$line = 0;
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
				$line += 1;
				$id = $lines[$j][0];
				$name = $lines[$j][2];
				$model_id = $lines[$j][3];
				$rename = $lines[$j][4];
				$data = $lines[$j][5];
				if($parent_id == "")
				{
					$root_id = $id;
				}
		?>
		<tr>
			<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
			<td style="padding-left:5px; padding-top:5px"><a href="javascript:selModel('<?php echo $root_id;?>', '<?php echo $id;?>')"><?php if($rename != ""){ echo $rename." (".$name.")"; }else{ echo $name;}?> <?php if($count>0){ echo $data;}?></a> <a href="javascript:addModel('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a> <a href="javascript:removeModel('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a> <a href="javascript:addAttribute('<?php echo $id;?>', '<?php echo $model_id;?>')"><img src="<?php echo URL;?>assets/img/plus_1.png"></a> <a href="javascript:loadRel('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/menu_right.png"></a></td>
		  </tr>
		  
		  <tr>
			<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php }?>></td>
			<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildModuleTree($lines, $id, $root_id);?></table></td>
		</tr>
		<?php
		
			}
		}
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$sql = "SELECT d1.id, d1.parent_id, d2.name, d1.model_id, d3.name AS rename, d4.data FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module_model_attribute d4 ON(d1.id = d4.module_model_id) WHERE d1.status =0 AND d1.module_id='".$module_id."' ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);


	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$models = [];
	$arrIndex = 0;
	for($i =0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$arr[2] = $row[2];
		$arr[3] = $row[3];
		$arr[4] = $row[4];
		$arr[5] = $row[5];
		$models[$arrIndex] = $arr;
		$arrIndex = $arrIndex + 1;
	}
	
	
		
?>
	<a href="javascript:lang('<?php echo $module_id;?>')">Language</a><br>
	Model: <a href="javascript:addModel('')"><img src="<?php echo URL;?>assets/img/add.png"></a>
	<div class="row">
		<div class="col container m12">
			
		</div>
	</div>
	<div class="row">
		<div class="col m4" style=" max-height:400px; overflow:auto">
			<table>
			<?php echo buildModuleTree($models, $parent_id, "");?>
			</table>
		</div>
		<div class="col m8">
			<div id="pnModel"></div>
		</div>
	</div>
	
	<script>
	var parent_id = '';
	function addModel(pid)
	{
		parent_id = pid;
		var _url = '<?php echo URL;?>admin/developer/module/?ac=model_list';
		openPopup(_url);
	}
	function doAddModel(id)
	{
		var _url = '<?php echo URL;?>admin/developer/module/?ac=addModel&module_id=<?php echo $module_id;?>&parent_id='+ parent_id+'&model_id=' + id;	
	
		closePopup();
		loadPage('pnModel', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadRel('<?php echo $parent_id;?>');
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function selModel(root_id, id)
	{
		
		var _url = '<?php echo URL;?>admin/developer/module/?ac=modeler&id=' + id;	
		_url = _url + '&root_id=' + root_id
		_url = _url + '&module_id=<?php echo $module_id;?>';
		loadPage('pnModel', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
	}
	function saveData(id, data)
	{
		var _url = '<?php echo URL;?>admin/developer/module/?ac=saveModuleModelAttribute&module_id=<?php echo $module_id;?>&module_model_id=' + id ;	
		_url = _url + "&data=" + encodeURIComponent(data);
		
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					//closePopup();
					//loadRel();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	
	function removeModel(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/module/?ac=removeModel&id=' + id;	
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadRel('<?php echo $parent_id;?>');
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function editModelAttribute(module_id, module_model_id, model_attribute_id, type)
	{
			
		var _url = '<?php echo URL;?>admin/developer/module/?ac=editModelAttribute';
		_url = _url + '&module_id=' + module_id;
		_url = _url + '&model_attribute_id=' + model_attribute_id;
		_url = _url + '&module_model_id=' + module_model_id;
		_url = _url + '&type=' + type;
		openPopup(_url);
	}
	function addAttribute(id, model_id)
	{
		parent_id = id;
		var _url = '<?php echo URL;?>admin/developer/module/?ac=attribute_list&model_id=' + model_id;
		openPopup(_url);
	}
	function doAddAttributeModel(id)
	{
		var _url = '<?php echo URL;?>admin/developer/module/?ac=doAddAttributeModel&module_id=<?php echo $module_id;?>&parent_id='+ parent_id+'&model_attribute_id=' + id;	
	
		closePopup();
		loadPage('pnModel', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadRel('<?php echo $parent_id;?>');
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	
	</script>
<?php
}else if($ac == "modeler")
{
	$module_model_id = '';
	if(isset($_REQUEST['id']))
	{
		$module_model_id = $_REQUEST['id'];
	}
	$root_id = '';
	if(isset($_REQUEST['root_id']))
	{
		$root_id = $_REQUEST['root_id'];
	}
	
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	
	
	$sql = "SELECT d3.name, d1.data , d1.module_model_id FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_model d3 ON(d2.model_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d2.module_id='".$module_id."' AND (d3.name ='data' OR d1.module_model_id='".$module_model_id."') AND d2.group_id='".$root_id."'";
	
	
	$datas = [];
	$data_id = "";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	for($m =0; $m<$numrows; $m++)
	{
		$row = $result[$m];
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$arr[2] = $row[2];
		
		$datas[$m] = $arr;
		if($row[0] == "data")
		{
			$data_id =  $row[1];
		}
	}
	$data_value = "";
	for($i = 0; $i<count($datas); $i++)
	{
		if($datas[$i][2] == $module_model_id)
		{
			$data_value = $datas[$i][1];
		}
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
	$params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[0];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		
		
		$params[$m] = $arr;
	}
	
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.data, d1.relationship FROM ir_data_line d1 WHERE data_id ='".$data_id."' AND d1.status =0";


					
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
	}
	$conditions = $appSession->getTier()->condition_array($condition);
	$data_sql = $appSession->getTier()->buildSQL($lines, "", $params, $conditions);
	$data_sql = str_replace("{rel_id}", "''", $data_sql);
	$data_sql = str_replace("{company}", "''", $data_sql);
	$data_sql = str_replace("{child_company}", "''", $data_sql);
	$data_sql = str_replace("{parent_company}", "''", $data_sql);
	$data_sql = str_replace("{root_company}", "''", $data_sql);
	$data_sql = str_replace("{companies}", "''", $data_sql);
	
	if($condition == "")
	{
		$data_sql = $data_sql." WHERE 1=0";
	}else{
		$data_sql = $data_sql." AND 1=0";
	}
	
	if($group_by != "")
	{
		$data_sql= $data_sql. " GROUP BY ".$group_by;
	}
	if($order_by != "")
	{
		$data_sql= $data_sql. " ORDER BY ".$order_by;
	}
	
	
	$name = "";
	$type = "";
	$parent_name = "";
	
	$sql = "SELECT  d2.name, d3.name AS category_name, d5.name AS parent_name FROM ir_module_model d1 LEFT OUTER JOIN ir_model_attribute d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN ir_model d3 ON(d2.category_id = d3.id) LEFT OUTER JOIN ir_module_model d4 ON(d1.parent_id = d4.id) LEFT OUTER JOIN ir_model d5 ON(d4.model_id = d5.id) WHERE d1.status =0 AND d1.id ='".$module_model_id."' ORDER BY d2.name ASC";
	
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	if($numrows>0)
	{
		$row = $result[0];
		$name = $row[0];
		$type = $row[1];
		$parent_name = $row[2];
	}
	
	
	$sql = "SELECT d1.id, d2.name, d3.name AS category_name, d5.name AS parent_name FROM ir_module_model d1 LEFT OUTER JOIN ir_model_attribute d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN ir_model d3 ON(d2.category_id = d3.id) LEFT OUTER JOIN ir_module_model d4 ON(d1.parent_id = d4.id) LEFT OUTER JOIN ir_model d5 ON(d4.model_id = d5.id) WHERE d1.status =0 AND d1.parent_id ='".$module_model_id."' ORDER BY d2.create_date ASC";
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	
	
	?>
	<?php echo $name;?> (<?php echo $type;?>):
	<?php if($type == 'data')
	{
		$sql = "SELECT id, name FROM ir_data WHERE status =0 ORDER BY name ASC";
		$msg->add("query", $sql);
		$result_data = $appSession->getTier()->getArray($msg);
		$numrows_data = count($result_data);
	?>
	<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value)">
		<option value=""></option>
		<?php
		for($k=0; $k<$numrows_data; $k++)
		{
			$row_k = $result_data[$k];
			$_id = $row_k[0];
			$_name = $row_k[1];
		?>
		<option value="<?php echo $_id;?>" <?php if($data_value == $_id){ echo " selected "; }?>><?php echo $_name;?></option>
		<?php
		}
		?>
		
	</select>
	<?php
	}else if($type == "action")
	{
		$sql = "SELECT id, name FROM ir_action WHERE status =0 ORDER BY name ASC";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		$numrows1 = count($result1);
	?>
		<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value);">
		<option value=""></option>
		<?php
		for($n =0; $n<$numrows1; $n++)
		{
			$row1 = $result1[$n];
			$_id = $row1[0];
			$_name = $row1[1];
		?>
		<option value="<?php echo $_id;?>" <?php if($_id == $data_value){ echo " selected "; } ?>><?php echo $_name;?></option>
		<?php
		}
		?>
	</select>
	<?php
	}else if($parent_name == "resource")
	{
		$sql = "SELECT id, name FROM res_resource WHERE status =0 AND rel_id ='' ORDER BY name ASC";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		$numrows1 = count($result1);
		?>
		<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value);">
		<option value=""></option>
		<?php
		for($n =0; $n<$numrows1; $n++)
		{
			$row1 = $result1[$n];
			$_id = $row1[0];
			$_name = $row1[1];
		?>
		<option value="<?php echo $_id;?>" <?php if($_id == $data_value){ echo " selected "; } ?>><?php echo $_name;?></option>
		<?php
		}
		?>
	</select>
	
		<?php
	}
	else{?>
	<input type="text" class="input" onblur="saveData('<?php echo $module_model_id;?>', this.value)" value="<?php echo $data_value;?>">
	<?php }?>
	<?php
	if($numrows>0)
	{
	?>
	<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th nowrap="nowrap"style="width:100px"><?php echo $appSession->getLang()->find('Name');?></th>
	
			<th  nowrap="nowrap"></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				$module_model_id = $row[0];
				$name = $row[1];
				$category_name = $row[2];
				$parent_name = $row[3];
				
				$sql = "SELECT data FROM ir_module_model_attribute WHERE module_model_id='".$module_model_id."'";
				$msg->add("query", $sql);
				$result_data_value = $appSession->getTier()->getArray($msg);
				$numrows_data_value = count($result_data_value);
				$data_value = "";
				
				if($numrows_data_value>0)
				{
					$row1 = $result_data_value[0];
					$data_value = $row1[0];
				}
				
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			<td><?php echo $name;?> (<?php echo $category_name;?>)</td>
			<td>
			<?php 
			
			
			if($parent_name == "datasource" && $category_name == "data")
			{
				$sql = "SELECT id, name FROM ir_data WHERE status =0 ORDER BY name ASC";
				$msg->add("query", $sql);
				$result_data = $appSession->getTier()->getArray($msg);
				$numrows_data = count($result_data);
				
				
				
				
			?>
			<select class="select" onchange= "saveData('<?php echo $model_attribute_id;?>', this.value)">
				<option value=""></option>
				<?php
				for($k=0; $k<$numrows_data; $k++)
				{
					$row_k = $result_data[$k];
					$_id = $row_k[0];
					$_name = $row_k[1];
				?>
				<option value="<?php echo $_id;?>" <?php if($data_value == $_id){ echo " selected "; }?>><?php echo $_name;?></option>
				<?php
				}
				?>
				
			</select>
			<?php
			}else if($parent_name == "column" && $name == "name")
			{
				
				
				if($data_sql != "")
				{
					$msg->add("query", $sql);
					$result_columns = $appSession->getTier()->getArray($msg);
					$fields = pg_num_fields($result_columns);
				
			?>
	
			<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value);">
				<option value=""></option>
				<?php
				for($n =0; $n<$fields; $n++)
				{
					$fieldname = pg_field_name($result_columns, $n);
					
				?>
				<option value="<?php echo $fieldname;?>" <?php if($fieldname == $data_value){ echo " selected "; } ?>><?php echo str_replace("__", ".", $fieldname);?></option>
				<?php
				}
				?>
			</select>
			<?php
					
				}
			}else if($parent_name == "column" && $name == "type")
			{
				$fields= [["",""], ["date", "date"], ["datetime", "datetime"], ["time", "time"], ["textarea", "textarea"], ["color", "color"], ["hidden", "hidden"],["multiple", "multiple"],["image", "image"], ["file", "file"], ["files", "files"], ["checkbox", "checkbox"], ["integer", "interger"], ["float", "float"] , ["currency", "currency"], ["percent", "percent"], ["password", "password"],["editor", "editor"] , ["language", "language"], ["status", "status"], ["daterange", "daterange"]];
				
				$sql = "SELECT d1.id, d2.name, d3.data FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_module_model_attribute d3 ON(d1.id = d3.module_model_id) WHERE d1.module_id='".$module_id."' AND d1.parent_id='' AND d1.status =0 ORDER BY d1.create_date ASC";
				$msg->add("query", $sql);
				$result1 = $appSession->getTier()->getArray($msg);
				$numrows1 = count($result1);
				for($i=0; $i<$numrows1; $i++)
				{
					$row1 = $result1[$i];
					$fields[count($fields)] = [$row1[0], $row1[1], $row1[2]];
				}
				?>
				<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value);">
				<?php
				for($n =0; $n<count($fields); $n++)
				{
					$_id = $fields[$n][0];
					$_name = $fields[$n][1];
					if($fields[$n][2] != "")
					{
						$_name = $_name." (" . $fields[$n][2].")";
					}
					
					
				?>
				<option value="<?php echo $_id;?>" <?php if($_id == $data_value){ echo " selected "; } ?>><?php echo $_name;?></option>
				<?php
				}
				?>
			</select>
				<?php
			}else if($name == "action")
			{
				
				
				$sql = "SELECT id, name FROM ir_action WHERE status =0 ORDER BY name ASC";
				$msg->add("query", $sql);
				$result1 = $appSession->getTier()->getArray($msg);
				$numrows1 = count($result1);
				
			?>
	
			<select class="select" onchange= "saveData('<?php echo $module_model_id;?>', this.value);">
				<option value=""></option>
				<?php
				for($n =0; $n<$numrows1; $n++)
				{
					$row1 = $result1[$n];
					$_id = $row1[0];
					$_name = $row1[1];
				?>
				<option value="<?php echo $_id;?>" <?php if($_id == $data_value){ echo " selected "; } ?>><?php echo $_name;?></option>
				<?php
				}
				?>
			</select>
			<?php
					
			}else if($parent_name == "column" && $name == "required")
			{
			?>
			<textarea type="text" class="input" onblur="saveData('<?php echo $module_model_id;?>', this.value)"><?php echo htmlentities($data_value);?></textarea>
			<?php
			}
			else if($category_name == "boolean")
			{
			
			?>
				
			<input type="checkbox" class="checkbox" onclick="if(this.checked){this.value = 1}else {this.value = 0};saveData('<?php echo $module_model_id;?>', this.value)" <?php if($data_value == "1"){ echo " checked " ; }?> value="<?php echo $data_value;?>">
				<?php
			}
			else 
			{
			?>
			<input type="text" class="input" onblur="saveData('<?php echo $module_model_id;?>', this.value)" value="<?php echo htmlentities($data_value);?>">
			<?php
			}
			?>
			
			</td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
</div>
<?php
	}
	?>
<?php
}else if($ac == "attribute_list")
{
	$model_id = '';
	if(isset($_REQUEST['model_id']))
	{
		$model_id = $_REQUEST['model_id'];
	}
	
	$sql = "SELECT d1.id, d1.name, d2.name AS type FROM ir_model_attribute d1 LEFT OUTER JOIN ir_model d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND d1.model_id='".$model_id."' ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
?>
<div class="row responsive" style="padding-top:4px;overflow-x:auto;">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Type');?></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				$id = $row[0];
				$name = $row[1];
				$type = $row[2];
				
		
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			
			<td style="width:40px" style="text-align:center; vertical-align:middle"><a href="javascript:doAddAttributeModel('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
			<td><?php echo $name; ?></td>
			<td><?php echo $type; ?></td>
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>

<?php
}
else if($ac == "model_list")
{
	$sql = "SELECT id, name FROM ir_model WHERE status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
?>
<div class="row responsive" style="padding-top:4px;overflow-x:auto;">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				$id = $row[0];
				$name = $row[1];
				
				
		
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			
			<td style="width:40px" style="text-align:center; vertical-align:middle"><a href="javascript:doAddModel('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
			<td><?php echo $name; ?></td>
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
<?php
}else if($ac == "doAddAttributeModel")
{
	function createModuleModel($appSession, $module_id, $model_id, $parent_id, $group_id)
	{
		$sql = "SELECT id, category_id FROM ir_model_attribute WHERE model_id='".$model_id."' AND status =0 AND include = 1 ORDER BY create_date ASC";
		$msg = $appSession->getTier()->createMessage();
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
		$msg->add("query", $sql);


		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		for($j =0; $j<$numrows; $j++)
		{
			$row = $result[$j];
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO ir_module_model(";
			$sql = $sql."id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", status";
			$sql = $sql.", model_id";
			$sql = $sql.", module_id";
			$sql = $sql.", parent_id";
			$sql = $sql.", rel_id";
			$sql = $sql.", group_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", NOW()";
			$sql = $sql.", NOW()";
			$sql = $sql.", 0";
			$sql = $sql.", '".$row[1]."'";
			$sql = $sql.", '".$module_id."'";
			$sql = $sql.", '".$parent_id."'";
			$sql = $sql.", '".$row[0]."'";
			$sql = $sql.", '".$group_id."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$rs = $appSession->getTier()->exec($msg);
			createModuleModel($appSession, $module_id, $row[1], $id, $group_id);
		}
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$model_attribute_id = "";
	if(isset($_REQUEST['model_attribute_id']))
	{
		$model_attribute_id = $_REQUEST['model_attribute_id'];
	}

	$group_id =  $parent_id;
	while(true)
	{
		$sql = "SELECT parent_id FROM ir_module_model WHERE id='".$group_id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		
		if($numrows>0)
		{
			$row = $result[0];
			if($row[0] != "")
			{
				$group_id = $row[0];
			}else{
				break;
			}
		}else{
			break;
		}
		
	}
	$sql = "SELECT id, category_id FROM ir_model_attribute WHERE id='".$model_attribute_id."' AND status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	for($j =0; $j<$numrows; $j++)
	{
		$row = $result[$j];
		$sql = "SELECT id FROM ir_module_model WHERE parent_id='".$parent_id."' AND module_id='".$module_id."' AND rel_id='".$row[0]."'";
		$msg->add("query", $sql);
		$result_check = $appSession->getTier()->getArray($msg);
		$numrows_check = count($result_check);	
		if($numrows_check>0)
		{
			$row = $result_check[0];
			$sql = "UPDATE ir_module_model SET status =0, write_date=NOW() where id='".$row[0]."' OR parent_id='".$row[0]."'";
			$msg->add("query", $sql);
			$r =$appSession->getTier()->exec($msg);
			
		}else
		{
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO ir_module_model(";
			$sql = $sql."id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", status";
			$sql = $sql.", model_id";
			$sql = $sql.", module_id";
			$sql = $sql.", parent_id";
			$sql = $sql.", rel_id";
			$sql = $sql.", group_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", NOW()";
			$sql = $sql.", NOW()";
			$sql = $sql.", 0";
			$sql = $sql.", '".$row[1]."'";
			$sql = $sql.", '".$module_id."'";
			$sql = $sql.", '".$parent_id."'";
			$sql = $sql.", '".$row[0]."'";
			$sql = $sql.", '".$group_id."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$r =$appSession->getTier()->exec($msg);
			
			createModuleModel($appSession, $module_id, $row[1], $id, $group_id);
		}
	
		
	}

	echo "OK";
}
else if($ac == "addModel")
{
	function createModuleModel($appSession, $module_id, $model_id, $parent_id, $group_id)
	{
		$sql = "SELECT id, category_id FROM ir_model_attribute WHERE model_id='".$model_id."' AND status =0 AND include=1 ORDER BY create_date ASC";
		$msg = $appSession->getTier()->createMessage();
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
		$msg->add("query", $sql);
	
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		for($j =0; $j<$numrows; $j++)
		{
			$row = $result[$j];
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO ir_module_model(";
			$sql = $sql."id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", status";
			$sql = $sql.", model_id";
			$sql = $sql.", module_id";
			$sql = $sql.", parent_id";
			$sql = $sql.", rel_id";
			$sql = $sql.", group_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", NOW()";
			$sql = $sql.", NOW()";
			$sql = $sql.", 0";
			$sql = $sql.", '".$row[1]."'";
			$sql = $sql.", '".$module_id."'";
			$sql = $sql.", '".$parent_id."'";
			$sql = $sql.", '".$row[0]."'";
			$sql = $sql.", '".$group_id."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$rs = $appSession->getTier()->exec($msg);
			createModuleModel($appSession, $module_id, $row[1], $id, $group_id);
		}
	}
	$model_id = '';
	if(isset($_REQUEST['model_id']))
	{
		$model_id = $_REQUEST['model_id'];
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	
	$id = $appSession->getTool()->getId();
	$group_id =  $parent_id;
	while(true)
	{
		$sql = "SELECT parent_id FROM ir_module_model WHERE id='".$group_id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		
		if($numrows>0)
		{
			$row = $result[0];
			if($row[0] != "")
			{
				$group_id = $row[0];
			}else{
				break;
			}
		}else{
			break;
		}
		
	}
	if($group_id == "")
	{
		$group_id = $id;
	}
	$sql = "INSERT INTO ir_module_model(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", model_id";
	$sql = $sql.", module_id";
	$sql = $sql.", parent_id";
	$sql = $sql.", rel_id";
	$sql = $sql.", group_id";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", 0";
	$sql = $sql.", '".$model_id."'";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.", '".$parent_id."'";
	$sql = $sql.", '".$model_id."'";
	$sql = $sql.", '".$group_id."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	
	$rs =$appSession->getTier()->exec($msg);
	createModuleModel($appSession, $module_id, $model_id, $id, $group_id);
	
	echo "OK";
}else if($ac == "removeModel")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_module_model SET status =1";
	$sql = $sql.", write_date=NOW()";
	$sql = $sql." WHERE (id='".$id."' OR parent_id='".$id."')";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
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
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", 0";
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.")";
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
	$sql = "UPDATE ir_module_line SET status =1";
	$sql = $sql.", write_date=NOW()";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "editModelAttribute")
{
	
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$model_attribute_id = "";
	if(isset($_REQUEST['model_attribute_id']))
	{
		$model_attribute_id = $_REQUEST['model_attribute_id'];
	}
	$module_model_id = "";
	if(isset($_REQUEST['module_model_id']))
	{
		$module_model_id = $_REQUEST['module_model_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	echo "Type: ".$type;
	?>
	<?php
	if($type == "data")
	{
		$sql = "SELECT id, name FROM ir_data WHERE status =0";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
	?>
	
	<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
		  </tr>
		</thead>
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				$id = $row[0];
				$name = $row[1];
				
				
				
		
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			
			<td style="width:40px" style="text-align:center; vertical-align:middle"><a href="javascript:saveData('<?php echo $id; ?>', '<?php echo $name; ?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
			<td><?php echo $name; ?></td>
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
	<script>
		
	</script>
	<?php
	}else
	{
	
		$sql = "SELECT id, name FROM ir_model WHERE status =0 ORDER BY name ASC";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		
		?>
		
		<div class="row responsive" style="padding-top:4px">
			<table class="table-all bordered">
				<thead>
				  <tr>
					<th style="width:30px"></th>
					<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
				  </tr>
				</thead>
				<tbody>
				  
					<?php
					for($j =0; $j<$numrows; $j++)
					{
						$row = $result[$j];
						$id = $row[0];
						$name = $row[1];
			
					?>
					<tr>
					<td style="width:40px" style="text-align:center; vertical-align:middle"><a href="javascript:saveData('<?php echo $id; ?>', '<?php echo $name; ?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
					<td nowrap="nowrap"><?php echo $name; ?></td>
					
					</tr>
				 <?php
					}
					?>
				</tbody>
			</table>
		
		<script>
		
	</script>
		<?php
	}
}else if($ac == "saveModuleModelAttribute")
{

	$module_model_id = "";
	if(isset($_REQUEST['module_model_id']))
	{
		$module_model_id = $_REQUEST['module_model_id'];
	}
	
	
	
	$data = "";
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$sql = "SELECT id,data FROM ir_module_model_attribute WHERE module_model_id='".$module_model_id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$id = "";
	if($numrows>0)
	{
		$row = $result[0];
		$id = $row[0];
		
	}else
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO ir_module_model_attribute(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", module_model_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", 0";
		$sql = $sql.", '".$module_model_id."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}
	$sql = "UPDATE ir_module_model_attribute SET status =0, data='".str_replace("'", "''", $data)."'";
	$sql = $sql.", write_date=NOW()";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "langs")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$pageIndex = 1;
	if($rel_id == "ROOT")
	{
		$pageIndex = 0;
	}
	$sql = "SELECT id, name FROM res_lang WHERE status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	$numrows = count($result);	
	?>
	<div class="row">
		<div class="col m2">
			  <label for="edittype" class="label">Language:</label>
			</div>
		<div class="col m6">
			<select class="select" id="editlang<?php echo $pageIndex;?>">
				<?php
				for($i =0; $i<$numrows; $i++)
				{
					$row = $result[$i];
				?>
				<option value="<?php echo $row[0];?>"><?php echo $row[1];?></option>
				<?php
				}
				?>
			  </select>
		</div>
		<div class="col m4">
		<a class="button" href="javascript:viewLang<?php echo $pageIndex;?>()">View</a>
		<a class="button" href="javascript:cloneLine<?php echo $pageIndex;?>()">Clone</a>
		<a class="button" href="javascript:viewLangContent<?php echo $pageIndex;?>()">Content</a>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col m12">
			<div id="pnLang<?php echo $pageIndex;?>"></div>
		</div>
	</div>
	<script>
		var lang_id = '';
		function viewLang<?php echo $pageIndex;?>()
		{
			var ctr = document.getElementById('editlang<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("select lang");
				ctr.focus();
				return;
			}
			lang_id = ctr.value;
			
			var _url = '<?php echo URL;?>admin/developer/module/?ac=lang_line&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id + '&pageIndex=<?php echo $pageIndex;?>';
			showpage('pnLang<?php echo $pageIndex;?>', _url);
		}
		function viewLangContent<?php echo $pageIndex;?>()
		{
			var ctr = document.getElementById('editlang<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("select lang");
				ctr.focus();
				return;
			}
			lang_id = ctr.value;
			
			var _url = '<?php echo URL;?>admin/developer/module/?ac=lang_line_content&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id + '&pageIndex=<?php echo $pageIndex;?>';
			showpage('pnLang<?php echo $pageIndex;?>', _url);
		}
		
		
		function delLangLine<?php echo $pageIndex;?>(id)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/developer/module/?ac=delLangLine';
			_url = _url + '&id=' + id;
		
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function addLangLine<?php echo $pageIndex;?>(lang_id)
		{
			var ctr = document.getElementById('editname<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("Please input name");
				ctr.focus();
				return;
			}
			var name = ctr.value;
			
			ctr = document.getElementById('editdescription<?php echo $pageIndex;?>');
			
			var description = ctr.value;
			var _url = '<?php echo URL;?>admin/developer/module/?ac=addLangLine&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id+ '&name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(description);
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function cloneLine<?php echo $pageIndex;?>()
		{
			if(lang_id == '')
			{
				alert('Please select view before clone');
			}
			var ctr = document.getElementById('editlang<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("select lang");
				ctr.focus();
				return;
			}
			var to_lang_id = ctr.value;
			
			var result = confirm("Want to clone?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/developer/module/?ac=cloneLine&rel_id=<?php echo $rel_id;?>';
			_url = _url + '&from_lang_id=' + lang_id;
			_url = _url + '&lang_id=' + to_lang_id;
		
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
	</script>
	<?php
}else if($ac == "lang_line")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$pageIndex = "";
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	
	$sql = "SELECT id, name, description FROM res_lang_line WHERE lang_id='".$lang_id."' AND rel_id='".$rel_id."' AND status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	
?>
<div class="responsive">
		
		<table class="table-all bordered">
			<thead>
				<tr>
					<th width="200">Name</th>
					<th >Description</th>
					<th width="20">#</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($i=0; $i<$numrows; $i++)
				{
					$row = $result[$i];
					$id = $row[0];
					$name = $row[1];
					$description = $row[2];
					
				?>
				<tr>
				
					<td><input type="text" onblur="dataChanged('res_lang_line', 'name', this, '<?php echo $id;?>', function(status, message){})" class="input" maxlength="100" value="<?php echo $name;?>"></td>
					<td><input type="text" onblur="dataChanged('res_lang_line', 'description', this, '<?php echo $id;?>', function(status, message){})" class="input" maxlength="250" value="<?php echo $description;?>"></td>
					<td><a class="button"  href="javascript:delLangLine<?php echo $pageIndex;?>('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
				</tr>
				<?php
				}
				?>
				<tr>
				<td><input type="text" id="editname<?php echo $pageIndex;?>" class="input" maxlength="100"></td>
			
				<td><input type="text" id="editdescription<?php echo $pageIndex;?>" class="input" maxlength="250"></td>
				<td><a class="button" href="javascript:addLangLine<?php echo $pageIndex;?>('<?php echo $lang_id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<br>
<?php
}else if($ac == "lang_line_content")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$pageIndex = "";
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	
	$sql = "SELECT id, name, description FROM res_lang_line WHERE lang_id='".$lang_id."' AND rel_id='".$rel_id."' AND status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$s = "";
	for($i=0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		if($s != "")
		{
			$s = $s."\n";
		}
		$s = $s.$row[0]."\t". $row[1];
	}
	
?>
<textarea id="editline_content" class="textarea" style="width:98%" rows="30">
<?php echo $s;?>
</textarea>
<?php
}
else if($ac == "delLangLine")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_lang_line SET write_date=NOW()";
	$sql = $sql.", status =1 WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addLangLine")
{
	
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sql = "SELECT id FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$lang_id."' AND name='".$name."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	if($numrows == 0)
	{
		
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO res_lang_line(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql.", lang_id";
		$sql = $sql.", name";
		$sql = $sql.", description";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$COMPANY_ID."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.", '".$lang_id."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".str_replace("'", "''", $description)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}else{
		$row = $result[0];
		$id = $row[0];
		
		$sql = "UPDATE res_lang_line SET status=0, description='".str_replace("'", "''", $description)."' WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	echo "OK";
	
}else if($ac == "cloneLine")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$from_lang_id = "";
	if(isset($_REQUEST['from_lang_id']))
	{
		$from_lang_id = $_REQUEST['from_lang_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	
	$sql = "SELECT name, description FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$from_lang_id."'";
	$msg->add("query", $sql);
	$result_line = $appSession->getTier()->getArray($msg);
	$numrows_line = count($result_line);	
	for($i =0; $i<$numrows_line; $i++)
	{
		$row = $result_line[$i];
		$name = $row[0];
		$description = $row[1];
		$sql = "SELECT id FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$lang_id."' AND name='".str_replace("'", "''", $name)."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		
		if($numrows == 0)
		{
			$description = "";
			if(isset($_REQUEST['description']))
			{
				$description = $_REQUEST['description'];
			}
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_lang_line(";
			$sql = $sql."id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", rel_id";
			$sql = $sql.", lang_id";
			$sql = $sql.", name";
			$sql = $sql.", description";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", NOW()";
			$sql = $sql.", NOW()";
			$sql = $sql.", '".$COMPANY_ID."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".$rel_id."'";
			$sql = $sql.", '".$lang_id."'";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", ''";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$r =$appSession->getTier()->exec($msg);
		}
	}
	echo "OK";
}else if($ac == "status_list")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$sql = "SELECT d1.id, d1.name, d1.per_view, d1.per_new, d1.per_edit, d1.per_delete, d1.sequence, d1.next_id FROM ir_module_status d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
	$msg->add("query", $sql);
	$result =$appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	
	
?>
Status: <a href="javascript:addStatus()"><img src="<?php echo URL;?>assets/img/add.png"></a>
	<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
			<th style="width:30px" nowrap="nowrap">Sequence</th>
			<th style="width:60px" nowrap="nowrap">Permission</th>
			<th style="width:120px" nowrap="nowrap">Goto</th>
			<th style="width:120px" nowrap="nowrap">Rel</th>
			<th style="width:120px" nowrap="nowrap">Valid</th>
			<th style="width:30px" nowrap="nowrap"></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				
				$id = $row[0];
				$name = $row[1];
				$per_new = $row[2];
				$per_edit = $row[3];
				$per_delete = $row[4];
				$sequence = $row[5];
				$next_id = $row[6];
				if($sequence == "")
				{
					$sequence = 0;
				}
				
			
			?>
			<tr>
			<td style="width:20px"><?php echo $j + 1; ?></td>
			<td ><input type="text" class="input" value="<?php echo $name; ?>" onblur="dataChanged('ir_module_status','name', this, '<?php echo $id;?>')" maxlength="36" ></td>
			<td ><input type="text" class="input" value="<?php echo $sequence; ?>" onblur="dataChanged('ir_module_status','sequence', this, '<?php echo $id;?>')" maxlength="36" ></td>
			<td>
			Edit
			<input <?php if($per_edit == "1"){ echo " checked "; }?> type="checkbox" class="input" onclick="dataChanged('ir_module_status','per_edit', this, '<?php echo $id;?>')"> 
			<br>
			Delete
			<input <?php if($per_delete == "1"){ echo " checked "; }?> type="checkbox" class="input" onclick="dataChanged('ir_module_status','per_delete', this, '<?php echo $id;?>')"> 
			</td>
			<td>
			<?php
			$sql = "SELECT status_id FROM ir_module_status_rel WHERE rel_id='".$id."' AND status=0";
			$msg->add("query", $sql);
			$numrows_rel = $appSession->getTier()->getArray($msg);
			for($n =0; $n<$numrows; $n++)
			{
				if($n>0)
				{
					echo "<br>";
				}
				$row1 = $result[$n];
				$_id = $row1["id"];
				
				$_name = $row1["name"];
				$checked = "";
				for($k =0; $k<$numrows_rel; $k++)
				{
					$rowrel = $result_rel[$k];
					if($rowrel[0] == $_id)
					{
						$checked = " checked ";
						break;
					}
				}
				?>
				<?php echo $_name;?>
			<input <?php echo $checked;?> type="checkbox" class="input" onclick="updateRel(this, 'ir_module_status_rel','n', 'status_id', '<?php echo $_id;?>', 'rel_id', '<?php echo $id;?>', 'status', function(status, message){  })"> 
				<?php
			}				
			?>
			</td>
			<td>
				<select class="select" onchange="dataChanged('ir_module_status','next_id', this, '<?php echo $id;?>')">
				<option value=""></option>
				<?php
				for($n =0; $n<$numrows; $n++)
				{
					$row1 = $result[$n];
				?>
				<option value="<?php echo $row1["id"];?>" <?php if($row1["id"] == $next_id){ echo " selected "; }?>><?php echo $row1["name"];?></option>
				<?php
				}
				?>
				</select>
			</td>
			<td>
			<a href="javascript:addValid('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a>
			<?php
			$sql = "SELECT d1.id, d2.name FROM ir_module_status_valid d1 LEFT OUTER JOIN ir_module d2 ON(d1.module_id = d2.id) WHERE d1.status =0 AND d1.status_id='".$id."'";
			$msg->add("query", $sql);
			$result_valid = $appSession->getTier()->getArray($msg);
			$numrows_valid = count($result_valid);	
			for($n =0; $n<$numrows_valid; $n++)
			{
				echo "<br>";
				$row_valid = $result_valid[$n];
			?>
			<?php echo $row_valid[1];?> <a href="javascript:removeValidStatus('<?php echo $row_valid[0]; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a>
			<?php
			}
			?>
			
			</td>
			<td  style="text-align:center; vertical-align:middle"><a href="javascript:removeStatus('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
			
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
	<script>
	function addStatus()
	{
		
		var name = prompt("Please enter your name", "");
		if (name == null || name == "") {
		  return;
		}

		var _url = '<?php echo URL;?>admin/developer/module/?ac=addStatus&module_id=<?php echo $module_id;?>&name=' + encodeURIComponent(name);	
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadStatus();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	
	function removeStatus(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/module/?ac=removeStatus&id=' + id;	
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadStatus();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function removeValidStatus(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/module/?ac=removeValidStatus&id=' + id;	
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadStatus();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	var status_id = "";
	function addValid(id)
	{
		status_id = id;
		var _url = '<?php echo URL;?>admin/developer/module/?ac=module_list&func=selModule';
		openPopup(_url);
	}
	function selModule(id, name)
	{
		closePopup();
		var _url = '<?php echo URL;?>admin/developer/module/?ac=addStatusValid&status_id=' + status_id;	
		_url = _url + '&module_id=' + id;
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadStatus();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
		
	}
	</script>
<?php	
}else if($ac == "addStatus")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO ir_module_status(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", module_id";
	$sql = $sql.", name";
	$sql = $sql.", sequence";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$COMPANY_ID."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.", 0";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addStatusValid")
{
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$status_id = "";
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$sql = "SELECT id FROM ir_module_status_valid WHERE module_id='".$module_id."' AND status_id='".$status_id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	if($numrows>0)
	{
		$row = $result[0];
		$id = $row[0];
		
		$sql = "UPDATE ir_module_status_valid SET write_date=NOW()";
		$sql = $sql.", status =0 WHERE id='".$id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}else
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO ir_module_status_valid(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", module_id";
		$sql = $sql.", status_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$COMPANY_ID."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$module_id."'";
		$sql = $sql.", '".$status_id."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	echo "OK";
}
else if($ac == "removeStatus")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_module_status SET write_date=NOW()";
	$sql = $sql.", status =1 WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeValidStatus")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_module_status_valid SET write_date=NOW()";
	$sql = $sql.", status =1 WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
else if($ac == "module_list")
{
	function buildCategory($func, $lines, $parent_id)
	{
		
			$count = 0;
			$line = 0;
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
					$line += 1;
			?>
			<tr >
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				<td style="padding-left:5px; padding-top:5px"><a href="javascript:<?php echo $func;?>('<?php echo $id;?>', '<?php echo $name;?>')"><?php echo $name;?></a></td>
			  </tr>
			  
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"></table></td>
			</tr>
			<?php
				
			}
		}
		
	}

	$sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$lines = [];
	for($j =0; $j<$numrows; $j++)
	{
		$row = $result[$j];
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$arr[2] = $row[2];
		$lines[$j] = $arr;
	}
	$func = '';
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	?>
	<div class="row">
		<div class="col container m12" >
			<a href="javascript:<?php echo $func;?>('')">Root</a>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php echo buildCategory($func, $lines, "");?>
			</table>
		</div>
	</div>
	<?php
}else if($ac == "module_list1")
{
	function buildCategory($lines, $parent_id, $func)
	{
		
			$count = 0;
			$line = 0;
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
					$line += 1;
			?>
			<tr >
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				<td style="padding-left:5px; padding-top:5px"><a href="javascript:<?php echo $func;?>('<?php echo $id;?>', '<?php echo $name;?>')"><?php echo $name;?></a></td>
			  </tr>
			  
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($lines, $id, $func);?></table></td>
			</tr>
			<?php
				
			}
		}
		
	}

	$sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$lines = [];
	for($j =0; $j<$numrows; $j++)
	{
		$row = $result[$j];
		$arr = [];
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$arr[2] = $row[2];
		$lines[$j] = $arr;
	}
	$func = '';
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	?>
	<a href="javascript:<?php echo $func;?>('')">Root</a>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php echo buildCategory($lines, "", $func);?>
		</table>
	<?php
}else if($ac == "moveModule")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	
	$sql = "UPDATE ir_module SET write_date=NOW()";
	$sql = $sql.", parent_id ='".$parent_id."' WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>