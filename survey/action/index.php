<?php

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "findQuestion")
{
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", COMPANY_ID);
	$msg->add("company_id", COMPANY_ID);
	$msg->add("user_id", $appSession->getUserInfo()->getId());

	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	
	$survey_id = "";
	if(isset($_REQUEST['survey_id']))
	{
		$survey_id = $_REQUEST['survey_id'];
	}
	$log_id = "";
	if(isset($_REQUEST['log_id']))
	{
		$log_id = $_REQUEST['log_id'];
	}
	$log_line_id = "";
	if(isset($_REQUEST['log_line_id']))
	{
		$log_line_id = $_REQUEST['log_line_id'];
	}
	$sql = "SELECT d1.id, d1.question_id FROM survey_line d1 WHERE d1.status =0 AND survey_id='".$survey_id."' ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$survey_lines = $appSession->getTier()->getArray($msg);
	$log_lines = [];
	if($log_line_id != "")
	{
		$sql = "UPDATE survey_log_line SET proccess =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$log_line_id."'";
		$log_line_id = "";
		$msg->add("query", $sql);
		$i = $appSession->getTier()->exec($msg);
		
		$sql = "SELECT survey_line_id FROM survey_log_line WHERE status =0 AND log_id='".$log_id."'";
		$msg->add("query", $sql);
		$log_lines = $appSession->getTier()->getArray($msg);
		$done = true;
		for($i =0; $i<count($survey_lines); $i++)
		{
			$hasItem = false;
			
			for($j=0; $j<count($log_lines); $j++)
			{
				if($survey_lines[$i][0] == $log_lines[$j][0])
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
				$done = false;
			}
		}
		if($done)
		{
			$sql = "SELECT d1.id, d3.id, d2.category_id FROM voucher_line d1";
			$sql = $sql." LEFT OUTER JOIN voucher d2 ON(d1.voucher_id = d2.id)";
			$sql = $sql." LEFT OUTER JOIN voucher_category_rel d3 ON(d2.category_id = d3.category_id)";
			$sql = $sql." LEFT OUTER JOIN voucher_log d4 ON(d1.id = d4.voucher_line_id)";
			$sql = $sql."  WHERE  d1.status =0 AND d2.publish = 1 AND d4.id IS NULL AND d3.rel_id ='".$survey_id."' LIMIT 1";
			$msg->add("query", $sql);
			$vouchers = $appSession->getTier()->getArray($msg);
			if(count($vouchers)>0)
			{
				$builder = $appSession->getTier()->createBuilder("voucher_log");
				$builder->add("id", $appSession->getTool()->getId());
				$builder->add("status", 0);
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("company_id", COMPANY_ID);
				$builder->add("voucher_line_id", $vouchers[0][0]);
				$builder->add("rel_id", $log_id);
				$sql = $appSession->getTier()->getInsert($builder);
				$msg->add("query", $sql);
			    $i = $appSession->getTier()->exec($msg);
			}
			$sql = "UPDATE survey_log SET proccess =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$log_id."'";
			$msg->add("query", $sql);
			$i = $appSession->getTier()->exec($msg);
		}
		
	}
	$sql = "SELECT id, survey_line_id, proccess FROM survey_log_line WHERE status =0 AND log_id='".$log_id."'";
	$msg->add("query", $sql);
	$log_lines = $appSession->getTier()->getArray($msg);
	$done = true;
	for($i =0; $i<count($survey_lines); $i++)
	{
		$hasItem = false;
		
		for($j=0; $j<count($log_lines); $j++)
		{
			if($survey_lines[$i][0] == $log_lines[$j][1])
			{
				if($log_lines[$j][2] == "0")
				{
					$log_line_id = $log_lines[$j][0];
				}
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			if($log_line_id == "")
			{
				$log_line_id = $appSession->getTool()->getId(); 
				$builder = $appSession->getTier()->createBuilder("survey_log_line");
				$builder->add("id", $log_line_id);
				$builder->add("status", 0);
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("company_id", COMPANY_ID);
				$builder->add("survey_line_id", $survey_lines[$i][0]);
				$builder->add("log_id", $log_id);
				$builder->add("lang_id", $lang_id);
				$builder->add("proccess", 0);
				$sql = $appSession->getTier()->getInsert($builder);
				$msg->add("query", $sql);
				$i = $appSession->getTier()->exec($msg);
			}
			break;
		}
	}
	if($log_line_id == "")
	{
		echo "DONE";
	}else
	{
		echo $log_line_id;
	}
}else if($ac == "answer")
{
	$question_line_id = "";
	if(isset($_REQUEST['question_line_id']))
	{
		$question_line_id = $_REQUEST['question_line_id'];
	}
	$log_line_id = "";
	if(isset($_REQUEST['log_line_id']))
	{
		$log_line_id = $_REQUEST['log_line_id'];
	}
	$status = "";
	if(isset($_REQUEST['status']))
	{
		$status = $_REQUEST['status'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", COMPANY_ID);
	$msg->add("company_id", COMPANY_ID);
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	if($type == "radio")
	{
		$sql = "UPDATE survey_log_line_response SET status =1";
		$sql = $sql."  WHERE log_line_id ='".$log_line_id."'";
		$msg->add("query", $sql);
		
		$i = $appSession->getTier()->exec($msg);
	}
	
	$sql = "SELECT d1.id FROM survey_log_line_response d1";
	$sql = $sql."  WHERE d1.log_line_id ='".$log_line_id."' AND d1.question_line_id='".$question_line_id."'";
	$msg->add("query", $sql);
	$responses = $appSession->getTier()->getArray($msg);
	$response_id = '';
	if(count($responses) == 0)
	{
		$result = 0;
		$sql = "SELECT result FROM survey_question_line WHERE id='".$question_line_id."'";
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		if(count($values)>0 && $values[0][0] != "")
		{
			$result = $appSession->getTool()->toDouble($values[0][0]);
		}
		
		$response_id = $appSession->getTool()->getId(); 
		$builder = $appSession->getTier()->createBuilder("survey_log_line_response");
		$builder->add("id", $response_id);
		$builder->add("status", 0);
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("company_id", COMPANY_ID);
		$builder->add("log_line_id", $log_line_id);
		$builder->add("question_line_id", $question_line_id);
		$builder->add("result", $result);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$i = $appSession->getTier()->exec($msg);
	}else{
		$response_id = $responses[0][0];
	}
	$sql = "UPDATE survey_log_line_response SET status =".$status.", description='".$appSession->getTool()->replace($description, "'", "''") ."',  write_date=".$appSession->getTier()->getDateString()." WHERE id='".$response_id."'";
	$msg->add("query", $sql);
	$i = $appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "checkResponse")
{

	$log_line_id = "";
	if(isset($_REQUEST['log_line_id']))
	{
		$log_line_id = $_REQUEST['log_line_id'];
	}
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", COMPANY_ID);
	$msg->add("company_id", COMPANY_ID);
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$sql = "SELECT d1.id FROM survey_log_line_response d1";
	$sql = $sql."  WHERE d1.log_line_id ='".$log_line_id."' AND d1.status =0";
	$msg->add("query", $sql);
	$responses = $appSession->getTier()->getArray($msg);
	if(count($responses) == 0)
	{
		echo "NONE";
	}else{
		echo "OK:".count($responses);
	}
	
}
?>