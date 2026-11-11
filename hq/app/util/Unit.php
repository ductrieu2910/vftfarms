<?php
class Unit
{
	private $config;
	private $units =[];
	function __construct($config) 
	{
		$this->config = $config;
	}
	function load($appSession)
	{
		$sql = "SELECT d1.category_id, d1.unit_id, d1.dividend, d1.divisor from product_unit_rate d1";
		$sql = $sql." LEFT OUTER JOIN product_unit d2 ON(d1.unit_id = d2.id)";
		$sql = $sql." WHERE d1.status =0";
		
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$this->units = $appSession->getTier()->getArray($msg);
	}
	
	function convert($from_unit_id, $to_unit_id, $product_id, $quantity)
	{
		if($from_unit_id == $to_unit_id)
		{
			return $quantity;
		}
		if($from_unit_id == "" || $to_unit_id == "")
		{
			return $quantity;
		}
		$from_factor = 1;
		$to_factor = 1;
		for ($i = 0; $i < count($this->units); $i++) {
          if ($this->units[$i][0] == $product_id &&  $this->units[$i][1] == $from_unit_id) {
            $dividend = floatval($this->units[$i][2]);
            $divisor = floatval($this->units[$i][3]);
			
            if ($divisor != 0) {
              $from_factor = $dividend / $divisor;
              break;
            }
          }
        }
		for ($i = 0; $i < count($this->units); $i++) {
          if ($this->units[$i][0] == $product_id &&  $this->units[$i][1] == $to_unit_id) {
            $dividend = floatval($this->units[$i][2]);
            $divisor = floatval($this->units[$i][3]);
            if ($divisor != 0) {
              $to_factor = $dividend / $divisor;
              break;
            }
          }
        }
		
		if ($to_factor <= 0) {
          $to_factor = 1;
        }
        if ($from_factor <= 0) {
          $from_factor = 1;
        }
		
		return ($from_factor * $quantity) / $to_factor;
		
	}
	
}

?>