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
	if(DEPARTMENT_STRUCTURE == "VICB")
	{
		$sql = "SELECT DISTINCT d1.department_id FROM hr_department_employee d1 LEFT OUTER JOIN res_user_company d2 ON(d1.employee_id = d2.employee_id) WHERE d1.status =0 AND d2.user_id='".$appSession->getUserInfo()->getId()."' AND d2.status =0";
		
		$msg->add("query", $sql);
	
		$values = $appSession->getTier()->getArray($msg);
		
		$vec = [];
		for($i=0; $i<count($values); $i++)
		{
			$parent_id = $values[$i][0];
			$vec[count($vec)] = $parent_id;
			while(true)
			{
				$sql = "SELECT parent_id FROM module_category_structure WHERE id ='".$parent_id."'";
				$msg->add("query", $sql);
				$parent_id = $appSession->getTier()->getValue($msg);
				if($parent_id == "")
				{
					break;
				}
				$vec[count($vec)] = $parent_id;
			}
		}
		for($i=0; $i<count($values); $i++)
		{
			$sql = "SELECT id FROM module_category_structure WHERE parent_id ='".$values[$i][0]."'";
			$msg->add("query", $sql);
			$arr = $appSession->getTier()->getArray($msg);
			for($j =0; $j<count($arr); $j++)
			{
				$vec[count($vec)] = $arr[$j][0];
			}
				
		}
		
		$ids = "";
		for($i=0; $i<count($vec); $i++)
		{
			if($ids != "")
			{
				$ids = $ids." OR ";
			}
			$ids = $ids." d1.id ='".$vec[$i]."'";
		}
		if($ids == "")
		{
			$ids = "1=0";
		}
			
		$sql = "SELECT d1.id, d1.parent_id, d2.category_name";
		$sql = $sql." FROM module_category_structure d1";
		$sql = $sql." LEFT OUTER JOIN module_category d2 ON(d1.category_id = d2.id)";
		$sql = $sql." WHERE d1.status =0 AND (".$ids.")";
		$sql = $sql." ORDER BY d1.sequence ASC, d2.category_name ASC";
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
		
		function buildCategory($lines, $parent_id)
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
					
					<td style="padding-left:5px; padding-top:5px"><a href="javascript:selDepartment('<?php echo $id;?>', '<?php echo $name;?>')"><?php echo $name;?></a>
					</td>
				  </tr>
				  <tr>
					<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
					<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($lines, $id);?></table></td>
				</tr>
				<?php
					
				}
			}
			
		}
		?>
		<div class="row">
			<div class="col m4 padding" style="height:400px; overflow: auto;">
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php buildCategory($lines, "");?>
				</table>
			</div>
			<div class="col m8 padding">
				<div class="btn-group pull-right">
				<div class="input-group"><input type="text" id="tsEmployee" value="" class="input" placeholder="Search" onKeyDown="if(event.keyCode == 13){loadEmployeeList();}"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="loadEmployeeList()">Search </button></span></div>
			</div>
				<div id="employeeList"></div>
			</div>
		</div>
		<script>
			var tree_id;
			function selDepartment(id)
			{
				tree_id = id;
				var ctr = document.getElementById('tsEmployee');
				var _url = '<?php echo URL;?>admin/module/user/?ac=department&tree_id=' + id;
				_url = _url + "&search=" + encodeURIComponent(ctr.value);
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
		
		function buildCategory($company_id, $lines, $parent_id, $isFirst)
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
					
					<td style="padding-left:5px; padding-top:5px"><a href="javascript:selCompany('<?php echo $id;?>', '<?php echo $name;?>')"><?php echo $name;?></a> <a href="javascript:selUser('<?php echo $id; ?>')"><i class="icon-plus"></i></a>
					</td>
				  </tr>
				  <tr>
					<td width="15" <?php if($line<$count){?> style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php } ?> ></td>
					<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($company_id, $lines, $id, false);?></table></td>
				</tr>
				<?php
					
				}
			}
			
		}
	
	
?>
<div class="row">
	<div class="col m4">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<?php buildCategory($company_id, $lines, $parent_id, true);?>
		</table>
	</div>
	<div class="col m8">
		<div id="pnCompany"></div>
	</div>
</div>
<script>
	function selCompany(id)
	{
		
		var _url = '<?php echo URL;?>admin/module/user/?ac=company&company_id=' + id;
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
			<th nowrap="nowrap" width="30"></th>
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
			
		?>
		<tr>
			<td width="30"><a href="javascript:selUser('<?php echo $id; ?>', '<?php echo $name;?>')"><i class="icon-plus"></i></a></td>
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
	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$sql = "SELECT DISTINCT d5.user_id, d3.partner_code, d3.partner_name, d3.email, d3.phone, d3.mobile FROM hr_department_employee d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN res_partner d3 ON(d2.partner_id = d3.id) LEFT OUTER JOIN res_user_company d5 ON(d2.id = d5.employee_id AND d5.status =0)  WHERE d1.status =0 AND d2.status =0";
	
	if($tree_id != "")
	{
		$sql = $sql." AND d1.department_id='".$tree_id."'";
	}else{
		$sql = $sql." AND 1=0";
	}
	if ($search != "")
	{
		
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d3.partner_code", "d3.partner_name","d3.phone","d3.mobile","d3.email"] , $search) . ")";
	}
	
	$sql = $sql." ORDER BY d3.partner_name ASC LIMIT 50";

	
	
	$msg->add("query", $sql);

	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="responsive">
	  <table class="table table-bordered nobottommargin">
		<thead>
		  <tr>
			<th nowrap="nowrap" width="30"></th>
			<th nowrap="nowrap" align="center" width="60">Code</th>
			<th nowrap="nowrap" align="center" width="120">Name</th>
			<th style="width:90px" nowrap="nowrap" align="center">Email</th>
			<th style="width:90px" nowrap="nowrap" align="center">Phone</th>
			<th style="width:90px" nowrap="nowrap" align="center">Mobile</th>
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
			
		?>
		<tr>
			<td width="30"><a href="javascript:selUser('<?php echo $id; ?>', '<?php echo $name;?>')"><i class="icon-plus"></i></a></td>
			<td><?php echo $code; ?> </td>
			<td><?php echo $name; ?> </td>
			<td><?php echo $email; ?> </td>
			<td><?php echo $phone; ?> </td>
			<td><?php echo $mobile; ?> </td>
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