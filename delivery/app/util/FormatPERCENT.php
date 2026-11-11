<?php
class FormatPERCENT
{
	private $config;
	function __construct($config) 
	{
		$this->config = $config;
	}
	function parse($value)
	{
		if($value == '')
		{
			return 0;
		}
		return ((double)$value/100);
	}
	function format($value)
	{
		
		$value = $value * 100;
		
		$decimal_point = $this->config->getProperty("decimal_point");
		$thousands_sep = $this->config->getProperty("thousands_sep");
		if($decimal_point == "")
		{
			$decimal_point = ".";
		}
		if($thousands_sep == "")
		{
			$thousands_sep = ",";
		}
		if($thousands_sep == $decimal_point)
		{
			$decimal_point = ".";
			$thousands_sep = ",";
		}
		$place = 2;
		if($value<0.001)
		{
			$place = 3;			
		}
		if($value<0.0001)
		{
			$place = 4;			
		}
		if($value>=0)
		{
			$place = 0;			
		}
		$value = number_format($value, $place, $decimal_point, $thousands_sep);
		
		$value = str_replace($decimal_point."0000", "", $value);
		return $value."%";
	}
	function formatWithPlace($value, $decimal_place)
	{
		
		$value = $value * 100;
		
		$decimal_point = $this->config->getProperty("decimal_point");
		$thousands_sep = $this->config->getProperty("thousands_sep");
		if($decimal_point == "")
		{
			$decimal_point = ".";
		}
		if($thousands_sep == "")
		{
			$thousands_sep = ",";
		}
		if($thousands_sep == $decimal_point)
		{
			$decimal_point = ".";
			$thousands_sep = ",";
		}
		
		$value = number_format($value, $decimal_place, $decimal_point, $thousands_sep);
		
		$value = str_replace($decimal_point."0000", "", $value);
		return $value."%";
	}
	
}

?>