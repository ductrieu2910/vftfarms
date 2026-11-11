<?php
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}

if($ac == "view")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$sql = "SELECT COUNT(id) AS c FROM res_notification WHERE create_uid='".$user_id."' AND status =0 AND seen=0";
	$msg->add("query", $sql);
	echo $appSession->getTier()->getValue($msg);
}else if($ac == "list")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$path = ABSPATH."log/notification/".$user_id;
	$last_date = "";
	if(file_exists($path))
	{
		$file = fopen($path, 'r');
		if ($file)
		{
			if (($line = fgets($file)) !== false) 
			{
				$last_date = $line;
			}
		}
		fclose($file);

	}
		
	$sql = "SELECT id, name, description, rel_id, type FROM res_notification d1 WHERE create_uid='".$user_id."' AND status =0 AND seen=0";
	if($last_date != "")
	{
		$sql = $sql." AND create_date>='".$last_date."'";
	}
	$sql = $sql." ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
	$last_date = date("Y-m-d h:i:s.u");
	$f=fopen($path,'w');
	fwrite($f, $last_date);
	fclose($f);
			
	
}else if($ac == "seen")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT create_uid FROM res_notification WHERE id='".$id."'";
	$msg->add("query", $sql);
	$users = $appSession->getTier()->getArray($msg);
	$to = [];
	for($j =0; $j<count($users); $j++)
	{
		$to[count($to)]= ['id' => $users[$j][0], 'type' => 'user'];
	}
	
	$sql= "UPDATE res_notification SET seen =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	echo $appSession->getTier()->exec($msg);
	if(count($to)>0)
	{
		$appSession->getTool()->postMessageToUser($appSession, $to, "type=table_changed&name=res_notification");
	}
}

?>