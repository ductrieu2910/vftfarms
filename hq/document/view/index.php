<?php
$document_id = '';
if(isset($_REQUEST['id']))
{
	$document_id = $_REQUEST['id'];
}
$path = '';
if(isset($_REQUEST['path']))
{
	$path = $_REQUEST['path'];
}
$ext = '';
if(isset($_REQUEST['ext']))
{
	$ext = $_REQUEST['ext'];
}
$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
if($rel_id != "")
{
	$sql = "SELECt id, path, ext FROM document WHERE rel_id ='".$rel_id."'";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		if($i>0)
		{
			echo "<br>";
		}
		$document_id = $values[$i][0];
		$path = $values[$i][1];
		$ext = $values[$i][2];
	?>
	<img src="<?php echo URL;?>document/?id=<?php echo $document_id;?>&path=<?php echo $path;?>&ext=<?php echo $ext;?>"/>
	<?php
	}
}else
{
?>
<img src="<?php echo URL;?>document/?id=<?php echo $document_id;?>&path=<?php echo $path;?>&ext=<?php echo $ext;?>"/>
<?php
}
?>