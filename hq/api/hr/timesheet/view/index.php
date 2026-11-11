<?php
$log_id = '';
if(isset($_REQUEST['id']))
{
	$log_id = $_REQUEST['id'];
}
$msg = $appSession->getTier()->createMessage();
if($log_id != "")
{
	$sql = "SELECT d1.start_date, d2.name, d3.id as document_id, d3.path, d3.ext FROM mrp_workcenter_line_log d1 LEFt OUTER JOIN hr_timesheet_resource d2 ON(d1.session_id = d2.id) LEFT OUTER JOIN document d3 ON(d2.id = d3.rel_id) WHERE d1.id='".$log_id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0)
	{
		
	?>
	<html>
	 <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	 <title></title>
	 <body>
	 <div style="text-align:center">
	 <?php
	 for($i =0; $i<$dt->getRowCount(); $i++)
	 {
		 $name = $dt->getString($i, "name");
		 $start_date = $dt->getString($i, "start_date");
		 $document_id = $dt->getString($i, "document_id");
		 $path = $dt->getString($i, "path");
		 $ext = $dt->getString($i, "ext");
		 ?>
		 
		 <h1><?php echo $name;?></h1>
		 <?php echo $start_date;?>
		 <br>
		 <?php
		 if($document_id != "")
		 {
			?>
			<img src="<?php echo URL;?>document/?id=<?php echo $document_id;?>&path=<?php echo $path;?>&ext=<?php echo $ext;?>"/>
			<?php
		 }
	 }
	 ?>
	 </div>
	 </body>
	</html>
	<?php
	}
	
}

?>