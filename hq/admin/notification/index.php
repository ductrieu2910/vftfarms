<?php
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
?>
<style>
.danger {
  border-top: 1px solid #ffdddd;
  border-right: 1px solid #ffdddd;
  border-bottom: 1px solid #ffdddd;
  border-left: 6px solid #f44336;
  padding: 4px;
  margin: 4px;
}
.success {
   border-top: 1px solid #ddffdd;
  border-right: 1px solid #ddffdd;
  border-bottom: 1px solid #ddffdd;
  border-left: 6px solid #04AA6D;
  padding: 4px;
  margin: 4px;
}

.info {
  border-top: 1px solid #e7f3fe;
  border-right: 1px solid #e7f3fe;
  border-bottom: 1px solid #e7f3fe;
  border-left: 6px solid #2196F3;
  padding: 4px;
  margin: 4px;
}
.warning {
  border-top: 1px solid #ffffcc;
  border-right: 1px solid #ffffcc;
  border-bottom: 1px solid #ffffcc;
  border-left: 6px solid #ffeb3b;
  padding: 4px;
  margin: 4px;
}
</style>
<div class="row">
	<div class="col m12">
	</div>
</div>
<div class="row">
	<div class="col m12" id="pnNotificationList">
	</div>
</div>
<script>
	function loadNotification(){
		var _url = "<?php echo URL;?>admin/notification/?ac=list";
		loadPage("pnNotificationList", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	loadNotification();
	function notificationLine(rel_id, type, notification_id)
	{ 
		var _url = "<?php echo URL;?>admin/tasklist/form/?id=" + rel_id;
		loadPage("pnNotificationList", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	addListener("res_notification", function(from, att)
	{
		if(att.getProperty("name") == "table_changed" && att.getProperty("name") == "res_notification")
		{
			loadNotification();
		}
	});
	
</script>
<?php
}else if($ac == "list")
{
	$sql = "SELECT d1.description, d1.rel_id, d1.type, d1.notification_id FROM res_notification d1 WHERE d1.status =0 AND d1.seen = 0 AND d1.create_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." ORDER BY d1.create_date dESC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);

	for($i =0; $i<$dt->getRowCount(); $i++)
	{
	?>
	<div class="border card">
	<div style="width:100%; padding-right:10px"><a href="javascript:notificationLine('<?php echo $dt->getString($i, "rel_id");?>', '<?php echo $dt->getString($i, "type");?>', '<?php echo $dt->getString($i, "notification_id");?>')"><i class="icon-arrow-right"></i></a></div>
	<?php echo $dt->getString($i, "description");?></div>
	<?php
	}
	
}
?>