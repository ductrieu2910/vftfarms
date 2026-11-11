<?php
validUser($appSession);
require_once(ABSPATH.'api/Sale.php' );
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "addPayment")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$payment_id = '';
	if(isset($_REQUEST['payment_id']))
	{
		$payment_id = $_REQUEST['payment_id'];
	}
	$currency_id = '';
	if(isset($_REQUEST['currency_id']))
	{
		$currency_id = $_REQUEST['currency_id'];
	}
	$amount = 0;
	if(isset($_REQUEST['amount']))
	{
		$amount = $_REQUEST['amount'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$sale = new Sale($appSession);
	echo $sale->addPayment($rel_id, $payment_id, $currency_id, $amount, $description);
	
	
}else if($ac == "updateProductQuantity")
{
	$quantity = '';
	if(isset($_REQUEST['quantity']))
	{
		$quantity = $_REQUEST['quantity'];
	}
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	function updateChildQuantity($appSession, $parent_id, $quantity)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT id FROM sale_product_local WHERE parent_id='".$parent_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($values); $i++)
		{
			$id = $values[$i][0];
			$sql = "UPDATE sale_product_local SET quantity=".$quantity.", write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			updateChildQuantity($appSession, $id, $quantity);
		}
	}
	$sql = "UPDATE sale_product_local SET quantity=".$quantity.", write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	updateChildQuantity($appSession, $id, $quantity);
	echo "OK";
}else if($ac == "delPayment")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
	$sql = "UPDATE account_payment_line_local SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	
	echo "OK";
}

?>