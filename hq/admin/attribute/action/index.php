<?php
validUser($appSession);

$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
function copyAttribute($appSession, $dt, $to_id, $parent_id)
{
	$msg = $appSession->getTier()->createMessage();
	$sql = "";
	for($i=0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "parent_id") == $parent_id)
		{
			
			$attribute_id = $dt->getString($i, "id");
			$new_id = $dt->getString($i, "new_id");
			$new_parent_id = $dt->getString($i, "parent_id");
			if($new_parent_id != "")
			{
				for($j=0; $j<$dt->getRowCount(); $j++)
				{
					if($dt->getString($j, "id") == $new_parent_id)
					{
						$new_parent_id = $dt->getString($j, "new_id");
						break;
					}
				}
			}
			$code = $dt->getString($i, "code");
			$name = $dt->getString($i, "name");
			$type_id = $dt->getString($i, "type_id");
			$category_id = $dt->getString($i, "category_id");
			$value = $dt->getString($i, "value");
			$sequence = $dt->getString($i, "sequence");
			if($sequence == "")
			{
				$sequence = 0;
			}
			$builder = $appSession->getTier()->createBuilder("res_attribute");
			$builder->add("id", $new_id);
			$builder->add("rel_id", $to_id);
			$builder->add("parent_id", $new_parent_id);
			$builder->add("code", $code);
			$builder->add("name", $name);
			$builder->add("type_id", $type_id);
			$builder->add("category_id", $category_id);
			$builder->add("value", $value);
			$builder->add("sequence", $sequence);
			$builder->add("readonly", 0);
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status", 0);
			$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
			$sql = $appSession->getTier()->getInsert($builder);
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			
			$sql = "SELECT id, oporator, parameter, is_or, expression FROM res_condition WHERE rel_id='".$attribute_id."' AND status =0";
			$sql = $sql." ORDER BY create_date ASC";
			$msg->add("query", $sql);
			$values = $appSession->getTier()->getArray($msg);
			for($j =0; $j<count($values); $j++)
			{
				
				$condition_id = $values[$j][0];
				$oporator = $values[$j][1];
				$parameter = $values[$j][2];
				$is_or = $values[$j][3];
				$expression = $values[$j][4];
				if($is_or == "")
				{
					$is_or = "0";
				}
				
				$builder = $appSession->getTier()->createBuilder("res_condition");
				$new_condition_id = $appSession->getTool()->getId();
				$builder->add("id", $new_condition_id);
				$builder->add("rel_id", $new_id);
				$builder->add("oporator", $oporator);
				$builder->add("parameter", $parameter);
				$builder->add("is_or", $is_or);
				$builder->add("expression", $expression);
				$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("status", 0);
				$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
				$sql = $appSession->getTier()->getInsert($builder);
				
				$msg->add("query", $sql);
				$r = $appSession->getTier()->exec($msg);
				
				$sql = "SELECT d1.id, d1.type, d1.name, d1.description FROM res_status_notification d1 LEFT OUTER JOIN hr_employee d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN hr_line_department d3 ON(d1.status_id = d3.id) WHERE d1.rel_id='".$condition_id."' AND d1.status =0";
				$sql = $sql." ORDER BY d1.create_date ASC";
				
				$msg->add("query", $sql);
				$values1 = $appSession->getTier()->getArray($msg);
				for($jj =0; $jj<count($values1); $jj++)
				{
					
					$task_id = $values1[$jj][0];
					$type = $values1[$jj][1];
					$name = $values1[$jj][2];
					$description = $values1[$jj][3];
					
					$builder = $appSession->getTier()->createBuilder("res_status_notification");
					$new_task_id = $appSession->getTool()->getId();
					$builder->add("id", $new_task_id);
					$builder->add("rel_id", $new_condition_id);
					$builder->add("type", $type);
					$builder->add("name", $name);
					$builder->add("description", $description);
					$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("status", 0);
					$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
					$sql = $appSession->getTier()->getInsert($builder);
					
					$msg->add("query", $sql);
					$appSession->getTier()->exec($msg);
		
					
					
					$sql = "SELECT  d1.res_id, d1.type FROM res_rel d1 WHERE d1.rel_id='".$task_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
					$msg->add("query", $sql);
					$res_rels = $appSession->getTier()->getArray($msg);
					for($n =0; $n<count($res_rels); $n++)
					{
						$builder = $appSession->getTier()->createBuilder("res_rel");
						$id = $appSession->getTool()->getId();
						$builder->add("id", $id);
						$builder->add("rel_id", $new_task_id);
						$builder->add("res_id", $res_rels[$n][0]);
						$builder->add("type", $res_rels[$n][1]);
						$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
						$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
						$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
						$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
						$builder->add("status", 0);
						$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
						$sql = $appSession->getTier()->getInsert($builder);
						
						$msg->add("query", $sql);
						$appSession->getTier()->exec($msg);
					}
				}
				
			}
			
			$sql = "SELECT d1.id, d1.type, d1.name, d1.description FROM res_status_notification d1 LEFT OUTER JOIN hr_employee d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN hr_line_department d3 ON(d1.status_id = d3.id) WHERE d1.rel_id='".$attribute_id."' AND d1.status =0";
			$sql = $sql." ORDER BY d1.create_date ASC";
			
			$msg->add("query", $sql);
			$values = $appSession->getTier()->getArray($msg);
			for($j =0; $j<count($values); $j++)
			{
				
				$task_id = $values[$j][0];
				$type = $values[$j][1];
				$name = $values[$j][2];
				$description = $values[$j][3];
				
				$builder = $appSession->getTier()->createBuilder("res_status_notification");
				$new_task_id = $appSession->getTool()->getId();
				$builder->add("id", $new_task_id);
				$builder->add("rel_id", $new_id);
				$builder->add("type", $type);
				$builder->add("name", $name);
				$builder->add("description", $description);
				$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("status", 0);
				$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
				$sql = $appSession->getTier()->getInsert($builder);
				
				$msg->add("query", $sql);
				$appSession->getTier()->exec($msg);
	
				
				
				$sql = "SELECT  d1.res_id, d1.type FROM res_rel d1 WHERE d1.rel_id='".$task_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
				$msg->add("query", $sql);
				$res_rels = $appSession->getTier()->getArray($msg);
				for($n =0; $n<count($res_rels); $n++)
				{
					$builder = $appSession->getTier()->createBuilder("res_rel");
					$id = $appSession->getTool()->getId();
					$builder->add("id", $id);
					$builder->add("rel_id", $new_task_id);
					$builder->add("res_id", $res_rels[$n][0]);
					$builder->add("type", $res_rels[$n][1]);
					$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
					$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
					$builder->add("status", 0);
					$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
					$sql = $appSession->getTier()->getInsert($builder);
					
					$msg->add("query", $sql);
					$appSession->getTier()->exec($msg);
				}
			}
			 copyAttribute($appSession, $dt, $to_id, $attribute_id);
			
		}
	}
}
if($ac == "addAttribute")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$code = '';
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$value = '';
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$type_id = '';
	if(isset($_REQUEST['type_id']))
	{
		$type_id = $_REQUEST['type_id'];
	}
	
	$sql = "SELECT MAX(sequence) AS sequence FROM res_attribute WHERE rel_id='".$rel_id."' AND status =0 AND parent_id='".$parent_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$sequence = 1;
	
	if(count($values)>0 && $values[0][0] != "")
	{
		$sequence = $appSession->getTool()->toDouble($values[0][0]);
		$sequence = $sequence + 1;
	}
	$code = $code.".".$sequence;
	if($parent_id != "")
	{
		$sql = "SELECT code FROM res_attribute WHERE status =0 AND id='".$parent_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		if(count($values)>0 && $values[0][0] != "")
		{
			$code = $values[0][0].".".$sequence;
		}
	}
	$builder = $appSession->getTier()->createBuilder("res_attribute");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $rel_id);
	$builder->add("parent_id", $parent_id);
	$builder->add("code", $code);
	$builder->add("name", $name);
	$builder->add("type_id", $type_id);
	$builder->add("category_id", $category_id);
	$builder->add("value", $value);
	$builder->add("sequence", $sequence);
	$builder->add("readonly", 0);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
	
}else if($ac == "delAttribute")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	function delChild($appSession, $parent_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT id FROM res_attribute WHERE parent_id='".$parent_id."' AND status =0";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($values); $i++)
		{
			$id = $values[$i][0];
			$sql = "UPDATE res_attribute SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			delChild($appSession, $id);
		}
	}
	$sql = "UPDATE res_attribute SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	delChild($appSession, $id);
	echo "OK";
}else if($ac == "listCondition")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT id, oporator, parameter, is_or, expression FROM res_condition WHERE rel_id='".$rel_id."' AND status =0";
	$sql = $sql." ORDER BY create_date ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		
		$condition_id = $values[$i][0];
		$oporator = $values[$i][1];
		$parameter = $values[$i][2];
		$is_or = $values[$i][3];
		$expression = $values[$i][4];
	?>
	<div class="row">
		<div class="col m1 padding">
			<a href="javascript:removeCondition('<?php echo $rel_id;?>', '<?php echo $condition_id;?>')"> <i class="icon-minus"></i></a>
		</div>
		<div class="col m2 padding">
		<select class="select" onchange="saveLine('res_condition', 'is_or', '', '<?php echo $condition_id;?>', this, function(status, message){})">
		<option value="0" <?php if($is_or == "0"){ echo " selected ";}?>>And</option>
		<option value="1" <?php if($is_or == "1"){ echo " selected ";}?>>Or</option>
		</select>
		
		</div>
		<div class="col m3 padding">
			<input type="text" value="<?php echo $expression;?>" placeholder="Expression" class="input" onblur="saveLine('res_condition', 'expression', '', '<?php echo $condition_id;?>', this, function(status, message){})"/>
		</div>
		<div class="col m3 padding">
			<select class="select" onchange="saveLine('res_condition', 'oporator', '', '<?php echo $condition_id;?>', this, function(status, message){})">
				<option value=""></option>
				<option value="=" <?php if($oporator == "="){ echo " selected ";}?>>=</option>
				<option value="!=" <?php if($oporator == "!="){ echo " selected ";}?> >!=</option>
				<option value="<" <?php if($oporator == "<"){ echo " selected ";}?>><</option>
				<option value="<=" <?php if($oporator == "<="){ echo " selected ";}?>><=</option>
				<option value=">" <?php if($oporator == ">"){ echo " selected ";}?>>></option>
				<option value=">=" <?php if($oporator == ">="){ echo " selected ";}?>>>=</option>
				<option value="in" <?php if($oporator == "in"){ echo " selected ";}?> >In</option>
				<option value="not_in" <?php if($oporator == "not_in"){ echo " selected ";}?> >Not In</option>
				<option value="begins_with" <?php if($oporator == "begins_with"){ echo " selected ";}?>>Begins with</option>
				<option value="not_begins_with" <?php if($oporator == "not_begins_with"){ echo " selected ";}?>>Not begins with</option>
				<option value="contains" <?php if($oporator == "contains"){ echo " selected ";}?>>Contains</option>
				<option value="not_contains" <?php if($oporator == "not_contains"){ echo " selected ";}?>>Not Contains</option>
				<option value="ends_with" <?php if($oporator == "ends_with"){ echo " selected ";}?>>Ends with</option>
				<option value="not_ends_with" <?php if($oporator == "not_ends_with"){ echo " selected ";}?>>Not ends with</option>
				<option value="is_empty" <?php if($oporator == "is_empty"){ echo " selected ";}?>>Is empty</option>
				<option value="is_not_empty" <?php if($oporator == "is_not_empty"){ echo " selected ";}?>>Not empty</option>
			</select>
		</div>
		
		<div class="col m2 padding">
			<input type="text" value="<?php echo $parameter;?>" placeholder="Value" class="input" onblur="saveLine('res_condition', 'parameter', '', '<?php echo $condition_id;?>', this, function(status, message){})"/>
		</div>
		<div class="col m1 padding">
			<a href="javascript:addTask('<?php echo $condition_id;?>')">Task <i class="icon-plus"></i></a>
		</div>
		
	</div>
	<div class ="row" >
		<div class="col m1">
		&nbsp;
		</div>
		<div class="col m11" id="pnTask<?php echo $condition_id;?>">
		</div>
	</div>
	<script>
		loadTask('<?php echo $condition_id;?>');
	</script>
	<?php
	}
	if(count($values)>0)
	{
	?>
	<div class="row">
		<div class="col m12 padding">
			<a href="javascript:addTask('<?php echo $rel_id;?>')">Task <i class="icon-plus"></i></a>
			<div class ="row" >
				<div class="col m12" id="pnTask<?php echo $rel_id;?>">
				</div>
			</div>
		</div>
	</div>
	<script>
		loadTask('<?php echo $rel_id;?>');
	</script>
	<?php
	}
}else if($ac == "addCondition")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$builder = $appSession->getTier()->createBuilder("res_condition");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $rel_id);
	$builder->add("oporator", "");
	$builder->add("parameter", "");
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "listTask")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.id, d1.type, d1.name, d1.description FROM res_status_notification d1 LEFT OUTER JOIN hr_employee d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN hr_line_department d3 ON(d1.status_id = d3.id) WHERE d1.rel_id='".$rel_id."' AND d1.status =0";
	$sql = $sql." ORDER BY d1.create_date ASC";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		
		$task_id = $values[$i][0];
		$type = $values[$i][1];
		$name = $values[$i][2];
		$description = $values[$i][3];
		
		
		$sql = "SELECT d1.id, d2.name, d3.code, d3.first_name, d3.last_name, d3.middle_name, d1.unit_id FROM res_rel d1 LEFT OUTER JOIN hr_line_department d2 ON(d1.res_id = d2.id) LEFT OUTER JOIN hr_employee d3 ON(d1.res_id = d3.id) WHERE d1.rel_id='".$task_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$res_rels = $appSession->getTier()->getArray($msg);
		
		
		$sql = "SELECT d1.id, d3.code, d3.name, d1.rel_id FROM res_rel d1 LEFT OUTER JOIN res_rel d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN product_classification d3 ON(d1.res_id = d3.id) WHERE d2.rel_id='".$task_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
		$msg->add("query", $sql);
		$specificationList = $appSession->getTier()->getArray($msg);
	
		
	?>
	<div class="row">
		<div class="col m1 padding">
			<a href="javascript:removeTask('<?php echo $rel_id;?>', '<?php echo $task_id;?>')"> <i class="icon-minus"></i></a>
		</div>
		<div class="col m2 padding">
		When
		<select class="select" onchange="saveLine('res_status_notification', 'type', '', '<?php echo $task_id;?>', this, function(status, message){})">
		<option value=""></option>
		<option value="true" <?php if($type == "true"){ echo " selected ";}?>>True</option>
		<option value="false" <?php if($type == "false"){ echo " selected ";}?>>False</option>
		</select>
		
		</div>
		
		<div class="col m9 padding">
			Department: <a href="javascript:selectRelId('<?php echo $rel_id;?>', '<?php echo $task_id;?>');openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=49a7eec3-ad43-4e35-aec9-114632c5cca8&module_model_id=d37ab78f-726a-4af4-82b3-77d0b97a7a44&pid=&parent_id=parent_id&parent_value=&func=selDepartment')"><i class="icon-plus"></i></a> |  Employee: <a href="javascript:selectRelId('<?php echo $rel_id;?>', '<?php echo $task_id;?>');openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=1e1febc6-a053-4a75-949f-5aee896c6382&module_model_id=0e863368-d369-4836-8829-60d63e3a7902&pid=&parent_id=&parent_value=&func=selEmployee')"><i class="icon-plus"></i></a>
			<?php
			for($j =0; $j<count($res_rels); $j++)
			{
				$res_id = $res_rels[$j][0];
				$department_name = $res_rels[$j][1];
				$employee_code = $res_rels[$j][2];
				$first_name = $res_rels[$j][3];
				$last_name = $res_rels[$j][4];
				$middle_name = $res_rels[$j][5];
				$unit_id = $res_rels[$j][6];
			?>
			<br>
			<?php if($department_name != ""){ ?>
			<input type = "text" class="input" style="width:80px" value="<?php echo $unit_id;?>" placeholder="Version" onchange="saveLine('res_rel', 'unit_id', '', '<?php echo $res_id;?>', this, function(status, message){})" /> <?php echo $department_name;?> <a href="javascript:removeResRel('<?php echo $rel_id;?>', '<?php echo $res_id;?>');"><i class="icon-minus"></i></a>
			
			<br> &nbsp;&nbsp;&nbsp;Sub Specification: <a href="javascript:selectRelId('<?php echo $rel_id;?>', '<?php echo $res_id;?>');openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=b8729bb8-d2f5-4a38-b8a3-2b349bf0b7ee&module_model_id=a1e386fd-4ee2-4f6f-df40-adc4b180896b&pid=&parent_id=parent_id&parent_value=&func=selClassification')"><i class="icon-plus"></i></a>
			<?php
			for($jj =0; $jj<count($specificationList); $jj++)
			{
				if($res_id != $specificationList[$jj][3])
				{
					continue;
				}
				$sub_res_id = $specificationList[$jj][0];
				$specification_code = $specificationList[$jj][1];
				$specification_name = $specificationList[$jj][2];
				
			?>
			<br>&nbsp;&nbsp;&nbsp;<?php echo $specification_code;?>. <?php echo $specification_name;?> <a href="javascript:removeResRel('<?php echo $rel_id;?>', '<?php echo $sub_res_id;?>');"><i class="icon-minus"></i></a>
			<?php
			}
			?>
			<?php
			}
			?>
			<?php if($employee_code != ""){ ?>
			<input type = "text" class="input" style="width:80px" value="<?php echo $unit_id;?>" placeholder="Version" onchange="saveLine('res_rel', 'unit_id', '', '<?php echo $res_id;?>', this, function(status, message){})" />
			<?php echo $employee_code;?>. <?php echo $first_name;?> <?php echo $last_name;?> <?php echo $middle_name;?> <a href="javascript:removeResRel('<?php echo $rel_id;?>', '<?php echo $res_id;?>');"><i class="icon-minus"></i></a>
			<br> &nbsp;&nbsp;&nbsp;Sub Specification: <a href="javascript:selectRelId('<?php echo $rel_id;?>', '<?php echo $res_id;?>');openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=b8729bb8-d2f5-4a38-b8a3-2b349bf0b7ee&module_model_id=a1e386fd-4ee2-4f6f-df40-adc4b180896b&pid=&parent_id=parent_id&parent_value=&func=selClassification')"><i class="icon-plus"></i></a>
			<?php
			for($jj =0; $jj<count($specificationList); $jj++)
			{
				if($res_id != $specificationList[$jj][3])
				{
					continue;
				}
				$sub_res_id = $specificationList[$jj][0];
				$specification_code = $specificationList[$jj][1];
				$specification_name = $specificationList[$jj][2];
				
			?>
			<br>&nbsp;&nbsp;&nbsp;<?php echo $specification_code;?>. <?php echo $specification_name;?> <a href="javascript:removeResRel('<?php echo $rel_id;?>', '<?php echo $sub_res_id;?>');"><i class="icon-minus"></i></a>
			<?php
			}
			?>
			<?php
			}
			?>
			
			<?php
			}
			?>
		</div>
		
	</div>
	<div class="row">
		<div class="col m12 padding">
			Name:
			<input type="text" value="<?php echo $name;?>" class="input" onblur="saveLine('res_status_notification', 'name', '', '<?php echo $task_id;?>', this, function(status, message){})"/>
		</div>
	</div>
	<div class="row">
		<div class="col m12 padding">
			Solution:
			<input type="text" value="<?php echo $description;?>" class="input" onblur="saveLine('res_status_notification', 'description', '', '<?php echo $task_id;?>', this, function(status, message){})"/>
		</div>
	</div>
	
	<?php
	}
	
}else if($ac == "addTask")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$builder = $appSession->getTier()->createBuilder("res_status_notification");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $rel_id);
	$builder->add("type", "");
	$builder->add("name", "");
	$builder->add("description", "");
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
	
}else if($ac == "removeCondition")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_condition SET status =1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "removeTask")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_status_notification SET status =1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "addResRel")
{
	$res_id = '';
	if(isset($_REQUEST['res_id']))
	{
		$res_id = $_REQUEST['res_id'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$builder = $appSession->getTier()->createBuilder("res_rel");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $rel_id);
	$builder->add("res_id", $res_id);
	$builder->add("type", $type);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeResRel")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_rel SET status =1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "copyAttribute")
{
	$from_id = '';
	if(isset($_REQUEST['from_id']))
	{
		$from_id = $_REQUEST['from_id'];
	}
	$to_id = '';
	if(isset($_REQUEST['to_id']))
	{
		$to_id = $_REQUEST['to_id'];
	}
	$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.type_id, d1.value, d1.readonly, d1.sequence, d2.code AS type_code, d1.description, '' AS new_id";
	$sql = $sql.", d1.category_id";
	$sql = $sql." FROM res_attribute d1";
	$sql = $sql." LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
	$sql = $sql." WHERE d1.status =0 AND d1.rel_id='".$from_id."'";
	$sql = $sql." ORDER BY d1.sequence ASC";
	echo $sql;
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	for($i=0; $i<$dt->getRowCount(); $i++)
	{
		$dt->setValue($appSession->getTool()->getId(), $i, "new_id");
	}
	copyAttribute($appSession, $dt, $to_id, "");
	echo "OK";
}
?>