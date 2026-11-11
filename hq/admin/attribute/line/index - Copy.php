<?php

validUser($appSession);

$msg = $appSession->getTier()->createMessage();


$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}

$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.type_id, d1.value, d1.readonly, d1.sequence, d2.code AS type_code, d1.description";
$sql = $sql.", d1.category_id, d1.unit_id";
$sql = $sql." FROM res_attribute d1";
$sql = $sql." LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
$sql = $sql." WHERE d1.status =0 AND d1.rel_id='".$rel_id."'";
$sql = $sql." ORDER BY d1.sequence ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);



$sql = "SELECT id, code, name FROM res_attribute_type WHERE status =0 ORDER BY sequence ASC";
$msg->add("query", $sql);
$dt_type = $appSession->getTier()->getTable($msg);

$sql = "SELECT id, name FROM product_unit WHERE status =0 ORDER BY name ASC";
$msg->add("query", $sql);
$dt_unit = $appSession->getTier()->getTable($msg);

$sql = "SELECT id, name FROM product_specification_category WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') ORDER BY sequence ASC, name ASC";
$msg->add("query", $sql);
$dt_category = $appSession->getTier()->getTable($msg);

function printProduct($appSession, $dt, $dt_type, $parent_id, $space, $category_id, $pages, $dt_unit, $dt_category)
{
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "parent_id") == $parent_id && $dt->getString($i, "category_id") == $category_id)
		{
			
		
		$no = $no + 1;
		$id = $dt->getString($i, "id");
		$code = $dt->getString($i, "code");
		$name = $dt->getString($i, "name");
		$value = $dt->getString($i, "value");
		$type_code = $dt->getString($i, "type_code");
		$sequence = $dt->getFloat($i, "sequence");
		$type_id = $dt->getString($i, "type_id");
		$unit_id = $dt->getString($i, "unit_id");
		$category_id = $dt->getString($i, "category_id");
		$description = $dt->getString($i, "description");
		$pageIndex = $pages;
		if($pages != "")
		{
			$pageIndex = $pages.$no;
		}else{
			$pageIndex = $no;
		}
		
		?>
		<?php
		if($parent_id == "")
		{
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?><?php echo $pageIndex;?> <a href="javascript:addAttribute('<?php echo $id;?>', '');"> <i class="icon-plus"></i></a> &nbsp;&nbsp;&nbsp;<a href="javascript:delAttribute('<?php echo $id;?>');"> <i class="icon-minus"></i></a>
			</div>
			<div class="col m4 padding" >
				<input type="text" class="input" value="<?php echo $code;?>" onblur="saveLine('res_attribute', 'code', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
			<div class="col m2 padding" >
				Sequence:
			</div>
			<div class="col m4 padding" >
				<input type="number" class="input" value="<?php echo $sequence;?>" onblur="saveLine('res_attribute', 'sequence', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
		</div>
		<?php
		}else{
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?><?php echo $pageIndex;?> <a href="javascript:addAttribute('<?php echo $id;?>', '');"> <i class="icon-plus"></i></a>&nbsp;&nbsp;&nbsp;<a href="javascript:delAttribute('<?php echo $id;?>');"> <i class="icon-minus"></i></a>
			</div>
			<div class="col m3 padding" >
				<input type="text" class="input" value="<?php echo $code;?>" onblur="saveLine('res_attribute', 'code', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
			<div class="col m2 padding" >
				Sequence:
			</div>
			<div class="col m2 padding" >
				<input type="number" class="input" value="<?php echo $sequence;?>" onblur="saveLine('res_attribute', 'sequence', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
			<div class="col m1 padding" >
				Type:
			</div>
			<div class="col m2 padding" >
				<select class="select" onchange="saveLine('res_attribute', 'type_id', '', '<?php echo $id;?>', this, function(status, message){ loadAttribute(); })">
				<option value=""></option>
				<?php
				for($j =0; $j<$dt_type->getRowCount(); $j++)
				{
					$_type_id =$dt_type->getString($j, "id");
					$code =$dt_type->getString($j, "name");
				?>
				<option value="<?php echo $_type_id;?>" <?php if($type_id == $_type_id){ echo " selected ";}?>><?php echo $code;?></option>
				<?php
				}
				?>
				</select>
			</div>
		</div>
		<?php
		}
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?>Name:
			</div>
			<div class="col m8 padding" >
				<input type="text" class="input" value="<?php echo $name;?>" onblur="saveLine('res_attribute', 'name', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
			<div class="col m2 padding" >
				<?php if($category_id != "Value"){
					?>
				<select class="select" onchange="saveLine('res_attribute', 'category_id', '', '<?php echo $id;?>', this, function(status, message){  })">
				<option value=""></option>
				<?php
				for($j =0; $j<$dt_category->getRowCount(); $j++)
				{
					$_category_id =$dt_category->getString($j, "id");
					$_category_name =$dt_category->getString($j, "name");
				?>
				<option value="<?php echo $_category_id;?>" <?php if($category_id == $_category_id){ echo " selected ";}?>><?php echo $_category_name;?></option>
				<?php
				}
				?>
				</select>
				<?php
				}
				?>
			</div>
		</div>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?>Description:
			</div>
			<div class="col m10 padding" >
				<input type="text" class="input" value="<?php echo $description;?>" onblur="saveLine('res_attribute', 'description', '', '<?php echo $id;?>', this, function(status, message){})">
			</div>
		</div>
		<?php
		$type_code = strtolower($type_code);
		if($parent_id != "")
		{
			if($type_code == "specification")
			{
			?>
			
			<div class="row">
				<div class="col m2 padding">
					<?php echo $space;?> 
				</div>
				<div class="col m10 padding">
				<?php
				$appSession->getTool()->httpGet(URL."addons/product_specification/?rel_id=".$id."&pageIndex=".$i);
				?>
				</div>
			</div>
			<?php
			}else if($type_code == "survey")
			{
			?>
			<div class="row">
				<div class="col m2 padding">
					<?php echo $space;?> 
				</div>
				<div class="col m10 padding">
				<?php
				$appSession->getTool()->httpGet(URL."addons/survey/?survey_id=".$id."&pageIndex=".$i);
				?>
				</div>
			</div>
			<?php
			}else if($type_code == "select" || $type_code == "select2" || $type_code == "checkbox"|| $type_code == "option")
			{
			?>
			<div class="row">
				<div class="col m2 padding">
					<?php echo $space;?> 
				</div>
				<div class="col m10 padding">
				<a href="javascript:addValue('<?php echo $id;?>')"><i class="icon-plus"></i></a>
				<input type="hidden" id="edit<?php echo $id;?>" value="<?php echo $value;?>">
				<div id="pn<?php echo $id;?>">
				<?php
				$list = $appSession->getTool()->split($value, ';');
				for($j =0; $j<count($list); $j++)
				{
					if($list[$j] == "")
					{
						continue;
					}
					$index = $appSession->getTool()->indexOf($list[$j], '=');
					$k = $list[$j];
					$v = $list[$j];
					if($index != -1)
					{
						$k = $appSession->getTool()->substring($list[$j], 0, $index);
						$v = $appSession->getTool()->substring($list[$j],$index + 1);
					}
					echo "<br>";
					echo '<a href="javascript:changeSelect(\''.$id.'\', '.$j.')"> '.$k.'='.$v.' </a> <a href="javascript:removeValue(\''.$id.'\', '.$j.')"> <i class="icon-minus"></i></a>';
				}
				?>
				</div>
				
				</div>
			</div>
			<?php 
		if($category_id != "Value")
		{
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?>
			</div>
			<div class="col m10 padding" >
				With Value: <a href="javascript:addAttribute('<?php echo $id;?>', 'Value');"> <i class="icon-plus"></i></a> 
				<?php
				printProduct($appSession, $dt, $dt_type , $id, $space."&nbsp;&nbsp;&nbsp;", "Value", $pageIndex.".", $dt_unit, $dt_category);
				?>
			</div>
		</div>
		<?php
		}
		?>
			<?php
			}
			else{
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?>Value: 
			</div>
			<div class="col m8 padding" >
				<input type="text" class="input" value="<?php echo $value;?>" onblur="saveLine('res_attribute', 'value', '', '<?php echo $id;?>', this, function(status, message){  })">
			</div>
			<div class="col m2 padding" >
				<select class="select" onchange="saveLine('res_attribute', 'unit_id', '', '<?php echo $id;?>', this, function(status, message){ loadAttribute(); })">
				<option value=""></option>
				<?php
				for($j =0; $j<$dt_unit->getRowCount(); $j++)
				{
					$_unit_id =$dt_unit->getString($j, "id");
					$_name =$dt_unit->getString($j, "name");
				?>
				<option value="<?php echo $_unit_id;?>" <?php if($unit_id == $_unit_id){ echo " selected ";}?>><?php echo $_name;?></option>
				<?php
				}
				?>
				</select>
			</div>
		</div>
		<?php 
		if($category_id != "Value")
		{
		?>
		<div class="row">
			<div class="col m2 padding">
				<?php echo $space;?>
			</div>
			<div class="col m10 padding" >
				With Value: <a href="javascript:addAttribute('<?php echo $id;?>', 'Value');"> <i class="icon-plus"></i></a> 
				<?php
				printProduct($appSession, $dt, $dt_type , $id, $space."&nbsp;&nbsp;&nbsp;", "Value", $pageIndex.".", $dt_unit, $dt_category);
				?>
			</div>
		</div>
		<?php
		}
		?>
		<?php
			}
		}
		?>
		<?php 
		if($category_id != "Value")
		{
		?>
		<div class="row">
			<div class="col m2 padding" >
			&nbsp;
			</div>
			<div class="col m10 padding">
				<a href="javascript:addCondition('<?php echo $id;?>')">Condition <i class="icon-plus"></i></a>
				<div class ="row" >
					<div class="col m12" id="pnCondition<?php echo $id;?>">
					</div>
				</div>
			</div>
		</div>
		<?php
		}else{
		?>
		<div class="row">
			<div class="col m2 padding" >
			&nbsp;
			</div>
			<div class="col m10 padding">
				<a href="javascript:addTask('<?php echo $id;?>')">Task <i class="icon-plus"></i></a>
				<div class ="row" >
					<div class="col m12" id="pnTask<?php echo $id;?>">
					</div>
				</div>
			</div>
		</div>
		
		<?php
		}
		?>
		<?php
		printProduct($appSession, $dt, $dt_type , $id, $space."&nbsp;&nbsp;&nbsp;", "", $pageIndex.".", $dt_unit, $dt_category);
		}
	}
}	

?>
<?php
	printProduct($appSession, $dt, $dt_type, "", "", "", "", $dt_unit, $dt_category);
?>
<script>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$id = $dt->getString($i, "id");
	?>
	loadCondition('<?php echo $id;?>');
	<?php
	}
	?>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$id = $dt->getString($i, "id");
		if($dt->getString($i, "category_id") == "Value")
		{
			
	?>
	loadTask('<?php echo $id;?>');
	<?php
		}
	}
	?>
	function addValue(id)
	{
		var name = prompt("Please enter name", "");
		if (name == null) {
		  name = "";
		} 
		var code = prompt("Please enter code", "");
		if (code == null) {
			code = name;
		} 
		var ctr = document.getElementById('edit'+id);
		var value = ctr.value;
		if(value != "")
		{
			value = value + ";";
		}
		value = value + code+'=' +name;
		ctr.value = value;
		saveLine('res_attribute', 'value', '',id, ctr, function(status, message){ buildValue(id); });

	}
	function buildValue(id)
	{
		var ctr = document.getElementById('edit'+id);
		var value = ctr.value;
		var list= value.split(';');
		var s = "";
		for(var i=0; i<list.length; i++)
		{
			if(list[i] == "")
			{
				continue;
			}
			var k = list[i];
			var v = list[i];
			var index = list[i].indexOf('=');
			if(index != -1)
			{
				k = list[i].substring(0, index);
				v = list[i].substring(index + 1);
			}
			s = s + "<br>";
			s = s + '<a href="javascript:changeValue(\'' + id + '\', ' + i +')">' + v + '</a> <a href="javascript:removeValue(\'' + id + '\', ' + i + ')"> <i class="icon-minus"></i></a>';
			
		}
		ctr = document.getElementById('pn'+id);
		ctr.innerHTML = s;
	}
	function changeValue(id, index)
	{
		var name = prompt("Please enter name", "");
		if (name == null ) {
		   name = "";
		} 
		var code = prompt("Please enter code", "");
		if (code == null || code == "") {
			code = name;
		} 
		var ctr = document.getElementById('edit'+id);
		var value = ctr.value;
		var list= value.split(';');
		list[index] = code+ "=" +name;
		value = "";
		for(var i=0; i<list.length; i++)
		{
			if(value != "")
			{
				value = value + ";";
			}
			value = value + list[i];
		}
		ctr.value = value;
		saveLine('res_attribute', 'value', '',id, ctr, function(status, message){ buildValue(id); });
	}
	function removeValue(id, index)
	{
		var ctr = document.getElementById('edit'+id);
		var value = ctr.value;
		var list= value.split(';');
		value = "";
		for(var i=0; i<list.length; i++)
		{
			if(i == index)
			{
				continue;
			}
			if(value != "")
			{
				value = value + ";";
			}
			value = value + list[i];
		}
		ctr.value = value;
		saveLine('res_attribute', 'value', '',id, ctr, function(status, message){ buildValue(id); });
	}
	function loadCondition(rel_id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=listCondition&rel_id=' + rel_id;
		loadPage('pnCondition' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
	}
	function addCondition(rel_id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=addCondition&rel_id=' + rel_id;
		loadPage('pnCondition' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadCondition(rel_id);
				
			}
			
		}, true);
	}
	function removeCondition(rel_id, id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=removeCondition&id=' + id;
		loadPage('pnCondition' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadCondition(rel_id);
				
			}
			
		}, true);
	}
	function loadTask(rel_id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=listTask&rel_id=' + rel_id;
		loadPage('pnTask' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
	}
	function addTask(rel_id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=addTask&rel_id=' + rel_id;
		loadPage('pnTask' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadTask(rel_id);
				
			}
			
		}, true);
	}
	function removeTask(rel_id, id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=removeTask&id=' + id;
		loadPage('pnCondition' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadTask(rel_id);
				
			}
			
		}, true);
	}
	var select_rel_id = "";
	var select_task_id = "";
	function selectRelId(rel_id, task_id)
	{
		select_rel_id = rel_id;
		select_task_id = task_id;
		
	}
	function selDepartment(id, name)
	{
		
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=addResRel&type=LINE_DEPARTMENT&rel_id=' + select_task_id;
		_url = _url + '&res_id=' + id;
		
		loadPage('pnTask' + select_rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				closePopup();
				loadTask(select_rel_id);
				
			}
			
		}, true);
	}
	function selEmployee(id, name)
	{
		
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=addResRel&type=EMPLOYEE&rel_id=' + select_task_id;
		_url = _url + '&res_id=' + id;
		loadPage('pnTask' + select_rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				closePopup();
				loadTask(select_rel_id);
				
			}
			
		}, true);
	}
	function removeResRel(rel_id, id)
	{
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=removeResRel&id=' + id;
		loadPage('pnCondition' + rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadTask(rel_id);
				
			}
			
		}, true);
	}
	function selClassification(id, name)
	{
		
		var _url = '<?php echo URL;?>admin/attribute/action/?ac=addResRel&type=Classification&rel_id=' + select_task_id;
		_url = _url + '&res_id=' + id;
		
		loadPage('pnTask' + select_rel_id, _url, function(status, message)
		{
			if(status== 0)
			{
				closePopup();
				loadTask(select_rel_id);
				
			}
			
		}, true);
	}
	
</script>
	


    
