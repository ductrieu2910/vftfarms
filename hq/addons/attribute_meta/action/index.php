<?php
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "saveSetting")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$UserName = '';
	if(isset($_REQUEST['UserName']))
	{
		$UserName = $_REQUEST['UserName'];
	}
	$Password = '';
	if(isset($_REQUEST['Password']))
	{
		$Password = $_REQUEST['Password'];
	}
	$Token = 0;
	if(isset($_REQUEST['Token']))
	{
		$Token = $_REQUEST['Token'];
	}
	$KHHDon = '';
	if(isset($_REQUEST['KHHDon']))
	{
		$KHHDon = $_REQUEST['KHHDon'];
	}
	$Print_Bill_With_Tax = '';
	if(isset($_REQUEST['Print_Bill_With_Tax']))
	{
		$Print_Bill_With_Tax = $_REQUEST['Print_Bill_With_Tax'];
	}
	$Print_Bill_With_Invoice = '';
	if(isset($_REQUEST['Print_Bill_With_Invoice']))
	{
		$Print_Bill_With_Invoice = $_REQUEST['Print_Bill_With_Invoice'];
	}
	
	
	$description = "UserName=".$UserName."&Password=".$Password."&Token=".$Token."&KHHDon=".$KHHDon."&Print_Bill_With_Tax=".$Print_Bill_With_Tax."&Print_Bill_With_Invoice=".$Print_Bill_With_Invoice;
	
	$sql = "UPDATE res_meta SET description='".$appSession->getTool()->replace($description, "'", "''")."', write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $description;
	
}

?>