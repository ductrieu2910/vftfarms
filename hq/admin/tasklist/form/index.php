<?php
require_once(ABSPATH.'api/Task.php' );
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
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
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT d1.id, d2.receipt_no, d2.name, d2.start_date, d2.end_date, d2.proccess_id, d2.priority_id, d2.status_id, d2.resource_id AS module_id, d1.rel_id, d2.description, d2.create_uid, d3.name AS priority_name, d4.name proccess_name, d1.proccess FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN project_priority d3 ON(d2.priority_id = d3.id) LEFT OUTER JOIN project_proccess d4 ON(d2.proccess_id = d4.id) WHERE d1.id='".$id."'";

	
	$receipt_no = "";
	$start_date = "";
	$end_date = "";
	$module_id = "";
	$task_id = "";
	$create_uid = "";
	$priority_name = "";
	$proccess_name = "";
	$proccess = "";
	$msg->add("query",$sql);
	$dt = $appSession->getTier()->getTable($msg);
	$description = "";
	$name = "";
	
	if($dt->getRowCount()>0)
	{
		
		
		$receipt_no = $dt->getString(0, "receipt_no");
		$name = $dt->getString(0, "name");
		$description = $dt->getString(0, "description");
		$start_date = $dt->getString(0, "start_date");
		$end_date = $dt->getString(0, "end_date");
		$module_id = $dt->getString(0, "module_id");
		$task_id = $dt->getString(0, "rel_id");
		$create_uid = $dt->getString(0, "create_uid");
		$assign_id = $dt->getString(0, "assign_id");
		$priority_name = $dt->getString(0, "priority_name");
		$proccess_name = $dt->getString(0, "proccess_name");
		
		$proccess = $dt->getString(0, "proccess");
		$sql = "SELECT id, create_uid FROM res_notification WHERE status =0 AND seen =0 AND rel_id='".$id."' AND create_uid='".$appSession->getUserInfo()->getId()."'";
		$msg->add("query",$sql);
		
	
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			$sql = "UPDATE res_notification SET seen =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$arr[0][0]."'";
			$msg->add("query",$sql);
			$appSession->getTier()->exec($msg);
			$to = [["id" => $arr[0][1], "type" => "user"]];
			$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification&id=".$arr[0][0]);
		}
		
	}
	$sql = "SELECT status_id FROM res_status_line d1 WHERE status =0 ORDER BY create_date DESC LIMIT 1";
	$msg->add("query",$sql);
	$current_status_id = $appSession->getTier()->getValue($msg);
	$statusList =  [];
	if($current_status_id != "" && $module_id == "" )
	{
		$sql = "SELECT d3.id, d3.name, d3.prompt, d3.proccess, d3.description, d3.forecolor, d3.backcolor FROM res_status_rel d1 LEFT OUTER JOIN res_status_rel d2 ON(d1.id = d2.rel_id AND d2.status =0) LEFT OUTER JOIN res_status d3 ON(d1.status_id = d3.id) WHERE d1.status=0 AND d1.status_id='".$current_status_id."' AND d1.rel_id='".$module_id."' AND d3.status =0 ORDER BY d3.sequence ASC";
		$msg->add("query",$sql);
		$statusList = $appSession->getTier()->getArray($msg);
	}
	if(count($statusList) == 0 && $module_id != "")
	{
		$sql = "SELECT d3.id, d3.name, d3.prompt, d3.proccess, d3.description, d3.forecolor, d3.backcolor FROM res_status_rel d1 LEFT OUTER JOIN res_status d3 ON(d1.status_id = d3.id) WHERE d1.status=0 AND d1.rel_id='".$module_id."' ORDER BY d3.sequence ASC";
		$msg->add("query",$sql);
		$statusList = $appSession->getTier()->getArray($msg);
	}
	if(count($statusList) == 0)
	{
		$sql = "SELECT d1.id, d1.name, d1.prompt, d1.proccess, d1.description, d1.forecolor, d1.backcolor FROM res_status d1 WHERE d1.company_id='ROOT' ORDER BY d1.sequence ASC";
		$msg->add("query",$sql);
		$statusList = $appSession->getTier()->getArray($msg);
	}
	
	
	
	?>
	<div class="row">
		<div class="col m2 padding">
		<?php
		if($create_uid == $appSession->getUserInfo()->getId())
		{
			?>
			<a class="btn btn-primary" href="javascript:editTaskLine('<?php echo $task_id;?>');">Edit</a>
			<?php
		}
		?>
		<a class="btn btn-default" href="javascript:todo();">Back</a>
		
		</div>
		<div class="col m10 padding right-align">
			<?php
			if($proccess != "2")
			{
			for($i =0; $i<count($statusList); $i++)
			{
			?>
			<a class="btn border" style="color:<?php echo $statusList[$i][5];?>; background-color:<?php echo $statusList[$i][6];?>"alt="<?php echo $statusList[$i][4];?>" title="<?php echo $statusList[$i][4];?>" href="javascript:resAssignStatus('<?php echo $id;?>', '<?php echo $statusList[$i][0];?>', '<?php echo $statusList[$i][2];?>', '<?php echo $statusList[$i][3];?>');"><?php echo $statusList[$i][1];?></a>
			<?php
			}
			}else{
				
			}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col m6 padding">
			#: <?php echo $receipt_no;?> - <?php echo $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($start_date));?> -> <?php echo $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($end_date));?>
		</div>
		<div class="col m6 padding right-align">
			<?php echo $priority_name;?> - <?php echo $proccess_name;?>
		</div>
	</div>
	<div class="row">
		<div class="col m12 padding">
			<b><?php echo $name;?></b><br>
			<?php echo $description;?>
		</div>
	</div>
	
	<div class="row">
		<div class="col m12 padding">
			<?php
			
			$appSession->getTool()->httpGet(URL."/admin/document/?readonly=1&rel_id=".$task_id);
			?>
		</div>
	</div>
	<br>
	<?php
	$params = "";
	if($module_id != "" && $task_id != "")
	{
		$params = $appSession->getTool()->urlEncode("id=".$task_id);
		
	?>
	<hr>
	<div class="row">
		<div class="col m12 padding" id="pnTaskRel">
			
		</div>
	</div>
	
	<?php
	}
	?>
	<script>
	function editTaskLine(id)
	{
		var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&func=taskLineChanged&id=' + id;
		openPopup(_url);
	}
	function taskLineChanged()
	{
		todo();
	}
	function loadModuleValue()
	{
		var _url = "<?php echo URL;?>admin/module/?module_id=<?php echo $module_id;?>";
		_url = _url + "&model_name=value&params=<?php echo $params;?>" ;
		_url = _url + "&root_module_id=";
		
		loadPage('pnTaskRel', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
	}
	<?php
	if($module_id != "" && $task_id != "")
	{
		
	?>
	loadModuleValue();
	<?php
	}
	?>
	</script>
<?php
}
?>