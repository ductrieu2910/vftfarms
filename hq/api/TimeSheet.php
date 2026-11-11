<?php
class TimeSheet
{
	public $timeList = [];
	public $employee_id = "";
	public function load($appSession, $salary_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT d1.id, d1.employee_id, d1.start_date, d1.shift_day, d1.time_amount, d1.ot_amount, d3.code";
		$sql = $sql." FROM hr_timesheet_time d1";
		$sql = $sql." LEFT OUTER JOIN hr_payroll_salary_timesheet d2 ON(d1.timesheet_id = d2.timesheet_id AND d2.status =0)";
		$sql = $sql." LEFT OUTER JOIN hr_timesheet_ot_category d3 ON(d1.ot_category_id = d3.id)";
		$sql = $sql." WHERE d1.status =0 AND d2.salary_id ='".$salary_id."'";
		$msg->add("query", $sql);
		
		$dt = $appSession->getTier()->getTable($msg);
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			
			$id = $dt->getString($i, "id");
			$start_date = $dt->getString($i, "start_date");
			$shit_day = $dt->getString($i, "shit_day");
			$employee_id = $dt->getString($i, "employee_id");
			$time_amount = $dt->getString($i, "time_amount");
			$ot_amount = $dt->getString($i, "ot_amount");
			$code = $dt->getString($i, "shit_day");
			$this->timeList[count($this->timeList)] = [$id, $employee_id, $start_date, $shit_day, $time_amount, $ot_amount, $code];
		}
		
		
		
	}
	public function listShiftDay()
	{
		$values = [];
		for($i=0; $i<count($this->timeList); $i++)
		{
			if($this->timeList[$i][1] == $this->employee_id)
			{
				if($this->timeList[$i][3] != "")
				{
					$values[count($values)] = floatval($this->timeList[$i][3]);
				}
			}
		}
		
		return $values;
	}
	public function totalShiftDay()
	{
		$values = $this->listShiftDay();
		$a = 0;
		for($i =0; $i<count($values); $i++)
		{
			$a = $a + $values[$i];
		}
		return $a;
	}
	public function listTime()
	{
		
		$values = [];
		
		for($i=0; $i<count($this->timeList); $i++)
		{
			if($this->timeList[$i][1] == $this->employee_id)
			{
				if($this->timeList[$i][4] != "")
				{
					$values[count($values)] = floatval($this->timeList[$i][4]);
					
					
				}
			}
		}
		
	
		return $values;
	}
	public function totalTime()
	{
		$values = $this->listTime();
		$a = 0;
		for($i =0; $i<count($values); $i++)
		{
			$a = $a + $values[$i];
		}
		return $a;
	}
	
	public function listOTTime()
	{
		$values = [];

		for($i=0; $i<count($this->timeList); $i++)
		{
			if($this->timeList[$i][1] == $this->employee_id)
			{
				if($this->timeList[$i][5] != "")
				{
					$values[count($values)] = floatval($this->timeList[$i][5]);
					
				}
			}
		}
		return $values;
	}
	public function totalOTTime()
	{
		$values = $this->listOTTime();
		$a = 0;
		for($i =0; $i<count($values); $i++)
		{
			$a = $a + $values[$i];
		}
		return $a;
	}
	public function findOT($code)
	{
		$a = 0;
		for($i=0; $i<count($this->timeList); $i++)
		{
			if($this->timeList[$i][1] == $this->employee_id && $this->timeList[$i][6] == $code)
			{
				if($this->timeList[$i][5] != "")
				{
					$a  = $a + floatval($this->timeList[$i][5]);
					
				}
			}
		}
		return $a;
	}
	
}
?>