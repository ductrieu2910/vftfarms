<?php
class Report
{
	public $params =[];
	public function parse($appSession, $s)
	{
		$this->params = [];
		$arr = $appSession->getTool()->split($s, ',');
		for($i =0; $i<count($arr); $i++)
		{
			$items = $appSession->getTool()->split($arr[$i], ';');
			$l =  [];
			for($j=0; $j<count($items); $j++)
			{
				$index = $appSession->getTool()->indexOf($items[$j], ':');
				if($index != -1)
				{
					$l[count($l)] = [$appSession->getTool()->substring($items[$j],  0, $index), $appSession->getTool()->substring($items[$j], $index + 1)];
				}
			}
			$this->params[count($this->params)] = $l;
		}
	}
	public function getParams(){
		return $this->params;
	}
	public function getParamValueCaption($name)
	{
		for($i =0; $i<count($this->params); $i++)
		{
			$l = $this->params[$i];
			for($j=0; $j<count($l); $j++)
			{
				if($l[$j][0] == "c" && $l[$j][1] == $name)
				{
					for($n=0; $n<count($l); $n++)
					{
						if($l[$n][0] == "v")
						{
							return $l[$n][1];
						}
					}
				}
			}
		}
		return "";
	}
	public function getParamValueName($name)
	{
		for($i =0; $i<count($this->params); $i++)
		{
			$l = $this->params[$i];
			for($j=0; $j<count($l); $j++)
			{
				if($l[$j][0] == "n" && $l[$j][1] == $name)
				{
					for($n=0; $n<count($l); $n++)
					{
						if($l[$n][0] == "v")
						{
							return $l[$n][1];
						}
					}
				}
			}
		}
		return "";
	}
	public function getParamTypeCaption($name)
	{
		for($i =0; $i<count($this->params); $i++)
		{
			$l = $this->params[$i];
			for($j=0; $j<count($l); $j++)
			{
				if($l[$j][0] == "c" && $l[$j][1] == $name)
				{
					for($n=0; $n<count($l); $n++)
					{
						if($l[$n][0] == "t")
						{
							return $l[$n][1];
						}
					}
				}
			}
		}
		return "";
	}
	public function getParamTypeName($name)
	{
		for($i =0; $i<count($this->params); $i++)
		{
			$l = $this->params[$i];
			for($j=0; $j<count($l); $j++)
			{
				if($l[$j][0] == "n" && $l[$j][1] == $name)
				{
					for($n=0; $n<count($l); $n++)
					{
						if($l[$n][0] == "t")
						{
							return $l[$n][1];
						}
					}
				}
			}
		}
		return "";
	}
}

?>