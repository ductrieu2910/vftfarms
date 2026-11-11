<?php
require_once(ABSPATH.'app/data/DataTable.php' );
require_once(ABSPATH.'app/data/Builder.php' );
require_once(ABSPATH.'app/Message.php' );
require_once('DataTier.php' );
require_once(ABSPATH.'app/data/PostgreSQLTier.php' );

class DataTier
{
	public $url =''; //mysql,postgresql,mssql, oracle
	public $api_id;
	
	public function __construct($url, $api_id) {
		$this->url = $url;
		if($api_id == "")
		{
			$api_id = "master";
		}
		$this->api_id = $api_id;
	}
	public function parse_timestamp($timestamp, $gmt_offset, $format = 'Y-m-d H:i:s')
	{
		if($gmt_offset == "")
		{
			return $timestamp;
		}
		$pos = strpos($timestamp, ".");
		$u = "";
		if($pos !== false)
		{
			$u = substr($timestamp, $pos);
			
			$timestamp = substr($timestamp, 0, $pos);
		}

		$d = strtotime($timestamp." ".$gmt_offset." minutes");
		
		return date($format, $d).$u;
	}
	public function createTier($name, $type, $params){
		$items = explode("&", $params);
		$url = "";
		$port = "";
		$user = "";
		$pass = "";
		
		for($i =0; $i<count($items); $i++)
		{
			$index = strpos($items[$i], '=');
			
			if($index !=-1)
			{
				$k = substr($items[$i], 0, $index);
				$v = substr($items[$i],$index + 1);
				if($k == "url")
				{
					$url = $v;
				}else if($k == "port")
				{
					$port = $v;
				}else if($k == "user")
				{
					$user = $v;
				}else if($k == "password")
				{
					$pass = $v;
				}
				
			}
		}
		if($url != "" && $type == "postgresql")
		{
			return new PostgreSQLTier($name, $url, $port, $user, $pass);
		}
		return new DataTier($this->url, $this->api_id);
	}
	public function setDBId($api_id){
		if($api_id == "")
		{
			$api_id = "master";
		}
		$this->api_id = $api_id;
	}
	public function getDbId(){
		return $this->api_id;
	}
	public function getDateString()
	{
		return "NOW()";
	}
	public function paging($sql, $p, $ps, $sort)
	{
		$arr = [];
		$index = strpos($sql, "FROM", 5); 
		if($index == true)
		{
			$arr[1] = "SELECT COUNT(*) ".substr($sql, $index);
			
		}
		if($sort != "")
		{
			$sql = $sql." ORDER BY ".$sort;
		}
		$p = $p * $ps;
		$sql = $sql." OFFSET ".$p." LIMIT ".$ps;
		$arr[0] = $sql;
		
		return $arr;
	}
	public function limit($sql, $n)
	{
		return $sql." LIMIT ".$n;
	}
	function split($s, $c)
	{
		return explode( $c, $s);
	}
	function indexOf($s, $c)
	{
		return strpos ($s, $c);
	}
	public function condition_array($condition)
	{
		$arr = [];
		$conditions = explode(',', $condition);
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "")
			{
				$filter_id = "";
				$parent_id = "";
				$column_name = "";
				$func = "";
				$op = "";
				$value = "";
				$value_1 = "";
				$logic = "";
				$type = "";
				$caption = "";
				$lines = $this->split( $conditions[$i], ';');
				for($j=0; $j<count($lines); $j++)
				{
					$index = $this->indexOf($lines[$j], '=');
					if($index != -1)
					{
						$n = substr($lines[$j], 0, $index);
						if($n == "i")
						{
							$filter_id = substr($lines[$j] , $index + 1);
						}
						else if($n == "p")
						{
							$parent_id = substr($lines[$j] , $index + 1);
						}else if($n == "n")
						{
							$column_name = substr($lines[$j] , $index + 1);
						}else if($n == "f")
						{
							$func = substr($lines[$j] , $index + 1);
						}else if($n == "o")
						{
							$o = substr($lines[$j] , $index + 1);
						}else if($n == "v")
						{
							$value = substr($lines[$j] , $index + 1);
						}else if($n == "l")
						{
							$logic = substr($lines[$j] , $index + 1);
						}else if($n == "t")
						{
							$type = substr($lines[$j] , $index + 1);
						}else if($n == "c")
						{
							$caption = substr($lines[$j] , $index + 1);
						}
					}
				}
				if($filter_id != "")
				{
					$arr[count($arr)] =[$filter_id, $parent_id, $column_name, $func, $o, $value,  $logic, $type,  $caption];
				}
				
				
			}
		}
		return $arr;
	}
	public function buildCondition($conditions, $parent_id, $first)
	{
		$sr = "";
		$founds =[];
		for($i=0; $i<count($conditions); $i++)
		{
			if($conditions[$i][1] == $parent_id)
			{
				$founds[count($founds)] = $conditions[$i];
			}
		}
		
		for($i=0; $i<count($founds); $i++)
		{
			$s = "";
			//$filter_id, $parent_id, $column_name, $func, $o, $value,  $logic, $type, $caption
			
			$line = 0;
			$is_param = -1;
			if($founds[$i][5] != "")
			{
				$is_param = strpos('.', $founds[$i][5]);
			}
			if($is_param == false)
			{
				$is_param = -1;
			}
			
				
			if($first == 1)
			{
				if($founds[$i][6] == "")
				{
					$s = $s." AND  ";
				}else{
					$s = $s." ".$founds[$i][6]." ";
				}
			}
			$s1 = $this->buildCondition($conditions, $founds[$i][0], 1);
			if($s1 != "")
			{
				$s = $s."(";
				
			}
			
			$first = 1;
			$line = $line + 1;
			if($founds[$i][7] == "d")
			{
				$founds[$i][5] = str_replace("=", ":", $founds[$i][5]);
			}
				
			
			$s =$s.$founds[$i][2];
			
			if($founds[$i][4] == 'equal')
			{
				if($is_param ==-1)
				{
					if($founds[$i][7] == "s" )
					{
						$s =$s." = '".$founds[$i][5]."'";
					}else if($founds[$i][7] == "d")
					{
						$s =$s." >= '".$founds[$i][5]." 00:00:00.00'";
						$s =$s." AND ".$founds[$i][2] ." <='".$founds[$i][5]." 23:59:59.9999'";
					}
					else{
						$s =$s." = ".$founds[$i][5];
					}
					
					
				}else{
					$s =$s." = '".$founds[$i][5]."'";
				}
				
				
			}else if($founds[$i][4] == 'not_equal')
			{
				if($is_param ==-1)
				{
					if($founds[$i][7] == "s" || $founds[$i][7] == "d")
					{
						$s =$s." != '".$founds[$i][5]."'";
					}else{
						$s =$s." != ".$founds[$i][5];
					}
					
					
				}else{
					$s =$s." != '".$founds[$i][5]."'";
				}
				
			}else if($founds[$i][4] == 'in')
			{
				if($founds[$i][7] == "s" || $founds[$i][7] == "d")
				{
					$items = explode("~", $founds[$i][5]);
					
					$s_in = "";
					for($n =0; $n<count($items); $n++)
					{
						if($n>0)
						{
							$s_in = $s_in.", ";
						}
						$s_in = $s_in."'".$items[$n]."'";
					}
					
					$s =$s." IN (".$s_in.")";
					
				}else{
					$s =$s." IN (".$founds[$i][5].")";
				}
				
			}else if($founds[$i][4] == 'not_in')
			{
				if($founds[$i][7] == "s" || $founds[$i][7] == "d")
				{
					$items = explode("~", $founds[$i][5]);
					
					$s_in = "";
					for($n =0; $n<count($items); $n++)
					{
						if($n>0)
						{
							$s_in = $s_in.", ";
						}
						$s_in = $s_in."'".$items[$n]."'";
					}
					
					$s =$s." NOT IN (".$s_in.")";
					
				}else{
					$s =$s." NOT IN (".$founds[$i][5].")";
				}
			}else if($founds[$i][4] == 'less')
			{
				if($founds[$i][7] == "s")
				{
					$s =$s." < '".$founds[$i][5]."'";
				}else if($founds[$i][7] == "d")
				{
					$s =$s." < '".$founds[$i][5]." 23:59:59.999'";
				}
				else{
					$s =$s." < ".$founds[$i][5]."";
				}
				
			}else if($founds[$i][4] == 'less_or_equal')
			{
				if($founds[$i][7] == "s")
				{
					$s =$s." <= '".$founds[$i][5]."'";
				}else if($founds[$i][7] == "d")
				{
					$s =$s." <= '".$founds[$i][5]." 23:59:59.999'";
				}
				else{
					$s =$s." <= ".$founds[$i][5]."";
				}
				
			}else if($founds[$i][4] == 'greater')
			{
				if($founds[$i][7] == "s")
				{
					$s =$s." > '".$founds[$i][5]."'";
				}else if($founds[$i][7] == "d")
				{
					$s =$s." > '".$founds[$i][5]." 00:00:00.000'";
				}
				else{
					$s =$s." > ".$founds[$i][5]."";
				}
				
			}else if($founds[$i][4] == 'greater_or_equal')
			{
				if($founds[$i][7] == "s")
				{
					$s =$s." >= '".$founds[$i][5]."'";
				}else if( $founds[$i][7] == "d")
				{
					$s =$s." >= '".$founds[$i][5]." 00:00:00.000'";
				}
				else{
					$s =$s." >= ".$founds[$i][5]."";
				}
				
				
			}else if($founds[$i][4] == 'begins_with')
			{
				$s =$s." ILIKE '".$founds[$i][5]."%'";
			}else if($founds[$i][4] == 'not_begins_with')
			{
				$s =$s." NOT ILIKE '".$founds[$i][5]."%'";
			}else if($founds[$i][4] == 'contains')
			{
				$s =$s." ILIKE '%".$founds[$i][5]."%'";
			}else if($founds[$i][4] == 'not_contains')
			{
				$s =$s." NOT ILIKE '%".$founds[$i][5]."%'";
			}else if($founds[$i][4] == 'ends_with')
			{
				$s =$s." ILIKE '%".$founds[$i][5]."'";
			}else if($founds[$i][4] == 'not_ends_with')
			{
				$s =$s." NOT ILIKE '%".$founds[$i][5]."'";
			}else if($founds[$i][4] == 'is_empty')
			{
				$s =$s." = ''";
			}else if($founds[$i][4] == 'is_not_empty')
			{
				$s =$s." != ''";
			}else if($founds[$i][4] == 'is_null')
			{
				$s =$s." IS NULL";
			}else if($founds[$i][4] == 'is_not_null')
			{
				$s =$s." IS NOT NULL";
			}
			
			if($s1 != "")
			{
				
				$s = $s." ".$s1.")";
			}
			
			$sr = $sr.$s;
			
		}
		
		return $sr;
	}
	public function buildSQL($line, $parent_id, $params, $condition)
	{
		$sql = "";
		if($parent_id == "")
		{
			
			$columns = "";
			for($i=0; $i<count($line); $i++)
			{
				$table_index = 0;
				for($j=0; $j<$i; $j++)
				{
					if($line[$j][2] == $line[$i][2])
					{
						$table_index = $table_index + 1;
					}
				}
			
				$data = explode(';', $line[$i][3]);
				for($j=0; $j<count($data); $j++)
				{
					if($data[$j] != "")
					{
						if($columns != "")
						{
							$columns = $columns.", ";
						}
						$as = $line[$i][2];
						if($table_index>0)
						{
							$as = $line[$i][2]."".$table_index;
						}
					
						$columns = $columns."".$as.".".$data[$j]." AS ".$as."__".$data[$j];
					}
				}
				
			}
			
			$sql = "SELECT ".$columns;
		}
		for($i=0; $i<count($line); $i++)
		{
			
			if($line[$i][1] == $parent_id)
			{
				if($parent_id == "")
				{
					$sql = $sql." FROM ".$line[$i][2];
				}else
				{
					$table_index = 0;
					for($j=0; $j<$i; $j++)
					{
						if($line[$j][2] == $line[$i][2])
						{
							$table_index = $table_index + 1;
						}
					}
					$as = $line[$i][2];
					if($table_index>0)
					{
						$as = $line[$i][2]."".$table_index;
					}
					
					$where = $this->buildCondition($this->condition_array($line[$i][4]), 0, 0);
					for($j=0; $j<count($params); $j++)
					{
						$where = str_replace("{".$params[$j][0]."}", "'".$params[$j][2]."'", $where);
					}
			
					$sql = $sql." LEFT OUTER JOIN ".$line[$i][2]." ".$as." ON( ".$where.")";
				}
				$sql = $sql.$this->buildSQL($line, $line[$i][0], $params, $condition);
			}
		}
		if($parent_id == "" && count($condition)>0)
		{
			
			$where = $this->buildCondition($condition, 0, 0);
			for($j=0; $j<count($params); $j++)
			{
				$where = str_replace("{".$params[$j][0]."}", "'".$params[$j][2]."'", $where);
			}
			$sql = $sql." WHERE (".$where.")";
			
			
		}
		
		return $sql;
	}
	public function validColumn($name)
	{
		return $name;
	}
	public function buildSearch($arr, $search)
	{
		$s = "";
		for($i =0; $i<count($arr); $i++)
		{
			if($arr[$i] != "")
			{
				if($s != "")
				{
					$s = $s." OR ";
				}
				$s = $s.$arr[$i]." ILIKE '%".str_replace("'", "''", $search)."%'";
			}
			
		}
		return $s;
	}
	public function createBuilder($name)
	{
		return new Builder($name);
	}
	public function getBuilder($name)
	{
		return $this->createBuilder($name);
	}
	public function createMessage()
	{
		return new Message();
	}
	public function getMessage($name)
	{
		return $this->createMessage($name);
	}
	public function createBuilderFromRow($name, $dt, $index)
	{
		$builder = new Builder($name);
		for($i =0; $i<$dt->getColumnCount(); $i++)
		{
			$name = $dt->getColumns()[$i]->getName();
			$type = $dt->getColumns()[$i]->gettype();
			if($name == "delete_id")
			{
				continue;
			}

			$builder->add($name,$dt->getValueAt($index, $i), $type);
		}
		return $builder;
	}
	
	function getInsert($builder)
	{
		$sql= "INSERT INTO ".$builder->getName()." (";
		for($i=0; $i<count($builder->getData()); $i++)
		{
			if($i>0)
			{
				$sql = $sql.", ";
			}
			$sql = $sql.$this->validColumn($builder->getData()[$i][0]);
		}
		$sql = $sql.") VALUES (";
		
		for($i=0; $i<count($builder->getData()); $i++)
		{
			if($i>0)
			{
				$sql = $sql.", ";
			}
			
			if($builder->getData()[$i][2] == 'n' || $builder->getData()[$i][2] == 'i'  )
			{
				if($builder->getData()[$i][1] == '')
				{
					$sql = $sql." 0";
				}else{
					$sql = $sql.$builder->getData()[$i][1];
				}
				
			}else if($builder->getData()[$i][2] == 'd')
			{
				
				if($builder->getData()[$i][1] == '')
				{
					$sql = $sql." NULL";
				}else{
					$sql = $sql."'".$builder->getData()[$i][1]."'";
				}
			}
			else if($builder->getData()[$i][2] == 'f')
			{
				$sql = $sql.$builder->getData()[$i][1];
			}
			else
			{
				$sql = $sql."'".str_replace("'", "''", $builder->getData()[$i][1]). "'";
			}
			
		}
		$sql = $sql.")";
		return $sql;
	}
	function getUpdate($builder)
	{
		$sql= "";
		$id = "";
		for($i=0; $i<count($builder->getData()); $i++)
		{
			if($builder->getData()[$i][0] == "id")
			{
				$id = $builder->getData()[$i][1];
				continue;
			}
			if($sql != "")
			{
				$sql = $sql.", ";
			}
			$sql = $sql.$this->validColumn($builder->getData()[$i][0])." =";
			if($builder->getData()[$i][2] == 'n' || $builder->getData()[$i][2] == 'i')
			{
				if($builder->getData()[$i][1] == "")
				{
					$sql = $sql." 0 ";
				}else{
					$sql = $sql.$builder->getData()[$i][1];
				}
				
			}else if($builder->getData()[$i][2] == 'd')
			{
				if($builder->getData()[$i][1] == "")
				{
					$sql = $sql." NULL ";
				}else{
					$sql = $sql."'".$builder->getData()[$i][1]."'";
				}
			}
			else if($builder->getData()[$i][2] == 'f')
			{
				$sql = $sql.$builder->getData()[$i][1];
			}
			else
			{
				$sql = $sql."'".str_replace("'", "''", $builder->getData()[$i][1]). "'";
			}
		}
		
		$sql = "UPDATE ".$builder->getName()." SET ".$sql;
		if($id == "")
		{
			$sql = $sql. " WHERE 1=0";
		}else
		{
			$sql = $sql." WHERE id='".$id."'";
		}
		return $sql;
	}
	public function httpPost($url, $data) {
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
		curl_setopt($ch,CURLOPT_TIMEOUT, 20);
		$response = curl_exec($ch);
		
		curl_close ($ch);

		return $response;
	}
	public function getData($msg)
	{
		return $this->httpPost($this->url, $msg->getMessage());
	}
	public function execQuery($msg, $db_id)
	{
		$msg->add("db_id",  $db_id);
		$msg->add("ac", "exec");
		$response = $this->httpPost($this->url."data", $msg->getMessage());
		return "1";
	}
	public function execMaster($msg)
	{
		return $this->execQuery($msg, "master");
		
	}
	public function exec($msg)
	{
		return $this->execQuery($msg, $this->api_id);
	}
	public function updateMaster($builder)
	{
		return $this->execMaster($this->getUpdate($builder));
	}
	public function update($builder)
	{
		return $this->exec($this->getUpdate($builder));
	}
	public function insertMaster($builder)
	{
		return $this->execMaster($this->getInsert($builder));
	}
	public function insert($builder)
	{
		return $this->exec($this->getInsert($builder));
	}
	public function getValue($msg)
	{
		$msg->add("ac", "getvalue");
		$sValue = $this->httpPost($this->url."data", $msg->getMessage());
		return $sValue;

	}
	
	public function getValueQuery($msg, $db_id)
	{
		$msg->add("db_id", $db_id);
		$msg->add("ac", "query");
		return $this->getValue($msg);
	}
	public function getValueMaster($msg)
	{
		return $this->getValueQuery($msg, "master");
	}
	
	public function getTable($msg)
	{
		$msg->add("ac", "query");
		$response = $this->httpPost($this->url."data", $msg->getMessage());
		$dt = new DataTable("");
		$arr = explode("\n", $response);
		$len = count($arr);
		if($len>0)
		{
			$items = explode("\t", $arr[0]);
			for($j=0; $j<count($items); $j++)
			{
				$dt->addColumn($items[$j]);
			}
		}
		if($len>1)
		{
			
			$items = explode("\t", $arr[1]);
			for($j=0; $j<count($items); $j++)
			{
				$dt->getColumns()[$j]->setType($items[$j]);
			}
		}
		for($i = 2; $i<$len; $i++)
		{
			$row = explode("\t", $arr[$i]);
			for($j=0; $j<count($row); $j++)
			{
				$row[$j] = str_replace("-n-", "\n", $row[$j]);
				$row[$j] = str_replace("-t-", "\t", $row[$j]);
			}
			$dt->addArray($row);
		}
		return $dt;
		
	}
	public function getTableQuery($msg, $db_id)
	{
		$dt = new DataTable("");
		$msg->add("db_id", $db_id);
		$msg->add("ac", "query");
		return $this->getTable($msg);
	}
	public function getTableMaster($msg)
	{
		return $this->getTableQuery($msg, "master");
	}
	
	
	
	
	public function getArray($msg)
	{
		$msg->add("ac", "array");
		$rows = [];
		
		$response = $this->httpPost($this->url."data", $msg->getMessage());
		if($response == "")
		{
			return [];
		}
		$arr = explode("\n", $response);
		$len = count($arr);
		for($i = 0; $i<$len; $i++)
		{
			$row = explode("\t", $arr[$i]);
			for($j=0; $j<count($row); $j++)
			{
				$row[$j] = str_replace("-n-", "\n", $row[$j]);
				$row[$j] = str_replace("-t-", "\t", $row[$j]);
			}
			$rows[count($rows)] = $row;
		}
		return $rows;
		
		
	}
	public function getArrayQuery($msg, $db_id)
	{
		$msg->add("db_id", $db_id);
		$msg->add("ac", "array");
		return $this->getArray($msg);
	}
	public function getArrayMaster($msg){
		return $this->getArrayQuery($msg, "master");
	}
	
	public function getResource($msg)
	{
		
		$msg->add("db_id", $this->api_id);
		return $this->httpPost($this->url."data", $msg->getMessage());
	}
	public function checkDb()
	{
		return -1;
	}
	public function close()
	{
	}
}

?>