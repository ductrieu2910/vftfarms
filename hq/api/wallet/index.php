<?php
require_once(ABSPATH.'app/barcode/autoload.php' );
require_once(ABSPATH.'app/phpqrcode/qrlib.php' );

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$msg = $appSession->getTier()->createMessage();
if($ac == "wallet"){
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$sql= "SELECT id FROM wallet_holder WHERE rel_id ='".$rel_id."' AND status =0";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount() == 0)
	{
		$code = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "wallet_holder");
		$code = $appSession->getTool()->paddingLeft($code, "0", 6);
			
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		$holder_id = $appSession->getTool()->getId();
		$sql = "INSERT INTO wallet_holder(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql.", code";
		$sql = $sql.", category_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$holder_id."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.", '".$appSession->getTool()->paddingLeft($code, '0', 6)."'";
		$sql = $sql.", '2056b3bb-97d8-4c3d-ad3a-4a61ce80b143'";
		$sql = $sql.")";
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
		$sql = "INSERT INTO wallet_holder_rel(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", holder_id";
		$sql = $sql.", rel_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$holder_id."'";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.")";
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
		$sql = "UPDATE wallet SET holder_id ='".$holder_id."' WHERE customer_id='".$user_id."' AND (customer_id='' OR customer_id IS NULL)";
		$msg->add("query", $sql);
		//$appSession->getTier()->exec($msg);
		
	}
	
	$sql = "SELECT d2.id, d2.code, d1.holder_id, d2.rel_id AS user_id, SUM(d1.amount * d1.factor) AS amount FROM wallet d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN wallet_holder_rel d3 ON(d2.id =d3.holder_id AND d3.status=0) WHERE d1.status =0";
	if($rel_id != "")
	{
		$sql = $sql." AND d2.rel_id='".$rel_id."'";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." GROUP BY d2.id, d2.code, d1.holder_id, d3.rel_id";
	$sql = $sql." ORDER BY d2.code ASC";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);

	echo $appSession->getTool()->respTable($dt);
	
}else if($ac == "wallet_holder")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT d1.id, d1.code, d1.rel_id AS user_id FROM wallet_holder d1 WHERE d1.status =0 AND d1.rel_id='".$rel_id."'";
	$sql = $sql." ORDER BY d1.code ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "wallet_holder_rel")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	
	$sql = "SELECT d1.id,  d1.rel_id, d1.holder_id, d3.code, d3.name FROM wallet_holder_rel d1 LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN res_user d3 ON(d1.rel_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d2.rel_id='".$rel_id."'";
	$sql = $sql." ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "wallet_holder_rel_amount")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.holder_id, d3.code , SUM(d2.amount * d2.factor) AS amount, d3.currency_id FROM wallet_holder_rel d1";
	$sql = $sql." LEFT OUTER JOIN wallet d2 ON(d1.holder_id= d2.holder_id) LEFT OUTER JOIN wallet_holder d3 ON(d1.holder_id = d3.id)";
	$sql = $sql." WHERE d1.rel_id='".$rel_id."' AND d1.status=0 AND d2.status =0";
	$sql = $sql." GROUP BY d1.holder_id, d3.code, d3.currency_id";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "remove_holder")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE wallet_holder_rel SET status=1, write_date=NOW() WHERE holder_id='".$id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->exec($msg);
	
	$sql = "UPDATE wallet_holder SET status=1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->exec($msg);
	
	echo "OK";
}else if($ac == "add_holder_rel")
{
	$holder_id = "";
	if(isset($_REQUEST['holder_id']))
	{
		$holder_id = $_REQUEST['holder_id'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT d1.id FROM wallet_holder_rel d1 WHERE d1.rel_id='".$rel_id."' AND holder_id='".$holder_id."'";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0)
	{
		$id = $dt->getString(0, "id");
		$sql = "UPDATE wallet_holder_rel SET status=0, write_date=NOW() WHERE id='".$id."'";
		
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}else{
		$sql = "INSERT INTO wallet_holder_rel(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", holder_id";
		$sql = $sql.", rel_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$holder_id."'";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.")";
		
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
	echo "OK";
	
}else if($ac == "remove_holder_rel")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$sql = "UPDATE wallet_holder_rel SET status=1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->exec($msg);
	
	
	echo "OK";
}
else if($ac == "wallet_add")
{
	$holder_id = "";
	if(isset($_REQUEST['holder_id']))
	{
		$holder_id = $_REQUEST['holder_id'];
	}

	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$amount = "";
	if(isset($_REQUEST['amount']))
	{
		$amount = $_REQUEST['amount'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	if($user_id != "")
	{
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "", "wallet");
		$wallet_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("wallet");
		$builder->add("id", $wallet_id);
		$builder->add("holder_id", $holder_id);
		$builder->add("create_uid", $appSession->getUserInfo()->getId());
		$builder->add("write_uid", $appSession->getUserInfo()->getId());
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("category_id", "eaf4f9aa-42de-4d19-b957-b3d24580d39d");
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$builder->add("status", -1);
		$builder->add("customer_id", $user_id);
		$builder->add("currency_id", "23");
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("factor", 1);
		$builder->add("amount", $amount);
		$builder->add("description", $description);
		$builder->add("payment_id", "fda0dc20-341e-4ac2-cfad-888dd77ee9d1");
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		echo $wallet_id;
	}
	
	
}else if($ac == "add_wallet_by_voucher_code")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$holder_id = "";
	if(isset($_REQUEST['holder_id']))
	{
		$holder_id = $_REQUEST['holder_id'];
	}
	$customer_id = "";
	if(isset($_REQUEST['customer_id']))
	{
		$customer_id = $_REQUEST['customer_id'];
	}
	$code = "";
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$sql = "SELECT d1.id, d2.amount, d2.currency_id FROM voucher_line d1 LEFT OUTER JOIN voucher d2 ON(d1.voucher_id = d2.id) LEFT OUTER JOIN voucher_log d3 ON(d1.id = d3.voucher_line_id) WHERE d1.status =0 AND d2.status =0 AND d2.exp_date>NOW() AND d1.code='".str_ireplace("'", "''", $code)."' AND (d3.id IS NULL OR d3.status !=0) AND d2.publish=1";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0){
		$voucher_line_id = $dt->getString(0, "id");
		$amount = $dt->getString(0, "amount");
		$currency_id = $dt->getString(0, "currency_id");
		$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "", "wallet");
		$wallet_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("wallet");
		$builder->add("id", $wallet_id);
		$builder->add("holder_id", $holder_id);
		$builder->add("create_uid", $appSession->getUserInfo()->getId());
		$builder->add("write_uid", $appSession->getUserInfo()->getId());
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("category_id", "eaf4f9aa-42de-4d19-b957-b3d24580d39d");
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$builder->add("status", 0);
		$builder->add("customer_id", $customer_id);
		$builder->add("currency_id", $currency_id);
		$builder->add("receipt_no", $receipt_no);
		$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("description", "Voucher code :".$code);
		$builder->add("factor", 1);
		$builder->add("amount", $amount);
		$builder->add("payment_id", "fda0dc20-341e-4ac2-cfad-888dd77ee9d1");
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
		$builder = $appSession->getTier()->createBuilder("voucher_log");
		$builder->add("id", $appSession->getTool()->getId());
		$builder->add("rel_id", $wallet_id);
		$builder->add("voucher_line_id", $voucher_line_id);
		$builder->add("create_uid", $appSession->getUserInfo()->getId());
		$builder->add("write_uid", $appSession->getUserInfo()->getId());
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		echo "OK";
	}else{
		echo "Invalid code";
	}
	
}
?>
