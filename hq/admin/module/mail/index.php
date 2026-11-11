<?php
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac == "send")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$from_account_id = "";
	if(isset($_REQUEST['from_account_id']))
	{
		$from_account_id = $_REQUEST['from_account_id'];
	}
	$category_id = "";
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}
	$mail_to = "";
	if(isset($_REQUEST['mail_to']))
	{
		$mail_to = $_REQUEST['mail_to'];
	}
	$cc = "";
	if(isset($_REQUEST['cc']))
	{
		$cc = $_REQUEST['cc'];
	}
	$bcc = "";
	if(isset($_REQUEST['bcc']))
	{
		$bcc = $_REQUEST['bcc'];
	}
	$subject = "";
	if(isset($_REQUEST['subject']))
	{
		$subject = $_REQUEST['subject'];
	}
	$mail_from = "";
	if(isset($_REQUEST['mail_from']))
	{
		$mail_from = $_REQUEST['mail_from'];
	}
	
	$body = "";
	if(isset($_REQUEST['body']))
	{
		$body = $_REQUEST['body'];
	}
	
	$comment_id = $appSession->getTool()->getId();
	
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$sql = "INSERT INTO mail_message(";
	$sql = $sql."id";
	$sql = $sql.", rel_id";
	$sql = $sql.", category_id";
	$sql = $sql.", is_html";
	$sql = $sql.", from_account_id";
	$sql = $sql.", send";
	$sql = $sql.", create_uid";
	$sql = $sql.", write_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", subject";
	$sql = $sql.", body";
	$sql = $sql.", mail_to";
	$sql = $sql.", cc";
	$sql = $sql.", bcc";
	$sql = $sql.", mail_from";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$category_id."'";
	$sql = $sql.", 1";
	$sql = $sql.", '".$from_account_id."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$appSession->getTool()->replace($subject, "'", "''")."'";
	$sql = $sql.", '".$appSession->getTool()->replace($body, "'", "''")."'";
	$sql = $sql.", '".$appSession->getTool()->replace($mail_to, "'", "''")."'";
	$sql = $sql.", '".$appSession->getTool()->replace($cc, "'", "''")."'";
	$sql = $sql.", '".$appSession->getTool()->replace($bcc, "'", "''")."'";
	$sql = $sql.", '".$appSession->getTool()->replace($mail_from, "'", "''")."'";
	$sql = $sql.")";
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";

		
}

?>