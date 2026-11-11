<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "createRequest")
{
	$planning_id = '';
	if(isset($_REQUEST['planning_id']))
	{
		$planning_id = $_REQUEST['planning_id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "purchase_request");
	$builder = $appSession->getTier()->createBuilder("purchase_request");
	$request_id = $appSession->getTool()->getId();
	$builder->add("id", $request_id);
	$builder->add("rel_id", $planning_id);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("receipt_no", $receipt_no);
	$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);
	
	$arr = $appSession->getTool()->split($data, ';');
	for($i =0; $i<count($arr); $i++)
	{
		$builder->clear();
		$builder->setName("purchase_request_product");
		
		$items = $appSession->getTool()->split($arr[$i], ',');
		if(count($items)>4)
		{
			$builder->add("id", $appSession->getTool()->getId());
			$builder->add("request_id", $request_id);
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("product_id", $items[0]);
			$builder->add("unit_id", $items[1]);
			$builder->add("attribute_id", $items[2]);
			$builder->add("type_id", $items[3]);
			$builder->add("quantity", $items[4]);
			if(count($items)>5)
			{
				$builder->add("unit_in_stock", $items[5]);
			}
			
			$builder->add("status", 0);
			$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
			$sql = $sql."; ".$appSession->getTier()->getInsert($builder);
		}
		
	}
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
	
}

?>