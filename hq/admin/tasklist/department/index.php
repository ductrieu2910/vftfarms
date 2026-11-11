<?php
$msg = $appSession->getTier()->createMessage();
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac =="MY_DEPARTMENT")
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
	$sql = "SELECT employee_id FROM res_user_company WHERE user_id='".$appSession->getUserInfo()->getId()."' AND status =0";
	$msg->add("query", $sql);
	$employee_id = $appSession->getTier()->getValue($msg);
	$sql = "SELECT d1.rel_id FROM hr_employee_rel d1 WHERE d1.status =0 AND d1.employee_id ='".$employee_id."'";
	
	
	$msg->add("query", $sql);

	$values = $appSession->getTier()->getArray($msg);
	if(count($values) == 0)
	{
		
		return;
	}
	
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name";
	$sql = $sql." FROM hr_line_department d1";
	$sql = $sql." WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);


	function buildCategory($appSession, $lines, $parent_id, $type, $group, $value, $func)
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
			
				<a href="javascript:selDepartment('<?php echo $id;?>')"><?php echo $name;?></a> <a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_line_department', '', '', '')"><i class="icon-plus"></i></a>
				
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $lines, $id, $type, $group."-".$id, $value, $func);?></table></td>
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
			buildCategory($appSession, $lines, $root_parent_id, $type, "".$v, $value, $func);
	
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
			<div id="pnEmplyeeList"></div>
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
			var _url = '<?php echo URL;?>admin/tasklist/department/?ac=employeeByLineDepartment&tree_id=' + encodeURIComponent(ids);
			_url = _url + "&search=" + encodeURIComponent(ctr.value);
			_url = _url + "&func=<?php echo $func;?>";
			_url = _url + "&type=<?php echo $type;?>";
			_url = _url + "&rel_id=<?php echo $rel_id;?>";
			
			loadPage('pnEmplyeeList', _url, function(status, message)
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
	
}else if($ac == "LINE_DEPARTMENT")
{
	$func = "func";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name";
	$sql = $sql." FROM hr_line_department d1";
	$sql = $sql." WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	

	function buildCategory($appSession, $lines, $parent_id, $func)
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
			if( $lines[$j][1] == $parent_id )
			{
				$id = $lines[$j][0];
				$name = $lines[$j][2];
				$line += 1;
			?>
			<tr >
				
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				
				<td style="padding-left:5px; padding-top:5px">
				<?php echo $name;?> <a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_line_department', '', '', '')"><i class="icon-plus"></i></a>
				
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $lines, $id, $func);?></table></td>
			</tr>
			<?php
			}
		}
		
	}
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php
	buildCategory($appSession, $arr, "",  $func);

	?>
	</table>
	<?php
}else if($ac == "COMPANY")
{
	$func = "func";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name";
	$sql = $sql." FROM res_company d1";
	$sql = $sql." WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	

	function buildCategory($appSession, $lines, $parent_id, $func)
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
			if( $lines[$j][1] == $parent_id )
			{
				$id = $lines[$j][0];
				$name = $lines[$j][2];
				$line += 1;
			?>
			<tr >
				
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				
				<td style="padding-left:5px; padding-top:5px">
			
				 <?php echo $name;?> <a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'res_company', '', '', '')"><i class="icon-plus"></i></a>
				
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $lines, $id, $func);?></table></td>
			</tr>
			<?php
			}
		}
		
	}
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php
	buildCategory($appSession, $arr, "",  $func);

	?>
	</table>
	<?php
}else if($ac == "HR_DEPARTMENT")
{
	$func = "func";
	if(isset($_REQUEST['func']))
	{
		$func = $_REQUEST['func'];
	}
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name";
	$sql = $sql." FROM hr_department d1";
	$sql = $sql." WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$sql = $sql." ORDER BY d1.sequence ASC, d1.name ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	

	function buildCategory($appSession, $lines, $parent_id, $func)
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
			if( $lines[$j][1] == $parent_id )
			{
				$id = $lines[$j][0];
				$name = $lines[$j][2];
				$line += 1;
			?>
			<tr >
				
				<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
				
				<td style="padding-left:5px; padding-top:5px">
			
				 <a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_department', '', '', '')"><i class="icon-plus"></i></a>
				
				</td>
			  </tr>
			  <tr>
				<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
				<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($appSession, $lines, $id, $func);?></table></td>
			</tr>
			<?php
			}
		}
		
	}
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php
	buildCategory($appSession, $arr, "",  $func);

	?>
	</table>
	<?php
}else if($ac == "SUP_EMPLOYEE")
{
	$sql = "SELECT DISTINCT d2.id, d2.code, d2.first_name, d2.last_name, d2.middle_name, d2.mobile, d2.email, d2.job_title_id FROM hr_employee_report d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id)  WHERE d1.status =0 AND d2.status =0 AND d1.sup_employee_id ='".$appSession->getConfig()->getProperty("employee_id")."'";
	$msg->add("query", $sql);
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="responsive">
	  <table class="table">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="30">
			
			</th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="60"><?php echo $appSession->getLang()->find("Code");?></th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="120"><?php echo $appSession->getLang()->find("Name");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Email");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Phone");?></th>
			
		  </tr>
		</thead>
		<tbody>
			<?php
			
			for($i =0; $i<$numrows; $i++)
			{
				$row = $result[$i];
				$id = $row[0];
				$code = $row[1];
				$name = $row[2]." ".$row[3]." ".$row[4];
				
				$mobile = $row[5];
				$email = $row[6];
				$job_title_id = $row[7];
				$job_id = "";
				$position_id = "";
				$checked = "";
				if($appSession->getTool()->indexOf($value, $id) != -1)
				{
					$checked = " checked ";
				}
				
			?>
			<tr>
				<td width="30" style="text-align:center">
				<a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $code;?>. <?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_employee', '<?php echo $job_title_id;?>', '<?php echo $job_id;?>', '<?php echo $position_id;?>')"><i class="icon-plus"></i></a>
				</td>
				<td nowrap="nowrap"><?php echo $code; ?> </td>
				<td nowrap="nowrap"><?php echo $name; ?> </td>
				<td nowrap="nowrap"><?php echo $mobile; ?> </td>
				<td nowrap="nowrap"><?php echo $email; ?> </td>
				
			</tr>
			<?php
			}
			?>
		</tbody>
		</table>
	</div>
<?php
	
}else if($ac == "SUB_EMPLOYEE")
{
	$sql = "SELECT DISTINCT d2.id, d2.code, d2.first_name, d2.last_name, d2.middle_name, d2.mobile, d2.email, d2.job_title_id FROM hr_employee_report d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id)  WHERE d1.status =0 AND d2.status =0 AND d1.employee_id ='".$appSession->getConfig()->getProperty("employee_id")."'";
	$msg->add("query", $sql);
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="responsive">
	  <table class="table">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="30">
			
			</th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="60"><?php echo $appSession->getLang()->find("Code");?></th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="120"><?php echo $appSession->getLang()->find("Name");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Email");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Phone");?></th>
			
		  </tr>
		</thead>
		<tbody>
			<?php
			
			for($i =0; $i<$numrows; $i++)
			{
				$row = $result[$i];
				$id = $row[0];
				$code = $row[1];
				$name = $row[2]." ".$row[3]." ".$row[4];
				
				$mobile = $row[5];
				$email = $row[6];
				$job_title_id = $row[7];
				$job_id = "";
				$position_id = "";
				$checked = "";
				if($appSession->getTool()->indexOf($value, $id) != -1)
				{
					$checked = " checked ";
				}
				
			?>
			<tr>
				<td width="30" style="text-align:center">
				<a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $code;?>. <?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_employee', '<?php echo $job_title_id;?>', '<?php echo $job_id;?>', '<?php echo $position_id;?>')"><i class="icon-plus"></i></a>
				</td>
				<td nowrap="nowrap"><?php echo $code; ?> </td>
				<td nowrap="nowrap"><?php echo $name; ?> </td>
				<td nowrap="nowrap"><?php echo $mobile; ?> </td>
				<td nowrap="nowrap"><?php echo $email; ?> </td>
				
			</tr>
			<?php
			}
			?>
		</tbody>
		</table>
	</div>
<?php
	
}
else if($ac == "employeeByLineDepartment")
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
	$sql = "SELECT DISTINCT d2.id, d2.code, d2.first_name, d2.last_name, d2.middle_name, d2.mobile, d2.email, d1.job_title_id, d1.job_id, d1.position_id FROM hr_employee_rel d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id)  WHERE d1.status =0 AND d2.status =0";
	
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
			$ids = $ids." d1.rel_id='".$arr[$i]."'";
		}
		$sql = $sql." AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	if ($search != "")
	{
		
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d2.code", "d2.first_name","d2.last_name","d2.middle_name"] , $search) . ")";
	}
	$sql = $sql." ORDER BY d2.code ASC LIMIT 50";

	$msg->add("query", $sql);
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	
	?>
	<div class="responsive">
	  <table class="table">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="30">
			
			</th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="60"><?php echo $appSession->getLang()->find("Code");?></th>
			<th nowrap="nowrap" nowrap="nowrap" align="center" width="120"><?php echo $appSession->getLang()->find("Name");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Email");?></th>
			<th style="width:90px" nowrap="nowrap" align="center"><?php echo $appSession->getLang()->find("Phone");?></th>
			
		  </tr>
		</thead>
		<tbody>
		<?php
		
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$id = $row[0];
			$code = $row[1];
			$name = $row[2]." ".$row[3]." ".$row[4];
			
			$mobile = $row[5];
			$email = $row[6];
			$job_title_id = $row[7];
			$job_id = $row[8];
			$position_id = $row[9];
			$checked = "";
			if($appSession->getTool()->indexOf($value, $id) != -1)
			{
				$checked = " checked ";
			}
			
		?>
		<tr>
			<td width="30" style="text-align:center">
			<a href="javascript:<?php echo $func;?>('<?php echo $id; ?>', '<?php echo $code;?>. <?php echo $appSession->getTool()->replace($name, "'", "\'");?>', 'hr_employee', '<?php echo $job_title_id;?>', '<?php echo $job_id;?>', '<?php echo $position_id;?>')"><i class="icon-plus"></i></a>
			</td>
			<td nowrap="nowrap"><?php echo $code; ?> </td>
			<td nowrap="nowrap"><?php echo $name; ?> </td>
			<td nowrap="nowrap"><?php echo $mobile; ?> </td>
			<td nowrap="nowrap"><?php echo $email; ?> </td>
			
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