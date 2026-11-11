<?php
require_once(ABSPATH.'api/Task.php' );
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "form")
{
	$task = new Task();
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$arr = $task->getName($appSession, $rel_id, $type);
	$start_date = $appSession->getTool()->replace($arr[0], " ", "T");
	$end_date = $appSession->getTool()->replace($arr[1], " ", "T");
	$description = $arr[2];
	$sql = "SELECT id, name FROM project_priority WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$dt_priority = $appSession->getTier()->getTable($msg);
	
	
	$sql = "SELECT id, name FROM project_status WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$dt_status = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT id, name FROM project_proccess WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$dt_proccess = $appSession->getTier()->getTable($msg);
	$sql = "SELECT receipt_no, description, start_date, end_date, proccess_id, priority_id, status_id FROM mrp_workorder_routing WHERE id='".$id."'";
	
	$receipt_no = "";
	$msg->add("query",$sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	if($dt->getRowCount()>0)
	{
		$receipt_no = $dt->getString(0, "receipt_no");
		$description = $dt->getString(0, "description");
		$start_date = $dt->getString(0, "start_date");
		$end_date = $dt->getString(0, "end_date");
		$start_date = $appSession->getTool()->replace($start_date, " ", "T");
		$end_date = $appSession->getTool()->replace($end_date, " ", "T");
		$proccess_id = $dt->getString(0, "proccess_id");
		$priority_id = $dt->getString(0, "priority_id");
		$status_id = $dt->getString(0, "status_id");
	}
	

	
	
	?>
	<div class="row">
		<div class="col m12 padding">
			<input type="button" class="btn btn-primary" onclick="doAddTask('<?php echo $id;?>', assignedList)" value="Save">
			<?php if($receipt_no != ""){
				echo "#: ".$receipt_no;
			}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("Start");?>:
		</div>
		<div class="col m4 padding">
			<input type="datetime-local" class="input" id="edittask_start_date" value="<?php echo $start_date;?>">
		</div>
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("End");?>:
		</div>
		<div class="col m4 padding">
			<input type="datetime-local" class="input" id="edittask_end_date" value="<?php echo $end_date;?>">
		</div>
	</div>
	
	<div class="row">
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("Priority");?>:
		</div>
		<div class="col m2 padding">
			<select class="select" id="edittask_priority_id">
			<option value=""></option>
			<?php
			for($i =0; $i<$dt_priority->getRowCount(); $i++)
			{
			?>
			<option value="<?php echo $dt_priority->getString($i,"id");?>"><?php echo $dt_priority->getString($i,"name");?></option>
			<?php
			}
			?>
			</select>
		</div>
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("Status");?>:
		</div>
		<div class="col m2 padding">
			<select class="select" id="edittask_status_id">
			<option value=""></option>
			<?php
			for($i =0; $i<$dt_status->getRowCount(); $i++)
			{
			?>
			<option value="<?php echo $dt_status->getString($i,"id");?>"><?php echo $dt_status->getString($i,"name");?></option>
			<?php
			}
			?>
			</select>
		</div>
		<div class="col m2 padding">
		%:
		</div>
		<div class="col m2 padding">
			<select class="select" id="edittask_proccess_id">
			<option value=""></option>
			<?php
			for($i =0; $i<$dt_proccess->getRowCount(); $i++)
			{
			?>
			<option value="<?php echo $dt_proccess->getString($i,"id");?>"><?php echo $dt_proccess->getString($i,"name");?></option>
			<?php
			}
			?>
			</select>
		</div>
	</div>
	
	<div class="row">
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("Description");?>:
		</div>
		<div class="col m10 padding">
			<textarea id ="edittask_description" class="textarea"><?php echo $description;?></textarea>
		</div>
	</div>
	<div class="row">
		<div class="col m2 padding">
		<?php echo $appSession->getLang()->find("Resource");?>:
		</div>
		<div class="col m10 padding">
			<select class="select" id="editresource" onchange="resourceChange()">
				<option value="LINE_DEPARTMENT"><?php echo $appSession->getLang()->find("Line Department");?></option>
				<option value="HR_DEPARTMENT"><?php echo $appSession->getLang()->find("Department");?></option>
				<option value="COMPANY"><?php echo $appSession->getLang()->find("COMPANY");?></option>
				<option value="SUP_EMPLOYEE"><?php echo $appSession->getLang()->find("Supper");?></option>
				<option value="SUB_EMPLOYEE"><?php echo $appSession->getLang()->find("Staff");?></option>
			</select>
			<div id="pnAssignedList"></div>
		</div>
	</div>
	<div class="row">
		<div class="col m12 padding" id="pnResource">
		</div>
	</div>
	<?php
	$sql = "SELECT d1.id, d1.resource_id, d1.type, d1.name, d1.job_title_id, d1.job_id, d1.position_id, d3.name AS company_name, d4.name AS line_department_name, d5.name AS department_name FROM res_assign d1 LEFT OUTER JOIN hr_employee d2 ON(d1.resource_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d1.resource_id = d3.id) LEFT OUTER JOIN hr_line_department d4 ON(d1.resource_id = d4.id) LEFT OUTER JOIN hr_department d5 ON(d1.resource_id = d5.id) WHERE d1.rel_id='".$id."' AND d1.status =0 ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	?>
	<script>
	
	var assignedList = new Array();
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$id = $dt->getString($i, "id");
		$resource_id = $dt->getString($i, "resource_id");
		$type = $dt->getString($i, "type");
		$name = $dt->getString($i, "name");
		$job_title_id = $dt->getString($i, "job_title_id");
		$job_id = $dt->getString($i, "job_id");
		$position_id = $dt->getString($i, "position_id");
		
		
	?>
	assignedList.push(new Array('<?php echo $id;?>', '<?php echo $resource_id;?>', '<?php echo $appSession->getTool()->replace($name, "'", "\'");?>', '<?php echo $type;?>', '<?php echo $job_title_id;?>', '<?php echo $job_id;?>', '<?php echo $position_id;?>'));
	<?php
	}
	?>
	function resourceChange()
	{
		var ctr = document.getElementById('editresource');
		var _url = "<?php echo URL;?>admin/task/department/?ac=" + ctr.value;
		_url = _url + "&rel_id=<?php echo $rel_id;?>&func=selResource";
		loadPage("pnResource", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	resourceChange();
	function selResource(resource_id, name, type, job_title_id, job_id, position_id)
	{
		
		assignedList.push(new Array("", resource_id, name, type, job_title_id, job_id, position_id));
		printAssignedList();
	}
	function printAssignedList()
	{
		var s = "";
		for(var i =0; i<assignedList.length; i++)
		{
			if(s != "")
			{
				s = s + "<br>";
			}
			s = s + assignedList[i][2] + ' <a href="javascript:removeResource(' + i + ')"> <i class="icon-minus"> </a>';
		}
		
		document.getElementById('pnAssignedList').innerHTML = s;
	}
	function removeResource(index)
	{
		var arr = new Array();
		for(var i =0; i<assignedList.length; i++)
		{
			if(i != index)
			{
				arr.push(assignedList[i]);
			}else{
				if(assignedList[i][0] != "")
				{
					var _url = "<?php echo URL;?>admin/task/action/?ac=removeResource";
					_url = _url + "&id=" + assignedList[i][0];
					loadPage("pnResource", _url, function(status, message)
					{
						if(status== 0)
						{
							
							
						}
						
					}, true);
				}
			}
		}
		assignedList = arr;
		printAssignedList();
	}
	printAssignedList();
	</script>
<?php

}else if($ac == "addTask")
{
	$start_date = '';
	if(isset($_REQUEST['start_date']))
	{
		$start_date = $_REQUEST['start_date'];
	}
	$end_date = '';
	if(isset($_REQUEST['end_date']))
	{
		$end_date = $_REQUEST['end_date'];
	}
	$priority_id = '';
	if(isset($_REQUEST['priority_id']))
	{
		$priority_id = $_REQUEST['priority_id'];
	}
	$status_id = '';
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$proccess_id = '';
	if(isset($_REQUEST['proccess_id']))
	{
		$proccess_id = $_REQUEST['proccess_id'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
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
	$proccess = '';
	if(isset($_REQUEST['proccess']))
	{
		$proccess = $_REQUEST['proccess'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$params = '';
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	$resource = '';
	if(isset($_REQUEST['resource']))
	{
		$resource = $_REQUEST['resource'];
	}
	$task = new Task();
	echo $task->addTask($appSession, $id, $description, $type, $rel_id, $start_date, $end_date, $proccess, $parent_id, 1, 0, $rel_id, $proccess_id, $priority_id, $status_id, $params, $resource);
	
}else if($ac == "delTask")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
	$sql = "UPDATE mrp_workorder_routing SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeResource")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$task = new Task();
	echo $task->removeAssign($appSession, $id);
}else if($ac == "line")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.id, d1.description, d1.start_date, d1.end_date FROM mrp_workorder_routing d1 WHERE d1.rel_id='".$rel_id."'";
	$sql = $sql." AND d1.status =0 ORDER BY d1.sequence ASC, d1.start_date ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	?>
	<table class="table">
	<tr>
		<td width="30"></td>
		<td>Name</td>
		<td width="100">Start</td>
		<td width="100">End</td>
		<td width="30"></td>
	</tr>
	<?php
	for($i = 0; $i<count($values); $i++)
	{
		$id = $values[$i][0];
		$description = $values[$i][1];
		$start_date = $values[$i][2];
		$end_date = $values[$i][3];
		if($start_date != "")
		{
			$start_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($start_date));
		}
		if($end_date != "")
		{
			$end_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($end_date));
		}
	?>
	<tr>
		<td><a href="javascript:editTask('<?php echo $id;?>')"><i class="icon-pencil"></i></a></td>
		<td><?php echo $description;?></td>
		<td nowrap ="nowrap"><?php echo $start_date;?></td>
		<td nowrap ="nowrap"><?php echo $end_date;?></td>
		<td><a href="javascript:delTask('<?php echo $id;?>')"><i class="icon-minus"></i></a></td>
	</tr>
	<?php
	}
	?>
	</table>
	<?php
	
	
}

?>