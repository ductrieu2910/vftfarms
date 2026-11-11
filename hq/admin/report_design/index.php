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
	$rel_id = "";
	$arr_params = explode("&", $params);
	for($i =0; $i<count($arr_params); $i++)
	{
		$arr = explode("=", $arr_params[$i]);
		if(count($arr)>1)
		{
			if($arr[0] == "rel_id")
			{
				$rel_id = $arr[1];
			}
		}
	}
	$sql = "SELECT resource FROM res_resource WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$resource = $appSession->getTier()->getValue($msg);
	?>
	<div class="row">
		<div class="col m12" >
			<button onclick="saveResource()" class="button btn-primary"><?php echo $appSession->getLang()->find("Save");?></button>
		</div>
	</div>
	
	<div class="row" style="padding-top:10px">
		<div class="col m12" >
			<textarea id="editresource" style="width:100%" rows="30"><?php echo $resource;?></textarea>
		</div>
	</div>
	<script>
		function saveResource()
		{
			var ctr = document.getElementById('editresource');
			var resource = encodeURIComponent(ctr.value);
			var _url = '<?php echo URL;?>admin/report_design/?ac=save';
			_url = _url + '&rel_id=<?php echo $rel_id;?>';
			_url = _url + '&resource=' + resource;
			loadPage('pnRoutingLine', _url, function(status, message)
			{
				if(message == "OK")
				{
					alert("Resouce is saved");
				}else
				{
					alert(message);
				}
				
			}, true);
		}
	</script>
	
	<?php
?>
<?php
}else if($ac == "save")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$resource = "";
	if(isset($_REQUEST['resource']))
	{
		$resource = $_REQUEST['resource'];
	}
	$sql = "SELECT id FROM res_resource WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$id = $appSession->getTier()->getValue($msg);
	
	if($id == "")
	{
		$id = $rel_id;
		$sql = "INSERT INTO res_resource(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_uid";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		
	}
	$sql = "UPDATE res_resource SET write_date=".$appSession->getTier()->getDateString();
	$sql = $sql.", resource='".$appSession->getTool()->replace($resource, "'", "''")."'";
	$sql = $sql.", write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>