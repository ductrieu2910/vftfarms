<?php
class Payroll
{
	public $payrollList = [];
	public $employee_id = "";
	public $attributeList = [];
	public $receipt_date = "";
	public $startDate = "";
	public $endDate = "";
	public function load($appSession, $company_id, $salary_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT d1.id, d1.employee_id, d1.payroll_id, d1.value, d2.code";
		$sql = $sql." FROM hr_payroll_salary_line d1";
		$sql = $sql." LEFT OUTER JOIN hr_payroll d2 ON(d1.payroll_id = d2.id)";
		$sql = $sql. " WHERE d1.salary_id ='".$salary_id."' AND d1.status =0";
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			
			$id = $dt->getString($i, "id");
			$employee_id = $dt->getString($i, "employee_id");
			$payroll_id = $dt->getString($i, "payroll_id");
			$value = $dt->getString($i, "value");
			$code = $dt->getString($i, "code");
			$this->payrollList[count($this->payrollList)] = [$id, $employee_id, $payroll_id, $value, $code];
		}
		$sql = "SELECT d1.id, d1.code";
		$sql = $sql." FROM hr_payroll d1";
		$sql = $sql. " WHERE d1.company_id ='".$company_id."' AND d1.status =0";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$code = $dt->getString($i, "code");
			$this->attributeList[count($this->attributeList)] = [$id, $code];
		}
		
	}
	public function setValue($appSession, $employee_id, $payroll_id, $amount)
	{
		$hasItem = false;
		for($i =0; $i<count($this->payrollList); $i++)
		{
			if($this->payrollList[$i][1] == $employee_id && $this->payrollList[$i][2] == $payroll_id)
			{
				$this->payrollList[$i][3] = $amount;
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			$code = "";
			for($i = 0; $i<count($this->attributeList); $i++)
			{
				if($this->attributeList[$i][0] == $payroll_id)
				{
					$code = $this->attributeList[$i][1];
					break;
				}
			}
			$this->payrollList[count($this->payrollList)] = ["", $employee_id, $payroll_id, $amount, $code];
		}
	}
	public function addValue($appSession, $employee_id, $payroll_id, $amount)
	{
		$hasItem = false;
		
		for($i =0; $i<count($this->payrollList); $i++)
		{
			if($this->payrollList[$i][1] == $employee_id && $this->payrollList[$i][2] == $payroll_id)
			{
				if($appSession->getTool()->isNumber($amount))
				{
					$amount = $appSession->getTool()->toDouble($amount);
					$a = $appSession->getTool()->toDouble($this->payrollList[$i][3]);
					$a = $a + $amount;
					$this->payrollList[$i][3] = $a;
				}else{
					$this->payrollList[$i][3] = $amount;
				}
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			$code = "";
			for($i = 0; $i<count($this->attributeList); $i++)
			{
				if($this->attributeList[$i][0] == $payroll_id)
				{
					$code = $this->attributeList[$i][1];
					break;
				}
			}
			$this->payrollList[count($this->payrollList)] = ["", $employee_id, $payroll_id, $amount, $code];
		}
	}
	public function findAmount($code)
	{
		$a = 0;
		for($i =0; $i<count($this->payrollList); $i++)
		{
			if($this->payrollList[$i][1] == $this->employee_id && $this->payrollList[$i][4] == $code)
			{
				if(is_numeric($this->payrollList[$i][3]))
				{
					$a = $a + floatval($this->payrollList[$i][3]);
				}
			}
		}
	
		return $a;
	}
	public function findValue($code)
	{
		for($i =0; $i<count($this->payrollList); $i++)
		{
			if($this->payrollList[$i][1] == $this->employee_id && $this->payrollList[$i][4] == $code)
			{
				return $this->payrollList[$i][3];
			}
		}
		return "";
	}
	
	
}
?>