<?php

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac == "view")
{
	$func = "selUser";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$value = "";
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$root_parent_id = "";
	if(DEPARTMENT_STRUCTURE == "VICB")
	{
		function findChild($arr, $parent_id)
		{
			$vec = [];
			for($i=0; $i<count($arr); $i++)
			{
				if($arr[$i][1] == $parent_id)
				{
					$id = $arr[$i][0];
					$vec[count($vec)] = $id ;
					$vec1 = findChild($arr, $id);
					for($j=0; $j<count($vec1); $j++)
					{
						$vec[count($vec)] = $vec1[$j] ;
					}
				}
			}
			return $vec;
		}
		function findParent($arr, $id)
		{
			for($i=0; $i<count($arr); $i++)
			{
				if($arr[$i][0] == $id)
				{
					return $arr[$i][1];
				}
			}
			return "";
		}
		function isChild($arr, $parent_id, $child_id)
		{
			for($i=0; $i<count($arr); $i++)
			{
				if($arr[$i][1] == $parent_id)
				{
					$id = $arr[$i][0];
					if($id == $child_id)
					{
						return true;
					}
					$b = isChild($arr, $id, $child_id);
					if($b == true)
					{
						return true;
					}
				}
			}
			return false;
		}
		
		$sql = "SELECT DISTINCT d1.department_id FROM hr_department_employee d1 LEFT OUTER JOIN res_user_company d2 ON(d1.employee_id = d2.employee_id) LEFT OUTER JOIN module_category_structure d3 ON(d1.department_id = d3.id) WHERE d1.status =0 AND d3.status =0 AND d2.user_id='".$appSession->getUserInfo()->getId()."' AND d2.status =0";
		
		
		$msg->add("query", $sql);
	
		$values = $appSession->getTier()->getArray($msg);
		
		
		$sql = "SELECT d1.id, d1.parent_id, d2.category_name";
		$sql = $sql." FROM module_category_structure d1";
		$sql = $sql." LEFT OUTER JOIN module_category d2 ON(d1.category_id = d2.id)";
		$sql = $sql." WHERE d1.status =0";
		$sql = $sql." ORDER BY d1.sequence ASC, d2.category_name ASC";
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
	
	
		function buildCategory($appSession, $lines, $parent_id, $type, $group, $value)
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
			
					
					if($lines[$j][0] != "" && $lines[$j][1] == $parent_id )
					{
						$id = $lines[$j][0];
						$name = $lines[$j][2];
						$line += 1;
				?>
				<tr >
					
					<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
					
					<td style="padding-left:5px; padding-top:5px">
					<?php 
					if($type == "checkbox")
					{
						$checked = "";
						if($appSession->getTool()->indexOf($value, $id) != -1)
						{
							$checked = " checked ";
						}
					?>
					<input type="checkbox" <?php echo $checked;?> name="department[]" id="<?php echo $group."-".$id; ?>" class="checkbox" value="<?php echo $id;?>"  onclick="depart(this)"> <a href="javascript:selDepartment('<?php echo $id;?>')"><?php echo $name;?></a>
					<?php
					}
					else{
					?>
					<a href="javascript:selDepartment('<?php echo $id;?>')"><?php echo $name;?></a>
					<?php
					}
					?>
					</td>
				  </tr>
				  <tr>
					<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
					<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $lines, $id, $type, $group."-".$id, $value);?></table></td>
				</tr>
				<?php
					
				}
			}
			
		}
		?>
		<div class="row">
			<div class="col m4 padding" style="height:400px; overflow: auto;">
				<?php
				for($v =0; $v<count($values); $v++)
				{
					if($v>0)
					{
						echo "<hr>";
					}
					$root_parent_id =  $values[$v][0];
					
					$lines = [];
					if($type == "checkbox")
					{
						
						
						$vec = [];
						
					
						
			
						$vec = findChild($arr, $root_parent_id);
						
						$vec[count($vec)] = $root_parent_id;
						
						
						$root_parent_id = findParent($arr, $root_parent_id);
						
						for($i =0; $i<count($arr); $i++)
						{
							$row = $arr[$i];
							$id = $row[0];
							$hasItem = false;
							for($j=0; $j<count($vec); $j++)
							{
								if($vec[$j] == $id)
								{
									$hasItem = true;
									break;
								}
							}
							if($hasItem)
							{
								$arr1 = [];
								$arr1[0] = $row[0];
								$arr1[1] = $row[1];
								$arr1[2] = $row[2];
								$lines[count($lines)] = $arr1;
							}
						}
								
					}else
					{
						
						$vec = findChild($arr, $root_parent_id);
						$vec[count($vec)] = $root_parent_id;
						
						
						while(true)
						{
							$root_parent_id = findParent($arr, $root_parent_id);
							if($root_parent_id == "")
							{
								break;
							}
							$vec[count($vec)] = $root_parent_id;
						}
						for($i =0; $i<count($arr); $i++)
						{
							$row = $arr[$i];
							$id = $row[0];
							$hasItem = false;
							for($j=0; $j<count($vec); $j++)
							{
								if($vec[$j] == $id)
								{
									$hasItem = true;
									break;
								}
							}
							if($hasItem)
							{
								$arr1 = [];
								$arr1[0] = $row[0];
								$arr1[1] = $row[1];
								$arr1[2] = $row[2];
								$lines[count($lines)] = $arr1;
							}
						}
					}
					
				?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php
				if($type == "checkbox")
				{
					$checked = "";
					if($appSession->getTool()->indexOf($value, $root_parent_id) != -1)
					{
						$checked = "  ";
					}
				?>
				<input type="checkbox" <?php echo $checked;?> name="department[]" id="<?php echo $v;?>"  class="checkbox" value="<?php echo $root_parent_id;?>"  onclick="depart(this)">
				<?php
				}
				?>
				<?php 
				buildCategory($appSession, $lines, $root_parent_id, $type, "".$v, $value);
				?>
				</table>
				<?php
				}
				?>
			</div>
			<div class="col m8 padding">
				<div class="btn-group pull-right">
					<div class="input-group"><input type="text" id="tsEmployee" value="" class="input" placeholder="Search" onKeyDown="if(event.keyCode == 13){loadEmployeeList();}"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="loadEmployeeList()">Search </button></span></div>
				</div>
				<br>
				<div id="employeeList"></div>
			</div>
		</div>
		<script>
			var tree_id;
			var lines = new Array();
			<?php
			for($i =0; $i<count($arr); $i++)
			{
				if($arr[$i][0] != "")
				{
			?>
				lines.push(new Array('<?php echo $arr[$i][0];?>', '<?php echo $arr[$i][1];?>'));
			<?php
				}
			}
			?>
			function findChild(parent_id)
			{
				var s = "";
				for(var i =0; i<lines.length; i++)
				{
					if(lines[i][1] == parent_id)
					{
						if(s != '')
						{
							s += ",";
						}
						s = s + lines[i][0];
						var s1 = findChild(lines[i][0]);
						if(s1 != "")
						{
							s = s + "," + s1;
						}
					}
					
					
				}
				return s;
			}
			function selDepartment(id)
			{
				tree_id = id;
				
				var ids = findChild(id);
				if(ids != "")
				{
					ids = ids + "," + id;
				}else{
					ids = id;
				}
				
				var ctr = document.getElementById('tsEmployee');
				var _url = '<?php echo URL;?>admin/module/assigned/?ac=department&tree_id=' + encodeURIComponent(id);
				_url = _url + "&search=" + encodeURIComponent(ctr.value);
				_url = _url + "&func=<?php echo $func;?>";
				_url = _url + "&type=<?php echo $type;?>";
				_url = _url + "&rel_id=<?php echo $rel_id;?>";
				_url = _url + "&value=" + encodeURIComponent('<?php echo $value;?>');
				loadPage('employeeList', _url, function(status, message)
				{
					if(status== 0)
					{
						
						
					}
					
				}, false);
			}
			function loadEmployeeList()
			{
				selDepartment(tree_id);
			}
			<?php 
			if(count($values)>0)
			{
			?>
			selDepartment('<?php echo $values[0][0];?>');
			<?php
			}
			?>
			function depart(theInput)
			{
				var values = '';
				var checkboxes = document.getElementsByName(theInput.name);
				for (var i= 0; i<checkboxes.length; i++) {
					
					
					if(theInput.id == checkboxes[i].id.substring(0, theInput.id.length))
					{
						if(checkboxes[i].id != theInput.id)
						{
							checkboxes[i].checked = theInput.checked;
						}
						
						if(checkboxes[i].checked)
						{
							if(values != "")
							{
								values += "~";
							}
							values += checkboxes[i].value;
						}
						
					}
				}
				checkDepartment(values, '<?php echo $rel_id;?>');
				
			}
			
		</script>
		<?php
		
		
			
	}else
	{
		$root_company_id = $appSession->getConfig()->getProperty("root_company_id");
		$company_id = $appSession->getUserInfo()->getCompanyId();

		$sql = "SELECT id, parent_id, name FROM res_company WHERE status =0";
		
		$msg->add("query", $sql);

		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		$lines = [];
		$parent_id = $company_id;
		for($j =0; $j<$numrows; $j++)
		{
			$row = $result[$j];
			$arr = [];
			
			$arr[0] = $row[0];
			$arr[1] = $row[1];
			$arr[2] = $row[2];
			$lines[$j] = $arr;
		}
		$line = 0;
		for($j =0; $j<count($lines); $j++)
		{
			if($lines[$j][1] == $parent_id )
			{
				$line += 1;
			}
		}

		if($root_company_id == $company_id)
		{
			for($j =0; $j<count($lines); $j++)
			{
				if($lines[$j][0] == $root_company_id )
				{
					$parent_id = $lines[$j][1];
					break;
				}
			}
		}else{
			if($line == 0)
			{
				for($j =0; $j<count($lines); $j++)
				{
					if($lines[$j][0] == $parent_id )
					{
						$parent_id = $lines[$j][1];
						break;
					}
				}
				
			}
		}
		
		function buildCategory($company_id, $lines, $parent_id, $isFirst, $type)
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
					
					<td width="15" align="right" ><?php if($id != $company_id){?><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /><?php } ?></td>
					
					<td style="padding-left:5px; padding-top:5px">
					<?php 
					if($type == "checkbox")
					{
					?>
					<a href="javascript:selCompany('<?php echo $id;?>')"><?php echo $name;?></a>
					<?php
					}
					else{
					?>
					<a href="javascript:selCompany('<?php echo $id;?>')"><?php echo $name;?></a> <a href="javascript:selUser('<?php echo $id; ?>')"><i class="icon-plus"></i></a>
					<?php
					}
					?>
					
					
					
					</td>
				  </tr>
				  <tr>
					<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
					<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($company_id, $lines, $id, false, $type);?></table></td>
				</tr>
				<?php
					
				}
			}
			
		}
	
	
?>
<div class="row">
	<div class="col m4">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<?php buildCategory($company_id, $lines, $parent_id, true, $type);?>
		</table>
	</div>
	<div class="col m8">
		<div id="pnCompany"></div>
	</div>
</div>
<script>
	function selCompany(id)
	{
		
		var _url = '<?php echo URL;?>admin/module/assigned/?ac=company&company_id=' + id;
		_url = _url + "&func=<?php echo $func;?>";
		_url = _url + "&type=<?php echo $type;?>";
		_url = _url + "&rel_id=<?php echo $rel_id;?>";
		_url = _url + "&value=" + encodeURIComponent('<?php echo $value;?>');
		loadPage('pnCompany', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
</script>
	
<?php
	}
}else if($ac == "company")
{
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$func = "";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	$value = "";
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	
	$sql = "SELECT d2.id, d2.name, d2.email, d2.phone, d2.avatar";
	$sql = $sql." FROM res_user_company d1";
	$sql = $sql." LEFT OUTER JOIN res_user d2 ON(d1.user_id = d1.id)";
	$sql= $sql." WHERE d1.status =0 AND d2.status =0";
	
	$msg->add("query", $sql);

	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="responsive">
	  <table class="table table-bordered nobottommargin">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="30" align="center">
			<?php
			if($type == "checkbox")
			{
			?>
			<input type="checkbox" class="checkbox" value="" name="chFilter[]" onclick="doHandleCheckAll(this);<?php echo $func;?>(this, '<?php echo $rel_id;?>')">
			<?php
			}
			?>
			</th>
			<th nowrap="nowrap" align="center" width="120">Name</th>
			<th style="width:90px" nowrap="nowrap" align="center">Email</th>
			<th style="width:90px" nowrap="nowrap" align="center">Phone</th>
			
		  </tr>
		</thead>
		<tbody>
		<?php
		
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$id = $row[0];
			$name = $row[1];
			$email = $row[2];
			$phone = $row[3];
			$checked = "";
			if($appSession->getTool()->indexOf($value, $id) != -1)
			{
				$checked = " checked ";
			}
			
		?>
		<tr>
			<td width="30">
			<?php
			if($type == "checkbox")
			{
			?>
			<input type="checkbox" <?php echo $checked;?> class="checkbox" value="<?php echo $id;?>" name="chFilter[]" onclick="<?php echo $func;?>(this, '<?php echo $rel_id;?>')">
			<?php
			}else
			{
			?>
			<a href="javascript:selUser('<?php echo $id; ?>', '<?php echo $rel_id;?>')"><i class="icon-plus"></i></a>
			<?php
			}
			?></td>
			<td><?php echo $name; ?> </td>
			<td><?php echo $email; ?> </td>
			<td><?php echo $phone; ?> </td>
		</tr>
		<?php
		}
		?>
	</tbody>
</table>
</div>
<?php
}else if($ac == "department")
{
	$tree_id = "";
	if(isset($_REQUEST['tree_id']))
	{
		$tree_id = $_REQUEST['tree_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$func = "";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$value = "";
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$sql = "SELECT DISTINCT d5.user_id, d3.partner_code, d3.partner_name, d3.email, d3.phone, d3.mobile FROM hr_department_employee d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN res_partner d3 ON(d2.partner_id = d3.id) LEFT OUTER JOIN res_user_company d5 ON(d2.id = d5.employee_id AND d5.status =0)  WHERE d1.status =0 AND d2.status =0";
	
	if($tree_id != "")
	{
		$arr = $appSession->getTool()->split($tree_id, ",");
		$ids = "";
		for($i =0; $i<count($arr); $i++)
		{
			if($ids != "")
			{
				$ids = $ids." OR ";
			}
			$ids = $ids." d1.department_id='".$arr[$i]."'";
		}
		$sql = $sql." AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	if ($search != "")
	{
		
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d3.partner_code", "d3.partner_name","d3.phone","d3.mobile","d3.email"] , $search) . ")";
	}
	
	$sql = $sql." ORDER BY d3.partner_name ASC LIMIT 50";

	
	
	$msg->add("query", $sql);
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="responsive">
	  <table class="table">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="60">
			<?php
			if($type == "checkbox")
			{
			?>
			<input type="checkbox" class="checkbox" value="" name="chFilter[]" onclick="doHandleCheckAll(this);<?php echo $func;?>(this, '<?php echo $rel_id;?>')">
			<?php
			}
			?>
			</th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="60"><?php echo $appSession->getLang()->find("Code");?></th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="120"><?php echo $appSession->getLang()->find("Name");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Email");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Phone");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Mobile");?></th>
		  </tr>
		</thead>
		<tbody>
		<?php
		
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$id = $row[0];
			$code = $row[1];
			$name = $row[2];
			$email = $row[3];
			$phone = $row[4];
			$mobile = $row[5];
			$checked = "";
			if($appSession->getTool()->indexOf($value, $id) != -1)
			{
				$checked = " checked ";
			}
			
		?>
		<tr>
			<td width="60">
			<?php
			if($type == "checkbox")
			{
			?>
			<input type="checkbox" <?php echo $checked;?> class="checkbox" value="<?php echo $id;?>" name="chFilter[]" onclick="<?php echo $func;?>(this, '<?php echo $rel_id;?>')">
			<?php
			}else
			{
			?>
			<a href="javascript:selUser('<?php echo $id; ?>')"><i class="icon-plus"></i></a>
			<?php
			}
			?>
			
			</td>
			<td nowrap="nowrap"><?php echo $code; ?> </td>
			<td nowrap="nowrap"><?php echo $name; ?> </td>
			<td nowrap="nowrap"><?php echo $email; ?> </td>
			<td nowrap="nowrap"><?php echo $phone; ?> </td>
			<td nowrap="nowrap"><?php echo $mobile; ?> </td>
		</tr>
		<?php
		}
		?>
	</tbody>
</table>
</div>
<?php
}
else if($ac == "assigned_list")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$func = "";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	$sql = "SELECT create_uid FROM mrp_workorder_resource WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$create_uid = $appSession->getTier()->getValue($msg);
	if($create_uid == "")
	{
		$create_uid = $appSession->getUserInfo()->getId();
	}
	
	$sql = "SELECT d1.id, d2.name, d3.name, d4.name, d1.status_date FROM res_assign d1 LEFT OUTER JOIN res_user d2 ON(d1.resource_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d1.resource_id = d3.id) LEFT OUTER JOIN res_assign_status d4 ON(d1.status_id = d4.id) WHERE d1.status =0 AND d1.rel_id='".$rel_id."' AND (d1.create_uid='".$create_uid."' OR d1.resource_id='".$create_uid."' OR d1.resource_id='".$appSession->getUserInfo()->getCompanyId()."') ORDER BY d1.create_date ASC";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	for($j =0; $j<$numrows; $j++)
	{
		$row = $result[$j];
		if($j>0)
		{
			echo "\n";
		}
		$name = $row[1];
		if($name == "")
		{
			$name = $row[2];
		}
		$status = $row[3];
		
		if($status != "")
		{
			$status_date = $row[4];
			if($status_date != "")
			{
				$status_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($status_date));
			}
			
			$name = $name." (".$status."-".$status_date.")";
		}
		echo $row[0]."\t".$name;
	}
}else if($ac == "add_assign")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$resource_id = "";
	if(isset($_REQUEST['resource_id']))
	{
		$resource_id = $_REQUEST['resource_id'];
	}
	$sql = "INSERT INTO res_assign(";
	$sql = $sql."id";
	$sql = $sql.", create_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_uid";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", rel_id";
	$sql = $sql.", resource_id";
	$sql = $sql.", type";
	$sql = $sql.", module_id";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$resource_id."'";
	$sql = $sql.", '".$type."'";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.")";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "remove_assign")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_assign SET status=1, write_date=".$appSession->getTier()->getDateString().", write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "assign_view")
{
	$assign_id = "";
	if(isset($_REQUEST['assign_id']))
	{
		$assign_id = $_REQUEST['assign_id'];
	}
	
	$sql = "SELECT create_uid FROM res_assign WHERE id='".$assign_id."'";
	
	$msg->add("query", $sql);
	$create_uid = $appSession->getTier()->getValue($msg);
	
	$sql = "SELECT id, name FROM res_assign_status WHERE status =0";
	if($create_uid != $appSession->getUserInfo()->getId())
	{
		//$sql = $sql." AND hidden =1";
	}
	$sql = $sql." ORDER BY sequence ASC";
	$msg->add("query", $sql);

	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
		$appSession->getLang()->load($appSession->getTier(), 'form', $appSession->getConfig()->getProperty("lang_id"));
	
	?>
	<div class="row">
		<div class="col m12">
			<?php echo $appSession->getLang()->find("Description");?>:
			<textarea id="editdescription_assign_status" class="textarea"></textarea>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col m8">
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
			?>
			<a href="javascript:addStatus('<?php echo $row[0];?>', '<?php echo $assign_id;?>')" class="button button-raised"><?php echo $row[1];?></a>
			<?php
			}
			?>
		</div>
	</div>
	<hr>
	<?php
	$sql = "SELECT d1.id, d1.create_date, d1.description, d2.name, d3.user_name FROM res_assign_status_log d1 LEFT OUTER JOIN res_assign_status d2 ON(d1.status_id = d2.id) LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id) WHERE d1.status =0 AND d1.assign_id='".$assign_id."' ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	?>
	<div class="responsive">
	  <table class="table">
		<thead>
		  <tr>
			<th style="width:90px" nowrap="nowrap" align="center" width="60"><?php echo $appSession->getLang()->find("Date");?></th>
			<th style="width:90px"  nowrap="nowrap" align="center" width="120"><?php echo $appSession->getLang()->find("User");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Status");?></th>
			<th nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Description");?></th>
		  </tr>
		</thead>
		<tbody>
		<?php
		
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$id = $row[0];
			$create_date = $row[1];
			$description = $row[2];
			$status = $row[3];
			$user = $row[4];
			
			
		?>
		<tr>
			<td nowrap="nowrap" valign="middle"><?php echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($create_date)); ?></td>
			<td nowrap="nowrap" valign="middle" ><?php echo $user; ?> </td>
			<td nowrap="nowrap" valign="middle" ><?php echo $status; ?> </td>
			<td valign="middle"><?php echo $description; ?> </td>
			
		</tr>
		<?php
		}
		?>
	</tbody>
</table>
</div>
	<?php
	
}else if($ac == "add_status")
{
	$assign_id = "";
	if(isset($_REQUEST['assign_id']))
	{
		$assign_id = $_REQUEST['assign_id'];
	}
	$status_id = "";
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$sql = "INSERT INTO res_assign_status_log(";
	$sql = $sql."id";
	$sql = $sql.", create_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_uid";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", status_id";
	$sql = $sql.", assign_id";
	$sql = $sql.", description";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$status_id."'";
	$sql = $sql.", '".$assign_id."'";
	$sql = $sql.", '".$appSession->getTool()->replace($description, "'", "''")."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	$sql = "UPDATE res_assign SET status_id='".$status_id."', write_date=".$appSession->getTier()->getDateString().", status_date=".$appSession->getTier()->getDateString().", write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE id='".$assign_id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	
	
	echo "OK";
}else if($ac == "search_user")
{
	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$sql = "SELECT d1.user_id, d4.partner_code, d4.partner_name FROM res_user_company d1";
	$sql = $sql." LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id)";
	$sql = $sql." LEFT OUTER JOIN hr_employee d3 ON(d1.employee_id = d3.id)";
	$sql = $sql." LEFT OUTER JOIN res_partner d4 ON(d3.partner_id = d4.id)";
	$sql = $sql." WHERE d1.status =0 AND d3.status=0 AND (d3.terminated =0 OR d3.terminated IS NULL)";
	if($search != "")
	{
		$search = $appSession->getTool()->replace($search, "'", "''");
		$sql = $sql." AND (d4.partner_code ILIKE '%".$search."%' OR d4.partner_name ILIKE '".$search."')";
	}
	$sql = $sql." ORDER BY d4.partner_code ASC LIMIT 10";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	for($i=0; $i<$numrows; $i++)
	{
		if($i>0)
		{
			echo "\n";
		}
		echo $result[$i][0]."=".$result[$i][1]." ".$result[$i][2];
	}
}
?>