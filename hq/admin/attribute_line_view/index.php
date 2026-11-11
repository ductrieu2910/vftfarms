<?php

if($appSession->getUserInfo()->getId() == "")
{
	echo "Access deny";
	exit;
}
$msg = $appSession->getTier()->createMessage();
$id = "";
if(isset($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
}
$rel_ids = "";
if(isset($_REQUEST['rel_ids']))
{
	$rel_ids = $_REQUEST['rel_ids'];
}
include( ABSPATH .'admin/attribute_line/index.php' );
?>