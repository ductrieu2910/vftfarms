<?php
require_once(ABSPATH.'api/Report.php' );
require_once(ABSPATH.'app/AppConfig.php' );
require_once(ABSPATH.'api/ResAttribute.php' );
$action_id = '';
if(isset($_REQUEST['action_id']))
{
	$action_id = $_REQUEST['action_id'];
}
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}

$msg = $appSession->getTier()->createMessage();
if($ac == "view")
{
	$view_type = '';
	if(isset($_REQUEST['view_type']))
	{
		$view_type = $_REQUEST['view_type'];
	}
	if($view_type != "")
	{
		$view_name = '';
		if(isset($_REQUEST['view_name']))
		{
			$view_name = $_REQUEST['view_name'];
		}
		if($view_name == "")
		{
			$view_name = $action_id;
		}
		if($view_type == "excel")
		{
			header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
			header("Content-type:   application/x-msexcel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$view_name.".xls"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
		}
		else if($view_type == "word")
		{
			header("Content-Type:   application/vnd.ms-word; charset=utf-8");
			header("Content-type:   application/x-msexcel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$view_name.".doc"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
		}
		
	}
	
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = '';
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$report_id	 = '';
	if(isset($_REQUEST['report_id']))
	{
		$report_id = $_REQUEST['report_id'];
	}
	
	
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	
	$sql = "SELECT d1.id, d1.resource FROM res_resource d1 WHERE d1.rel_id='".$action_id."' AND d1.status =0";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if(count($arr)>0)
	{
		$resource_id = $arr[0][0];
		$resource = $arr[0][1];
		$path = ABSPATH."log/".$resource_id.".php";
		
		$myfile = fopen($path, "w");
		fwrite($myfile, $resource);
		fclose($myfile);
		include($path);
		
	}
}else if($ac == "check_param")
{
	$sql = "SELECT d1.id FROM ir_action_param d1 WHERE d1.ir_id='".$action_id."' AND d1.status =0 ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if( count($arr)>0)
	{
		echo "OK";
	}else if( count($arr) == 0)
	{
		echo "NO";
	}
	
}else if($ac == "report")
{
	
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = '';
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$db_id = '';
	if(isset($_REQUEST['db_id']))
	{
		$db_id = $_REQUEST['db_id'];
	}
	$report_id	 = '';
	if(isset($_REQUEST['report_id']))
	{
		$report_id = $_REQUEST['report_id'];
	}
	$lang_id = $appSession->getConfig()->getProperty("lang_id");
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	
	$sql = "SELECT d1.id, d1.resource FROM res_resource d1  WHERE d1.id='".$action_id."' AND d1.status =0";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if(count($arr)>0)
	{
		$resource_id = $arr[0][0];
		$resource = $arr[0][1];
		$path = ABSPATH."log/".$resource_id.".php";
		
		
		file_put_contents($path, $resource);
		
		require_once($path);
		
	}
}else if($ac == "report_action")
{

	
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$company_id = '';
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$db_id = '';
	if(isset($_REQUEST['db_id']))
	{
		$db_id = $_REQUEST['db_id'];
	}
	$report_id	 = '';
	if(isset($_REQUEST['report_id']))
	{
		$report_id = $_REQUEST['report_id'];
	}
	$lang_id = $appSession->getConfig()->getProperty("lang_id");
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
		
	}
	
	$sql = "SELECT d1.id, d1.resource, d2.name FROM res_resource d1 LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id) WHERE d1.rel_id='".$action_id."' AND d1.status =0";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);
	if(count($arr)>0)
	{
		$resource_id = $arr[0][0];
		$resource = $arr[0][1];
		$action_name = $arr[0][2];
		if($type == "excel")
		{
			header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
			header("Content-type:   application/x-msexcel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$action_name.".xls"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
		}if($type == "word"){
			header("Content-Type:   application/vnd.ms-word; charset=utf-8");
			header("Content-type:   application/x-msexcel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$action_name.".doc"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
		}
		$path = ABSPATH."log/".$resource_id.".php";
		echo "<html>";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<link rel="stylesheet" href="'.URL.'assets/report/css.css">';
		echo '<body>';
		file_put_contents($path, $resource);
		require_once($path);
		echo '</body>';
		echo '</html>';
		
		
	}
}else if($ac == "resource")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$resource = '';
	if(isset($_REQUEST['resource']))
	{
		$resource = $_REQUEST['resource'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	
	$path = ABSPATH."log/".$id.".php";
	$myfile = fopen($path, "w");
	fwrite($myfile, $resource);
	fclose($myfile);
	require_once($path);
}


?>