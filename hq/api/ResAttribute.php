<?php
class ResAttribute
{

	private $values =[];
	function __construct() 
	{
		
	}
	function load($appSession, $rel_id)
	{
		$sql = "SELECT d2.code, d2.value, d1.description, d1.id AS line_id, d2.id AS attribute_id FROM res_attribute_line d1 LEFT OUTER JOIN res_attribute d2 ON(d1.attribute_id = d2.id) WHERE d1.status =0 AND d1.rel_id='".$rel_id."'";
		
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		for($i =0; $i<count($arr); $i++)
		{
			$hasItem = false;
			for($n =0; $n<count($this->values); $n++)
			{
				if($this->values[$n][0] ==  $arr[$i][0])
				{
					$this->values[$n] = $arr[$i];
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
				$this->values[count($this->values)] = $arr[$i];
			}
			
		}
		
	}
	function clear()
	{
		$this->$values =[];
	}
	function find($code)
	{
		for($i =0; $i<count($this->values); $i++)
		{
			if($this->values[$i][0] == $code)
			{
				return $this->values[$i][2];
			}
		}
		return "";
	}
	function findAttributeId($code)
	{
		for($i =0; $i<count($this->values); $i++)
		{
			if($this->values[$i][0] == $code)
			{
				return $this->values[$i][4];
			}
		}
		return "";
	}
	function findLineId($code)
	{
		for($i =0; $i<count($this->values); $i++)
		{
			if($this->values[$i][0] == $code)
			{
				return $this->values[$i][3];
			}
		}
		return "";
	}
	function findAttributeValue($code)
	{
		for($i =0; $i<count($this->values); $i++)
		{
			if($this->values[$i][0] == $code)
			{
				return $this->values[$i][1];
			}
		}
		return "";
	}
}

?>