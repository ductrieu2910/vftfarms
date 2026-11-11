<?php

$type = '';
if(isset($_REQUEST['type']))
{
	$type = $_REQUEST['type'];
}
$value = '';
if(isset($_REQUEST['value']))
{
	$value = $_REQUEST['value'];
}
$location_id = '';
if(isset($_REQUEST['location_id']))
{
	$location_id = $_REQUEST['location_id'];
}
$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
$module_id = '';
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
$id = '';
if(isset($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
}

$msg = $appSession->getTier()->createMessage();
function parseValue($appSession, $msg, $value, $location_id, $rel_id)
{
	if($value == "warehouse")
	{
		$locations = [];
		
		if($location_id == "")
		{
			$sql = "SELECT d2.line_id FROM res_user_company d1 LEFT OUTER JOIN mrp_workcenter_line_rel d2 ON(d1.group_id = d2.rel_id and d2.status=0) WHERE d1.user_id ='".$appSession->getConfig()->getProperty("user_id")."' AND d2.id IS NOT NULL";
			$msg->add("query", $sql);
			
			$dt = $appSession->getTier()->getTable($msg);
			for($i =0; $i<$dt->getRowCount(); $i++)
			{
				$locations[count($locations)] = $dt->getString($i, "line_id");
			}
			if($dt->getRowCount()==0){
				$sql = "SELECT d1.line_id FROM mrp_workcenter_line_rel d1  LEFT OUTER JOIN hr_line_department d2 ON(d1.rel_id = d2.id AND d2.status =0)  LEFT OUTER JOIN hr_employee_rel d3 ON(d2.id = d3.rel_id AND d3.status =0) WHERE d3.employee_id ='".$appSession->getConfig()->getProperty("employee_id")."' AND d1.status =0 AND d3.id IS NOT NULL";
				
				$msg->add("query", $sql);
				$dt = $appSession->getTier()->getTable($msg);
				for($i =0; $i<$dt->getRowCount(); $i++)
				{
					$locations[count($locations)] = $dt->getString($i, "line_id");
				}
			}
		
			
		}else{
			$locations[count($locations)] = $location_id;
		}
		$sql = "SELECT DISTINCT d2.id, d2.name, d3.name AS company_name FROM stock_warehouse_rel d1 LEFT OUTER JOIN stock_warehouse d2 ON(d1.warehouse_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d2.company_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d1.per_read=1";
		$ids = "";
		for($i =0; $i<count($locations); $i++)
		{
			if($ids != "")
			{
				$ids = $ids." OR ";
			}
			$ids = $ids." d1.rel_id='".$locations[$i]."'";

		}
		if($ids != "")
		{
			$sql = $sql." AND (".$ids.")";
		}else{
			$sql = $sql." AND 1=0";
		}
		$sql = $sql." ORDER BY d3.name ASC, d2.name ASC";
		
		if(count($locations) == 0)
		{
			$sql = "SELECT d2.id, d2.name, d3.name AS company_name FROM stock_warehouse d2 LEFT OUTER JOIN res_company d3 ON(d2.company_id = d3.id) WHERE (d2.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d3.parent_id='".$appSession->getConfig()->getProperty("company_id")."') AND d2.status =0 AND d2.parent_id =''";
			$sql = $sql." ORDER BY d3.name ASC, d2.name ASC";
		}
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		$s = "";
		for($i = 0; $i<$dt->getRowCount(); $i++)
		{
			if($i>0){
				$s = $s.";";
			}
			$s = $s.$dt->getString($i, "id")."=".$dt->getString($i, "name")." - ".$dt->getString($i, "company_name");
		}
		return $s;
		
	}
	
	return $value;
}
if($type == "date")
{
	if($value == "NOW")
	{
		echo date("Y-m-d");
	}
}else if($type == "dropdown")
{
	echo parseValue($appSession, $msg, $value, $location_id, $rel_id);
}else if($type == "checkbox")
{
	echo parseValue($appSession, $msg, $value, $location_id, $rel_id);
	
	
	
}else if($type == "radio")
{
	echo parseValue($appSession, $msg, $value, $location_id, $rel_id);
	
	
	
}


?>