<?php

require_once(ABSPATH.'config.php' );

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$module_id = "";
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
$company_id = "";
if(isset($_REQUEST['company_id']))
{
	$company_id = $_REQUEST['company_id'];
}

 if($ac == "add")
{
	
	
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sequence = 1;
	$sql = "SELECT MAX(sequence) FROM ir_module_status WHERE module_id='".$module_id."' AND company_id='".$company_id."' AND status =0";
	$sValue = $appSession->getTier()->getValue($sql);
	if($sValue != "")
	{
		$sequence = $appSession->getTool()->toDouble($sValue);
		$sequence = $sequence + 1;
	}
	$id = $appSession->getTool()->getId();
	$sql = "INSERT INTO ir_module_status(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", module_id";
	$sql = $sql.", sequence";
	$sql = $sql.", name";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$company_id."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$module_id."'";
	$sql = $sql.", ".$sequence;
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.")";
	$r = $appSession->getTier()->exec($sql);
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_module_status SET write_date=NOW()";
	$sql = $sql.", status =1 WHERE id='".$id."'";
	$result = $appSession->getTier()->exec($sql);
}

$sql = "SELECT d1.id, d1.name, d1.sequence FROM ir_module_status d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.company_id='".$company_id."' ORDER BY d1.sequence ASC, d1.name ASC";
$result = $appSession->getTier()->getArray($sql);
	
?>
<div class="row">
	
	<div class="col-12">
		<div class="input-group-append">
		<input type="text" id ="editstatus_name" class="form-control">
		<a class="btn" href="javascript:add()"><img src="<?php echo URL;?>assets/images/add.png"/></a>
		</div>
	</div>
	
</div>
<br>
<div class="row">
	<div class="col m12">
		<div class="table-responsive">
			<table class="table table-head-custom table-vertical-center">
				<tbody>
					<?php
					for($i=0; $i<count($result); $i++)
					{
						$id = $result[$i][0];
						$name = $result[$i][1];
						$sequence = $result[$i][2];
					?>
					<tr>
						<td><input type="text"  class="form-control" maxlength="150" value="<?php echo $name;?>" onblur="saveLine('ir_module_status', 'name', '', '<?php echo $id;?>', this, function(status, message){})"></td>
						<td style="width: 80px"><input type="number" class="form-control" value="<?php echo $sequence;?>" onblur="saveLine('ir_module_status', 'sequence', 'n', '<?php echo $id;?>', this, function(status, message){})"></td>
						<td style="width: 20px"><a class="btn" href="javascript:del('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/images/remove.png"/></a></td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	
	
	
	function del(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>addons/module/status.php?ac=del&module_id=<?php echo $module_id;?>&company_id=<?php echo $company_id;?>';
		_url = _url + '&id=' + id;
	
		
		loadPage('fullDialogBody', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	function add()
	{
		
	
		
		var ctr = document.getElementById('editstatus_name');
		
		var name = ctr.value;
		var _url = '<?php echo URL;?>addons/module/status.php?ac=add&module_id=<?php echo $module_id;?>&company_id=<?php echo $company_id;?>&name=' + encodeURIComponent(name);
		loadPage('fullDialogBody', _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	
</script>
	