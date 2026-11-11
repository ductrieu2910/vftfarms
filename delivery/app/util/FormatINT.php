<?php
class FormatINT
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
		return (int)$value;
	}
	function format($value)
	{
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
		$f = floatval($value);
		return number_format($f, 0, $decimal_point, $thousands_sep);
	}
}

?>