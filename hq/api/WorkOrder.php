<?php

class Sale
{

	
	public function __construct() {
		
	}
	public function createRoutingLine($appSession, $msg, $workorder_id, $routing_parent_id, $routing_id, $start, $end, $quantity, $description, $sequence, $code, $name)
	{
		if($quantity == "")
		{
			$quantity = 1;
		}
		if($sequence == "")
		{
			$sequence = 0;
		}
		
		
		$workorder_routing_id= $appSession->getTool()->getId();	
		$sql = "INSERT INTO mrp_workorder_routing(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_uid";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", routing_id";
		$sql = $sql.", workorder_id";
		$sql = $sql.", proccess";
		$sql = $sql.", start_date";
		$sql = $sql.", end_date";
		$sql = $sql.", description";
		$sql = $sql.", parent_id";
		$sql = $sql.", quantity";
		$sql = $sql.", sequence";
		$sql = $sql.", code";
		$sql = $sql.", name";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$workorder_routing_id."'";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$routing_id."'";
		$sql = $sql.", '".$workorder_id."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$start."'";
		$sql = $sql.", '".$end."'";
		$sql = $sql.", '".str_replace("'", "''", $description)."'";
		$sql = $sql.", '".$routing_parent_id."'";
		$sql = $sql.", ".$quantity;
		$sql = $sql.", ".$sequence;
		$sql = $sql.", '".str_replace("'", "''", $code)."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$rs = $appSession->getTier()->exec($msg);
		return $workorder_routing_id;
	}
	function createRouting($appSession, $msg, $workorder_id, $dDate, $sdate, $edate, $quantity, $routings, $parent_id, $routing_parent_id)
	{
		$numrows = count($routings);
		
		for($i =0; $i<$numrows; $i++)
		{
			if($routings[$i][1] == $parent_id)
			{
				$routing_id = $routings[$i][0];
				$description = $routings[$i][3];
				$routing_quantity = $routings[$i][5];
				$sequence = $routings[$i][6];
				$code = $routings[$i][7];
				$name = $routings[$i][8];
				$quantity_time = $routings[$i][9];
				$min_time = $routings[$i][10];
				$max_time = $routings[$i][11];
				$quantity_num1 = $routings[$i][12];
				$quantity_num2 = $routings[$i][13];
				$quantity_num3 = $routings[$i][14];
				$quantity_num4 = $routings[$i][15];
				$factor = $routings[$i][16];
				
				$sql = "SELECT quantity, unit_id, start_time, end_time FROM mrp_routing_calendar WHERE routing_id='".$routing_id."' AND status =0 ORDER BY create_date ASC";
				$msg->add("query", $sql);
				$calendar = $appSession->getTier()->getArray($msg);
				if(count($calendar) >0)
				{
					for($l =0; $l<count($calendar); $l++)
					{
						$d = $sdate;
						$times =  $calendar[$l][0];
						$unit_id = $calendar[$l][1];
						$start_time = $calendar[$l][2];
						$end_time = $calendar[$l][3];
						
						$add_type = "";
						if($unit_id == "year")
						{
							$add_type = "years";
						}else if($unit_id == "month")
						{
							$add_type = "month";
						}else if($unit_id == "week")
						{
							$add_type = "week";
						}else if($unit_id == "day")
						{
							$add_type = "days";
						}else if($unit_id == "hour")
						{
							$add_type = "hours";
						}else if($unit_id == "minute")
						{
							$add_type = "minutes";
						}else if($unit_id == "second")
						{
							$add_type = "seconds";
						}
						if($appSession->getTool()->indexOf($times, '-') == -1)
						{
							$times = "+".$times;
						}
						if($add_type != "" && is_numeric($add_type))
						{
							$times = $times." ".$add_type;
							$d = strtotime($times, $sdate);
							if($start_time == "")
							{
								$start_time = "00:01";
							}
							if($end_time == "")
							{
								$end_time = "23:59";
							}
							$start = date("Y-m-d", $d)." ".$start_time.":00";
							$end = date("Y-m-d", $d)." ".$end_time.":59";
							
							$workorder_routing_id= createRoutingLine($appSession,$workorder_id, $routing_parent_id , $routing_id, $start, $end, $routing_quantity, $description, $sequence, $code, $name) ;
					
							createRouting($appSession, $msg, $workorder_id, $dDate, $sdate, $edate, $routing_quantity, $routings, $routing_id, $workorder_routing_id);
						}
						
					}
					$d = $dDate;
					 
					while(true)
					{
						
						if($d>$edate)
						{
							break;
						}
						
						$d = strtotime("+1 days", $d);
						
						for($l =0; $l<count($calendar); $l++)
						{
							$unit_id = $calendar[$l][1];
							$start_time = $calendar[$l][2];
							$end_time = $calendar[$l][3];
							
							$nameOfDay = date('l', $d);
							if($unit_id == $nameOfDay)
							{
								if($start_time == "")
								{
									$start_time = "00:01";
								}
								if($end_time == "")
								{
									$end_time = "23:59";
								}
								$start = date("Y-m-d", $d)." ".$start_time.":00";
								$end = date("Y-m-d", $d)." ".$end_time.":59";
							
								$workorder_routing_id= createRoutingLine($appSession,$workorder_id, $routing_parent_id , $routing_id, $start, $end, $routing_quantity, $description, $sequence, $code, $name) ;
						
								createRouting($appSession, $msg, $workorder_id, $dDate, $sdate, $edate, $routing_quantity, $routings, $routing_id, $workorder_routing_id);
							}
						}
						
					}
				}else{
					$time_amount = $routings[$i][2];
					
					$unit_id = $routings[$i][4];
					
		
					if($time_amount == "" )
					{
						$time_amount = 0;
					}
					if($routing_quantity == "")
					{
						$routing_quantity = 1;
					}
					$days = rand ( 1 , 5 );
					$sdate = date("Y-m-d", $dDate);
					$start_date = strtotime($sdate. ' + '.$days.' days');
					
					
					$days = rand ( 1 , 10 );
					
					$sdate = date("Y-m-d", $start_date);
					
					$end_date = strtotime($sdate. ' + '.$days.' days');
					
					$workorder_routing_id= createRoutingLine($appSession,$workorder_id, $routing_parent_id, $routing_id, date("Y-m-d", $start_date)." 01:00:00", date("Y-m-d", $end_date)." 23:59:59", $routing_quantity, $description, $sequence, $code, $name) ;
					
					createRouting($appSession, $msg, $workorder_id, $dDate, $sdate, $edate, $routing_quantity, $routings, $routing_id, $workorder_routing_id);
				}
				
			}
		}
		
	}
	public function add($appSession, $receipt_no, $receipt_date, $start_date, $end_date, $production_id, $rel_id, $quantity, $description)
	{
		
		$msg = $appSession->getTier()->createMessage();
		$company_id = $appSession->getConfig()->getProperty("company_id");
		if($receipt_no == "")
		{
			$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "mrp_workorder");
		}
		
		
		$workorder_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("mrp_workorder");
		$builder->add("id", $workorder_id);
		$builder->add("write_uid", $user_id);
		$builder->add("production_id", $production_id);
		$builder->add("quantity", $quantity);
		$builder->add("proccess", 0);
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("receipt_no", $receipt_no);
		if($receipt_date != "")
		{
			$builder->add("receipt_date", $receipt_date);
		}else{
			$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
		}
		if($start_date != "")
		{
			$builder->add("start_date", $start_date);
		}else{
			$builder->add("start_date", $appSession->getTier()->getDateString(), 'f');
		}
		if($end_date != "")
		{
			$builder->add("end_date", $end_date);
		}else{
			$builder->add("end_date", $appSession->getTier()->getDateString(), 'f');
		}
		$builder->add("production_id", $production_id);
		$builder->add("rel_id", $rel_id);
		$builder->add("description", $description);
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $user_id);
		$builder->add("status", 0);
		$builder->add("company_id", $company_id);
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		
		$sql = "SELECT d1.id, d1.parent_id, d1.description, d1.unit_id, d1.quantity, d1.sequence, d1.code, d2.name FROM mrp_routing d1 LEFT OUTER JOIN mrp_routing_category d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND d1.production_id='".$production_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
		$sdate= strtotime($start_date);
		$edate= strtotime($end_date);
		$msg->add("query", $sql);
		$routings = $appSession->getTier()->getArray($msg);
		createRouting($appSession, $msg, $workorder_id, $sdate, $sdate, $edate, $quantity, $routings, "", "");
		echo $workorder_id;
	}
	
	
	
}

?>