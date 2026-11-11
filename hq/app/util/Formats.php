<?php
require ABSPATH.'app/util/FormatDOUBLE.php';
require ABSPATH.'app/util/FormatINT.php';
require ABSPATH.'app/util/FormatDATE.php';
require ABSPATH.'app/util/FormatPERCENT.php';
class Formats
{
	private $formatDouble;
	private $formatInt;
	private $formatDate;
	private $formatPercent;
	function __construct($config) 
	{
		
		$this->formatDouble = new FormatDOUBLE($config);
		$this->formatInt = new FormatINT($config);
		$this->formatDate = new FormatDATE($config);
		$this->formatPercent = new FormatPERCENT($config);
	}
	function getDOUBLE()
	{
		return $this->formatDouble;
	}
	function getINT()
	{
		return $this->formatInt;
	}
	function getDATE()
	{
		return $this->formatDate;
	}
	function getPERCENT()
	{
		return $this->formatPercent;
	}
}
?>