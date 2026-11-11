<?php
class Leave
{
	public $datas = [];
	public $employee_id = "";
	public function load($appSession, $company_id, $start_date, $end_date)
	{
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT d1.employee_id, d1.unit_id, d1.category_id, d2.code, SUM(d1.quantity) AS quantity";
		$sql = $sql." FROM hr_leave d1";
		$sql = $sql." LEFT OUTER JOIN hr_leave_category d2 ON(d1.category_id = d2.id)";
		$sql = $sql." WHERE d1.status =0 AND d1.start_date >='".$start_date."'";
		$sql = $sql." AND d1.start_date<='".$end_date."'";
		$sql = $sql." AND d1.company_id ='".$company_id."'";
		$sql = $sql." GROUP BY d1.employee_id, d1.unit_id, d1.category_id, d2.code";
		$msg->add("query", $sql);
		
		$dt = $appSession->getTier()->getTable($msg);
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$employee_id = $dt->getString($i, "employee_id");
			$unit_id = $dt->getString($i, "unit_id");
			$category_id = $dt->getString($i, "category_id");
			$code = $dt->getString($i, "code");
			$quantity = $dt->getString($i, "quantity");
			$this->datas[count($this->datas)] = [$employee_id, $unit_id, $category_id, $code, $quantity];
		}
	}
	public function find($code)
	{
		$a = 0;
		for($i=0; $i<count($this->datas); $i++)
		{
			if($this->datas[$i][0] == $this->employee_id && $this->datas[$i][3] == $code)
			{
				if($this->datas[$i][4] != "")
				{
					$a = $a +  floatval($this->datas[$i][4]);
				}
			}
		}
		
		return $a;
	}
	
	
}
?>