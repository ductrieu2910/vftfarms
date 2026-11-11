<?php
validUser($appSession);
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
if($ac == "removeRequestProduct"){
	

	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE purchase_request_product SET status =1, write_date=NOW() WHERE id ='".$id."'";
	$msg->add("query", $sql);
	$status = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>