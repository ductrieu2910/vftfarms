<?php
class Employee
{
	public $employeeList = [];
	public $employee;
	public $date;
	public function load($appSession, $company_id, $date)
	{
		$this->date = $date;
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT d1.id, d1.code, d1.name, d1.department_id, d1.job_title_id, d1.join_date";
		$sql = $sql.", (select receipt_date from contract where partner_id=d1.id and status=0) AS contract_date";
		$sql = $sql." FROM hr_employee d1";
		$sql = $sql." WHERE d1.status =0 AND d1.company_id ='".$company_id."'";
		$sql = $sql." AND (d1.terminated = 0 OR d1.terminated_date>='".$date."') AND d1.join_date<='".$date."'";
		$msg->add("query", $sql);
		
		$dt = $appSession->getTier()->getTable($msg);
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			
			$id = $dt->getString($i, "id");
			$code = $dt->getString($i, "code");
			$department_id = $dt->getString($i, "department_id");
			$job_title_id = $dt->getString($i, "job_title_id");
			$join_date = $dt->getString($i, "join_date");
			$contract_date = $dt->getString($i, "contract_date");
			$item = new Item();
			$item->id = $id;
			$item->code = $code;
			$item->department_id = $department_id;
			$item->job_title_id = $job_title_id;
			$item->join_date = $join_date;
			$item->contract_date = $contract_date;
			$this->employeeList[count($this->employeeList)] = $item;
		}
		
	}
	public function find($id)
	{
		for($i =0; $i<count($this->employeeList); $i++)
		{
			if($this->employeeList[$i]->id == $id)
			{
				$this->employee = $this->employeeList[$i];
				return $this->employee;
			}
		}
		return NULL;
	}
	public function findCode($code)
	{
		for($i =0; $i<count($this->employeeList); $i++)
		{
			if($this->employeeList[$i]->code == $code)
			{
				$this->employee = $this->employeeList[$i];
				return $this->employee;
			}
		}
		return NULL;
	}
	public function seniorityYear()
	{
		$date1=date_create($this->date);
		$date2=date_create($this->employee->join_date);
		$diff= date_diff($date1,$date2);
		return abs($diff->format("%R%a"))/365;
	}
	public function joinDate(){
		return $this->employee->join_date;
	}
	public function contractDate(){
		return $this->employee->contract_date;
	}
	
}
class Item
{
	public $id = "";
	public $code = "";
	public $department_id ="";
	public $job_title_id = "";
	public $join_date = "";
	public $contract_date = "";
	public function __construct() {
		
	}
	
}

?>