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
	$Print_Bill_With_Discount_Combine = '';
	if(isset($_REQUEST['Print_Bill_With_Discount_Combine']))
	{
		$Print_Bill_With_Discount_Combine = $_REQUEST['Print_Bill_With_Discount_Combine'];
	}
	
	
	$Print_Bill_With_Invoice = '';
	if(isset($_REQUEST['Print_Bill_With_Invoice']))
	{
		$Print_Bill_With_Invoice = $_REQUEST['Print_Bill_With_Invoice'];
	}
	$reset_sale_receipt_type = '';
	if(isset($_REQUEST['Reset_Sale_Receipt_No']))
	{
		$reset_sale_receipt_type = $_REQUEST['Reset_Sale_Receipt_No'];
	}
	$Not_Issue_Invoice_Previous_Day = '';
	if(isset($_REQUEST['Reset_Sale_Receipt_No']))
	{
		$Not_Issue_Invoice_Previous_Day = $_REQUEST['Not_Issue_Invoice_Previous_Day'];
	}
	
	$description = "UserName=".$UserName."&Password=".$Password."&Token=".$Token."&KHHDon=".$KHHDon."&Print_Bill_With_Tax=".$Print_Bill_With_Tax."&Print_Bill_With_Invoice=".$Print_Bill_With_Invoice."&Reset_Sale_Receipt_No=".$reset_sale_receipt_type."&Not_Issue_Invoice_Previous_Day=".$Not_Issue_Invoice_Previous_Day."&Print_Bill_With_Discount_Combine=".$Print_Bill_With_Discount_Combine;
	
	$sql = "UPDATE res_meta SET description='".$appSession->getTool()->replace($description, "'", "''")."', write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $description;
	
}else if($ac == "saveSaleCategoryRel")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$status = '';
	if(isset($_REQUEST['status']))
	{
		$status = $_REQUEST['status'];
	}
	$sql = "SELECT id FROM sale_category_rel WHERE category_id='".$category_id."' AND rel_id='".$rel_id."'";
	$msg->add("query", $sql);
	$id = $appSession->getTier()->getValue($msg);
	if($id == "")
	{
		$builder = $appSession->getTier()->createBuilder("sale_category_rel");
		$id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		$builder->add("rel_id", $rel_id);
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("category_id", $category_id);
		$builder->add("status", 0);
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$sql = $appSession->getTier()->getInsert($builder);
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
	$sql = "UPDATE sale_category_rel SET status =".$status." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}

?>