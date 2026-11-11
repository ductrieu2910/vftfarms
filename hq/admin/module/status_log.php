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
$rel_id = "";
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}


$sql = "SELECT d1.id , d2.user_name, d3.name, d1.create_date FROM ir_module_status_log d1 LEFT OUTER JOIN res_user d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN ir_module_status d3 ON(d1.status_id = d3.id) WHERE d1.status =0 AND d1.rel_id='".$rel_id."' ORDER BY d1.create_date DESC";
$result = $appSession->getTier()->getArray($sql);
	
?>

<div class="row">
	<div class="col m12">
		<div class="table-responsive">
			<table class="table table-head-custom table-vertical-center">
				<tbody>
					<?php
					for($i=0; $i<count($result); $i++)
					{
						$id = $result[$i][0];
						$user_name = $result[$i][1];
						$name = $result[$i][2];
						$create_date = $result[$i][3];
					?>
					<tr>
						<td style="width:40px"><?php echo $user_name;?></td>
						<td style="width:120px"><?php echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($create_date));?></td>
						<td><?php echo $name;?></td>
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
	