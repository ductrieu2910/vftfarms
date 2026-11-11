<?php
require_once('DataTier.php' );
set_time_limit(2000); // 
class PostgreSQLTier extends DataTier
{
	private $db = NULL;
	private $host = "localhost";
	private $port = "5432";
	private $connection ="";
	function __construct($name, $host, $port, $user, $password) {
		$this->type = 'postgresql';
		$this->name = $name;
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->connection = "host=".$host." port=".$port." dbname=".$name." user=".$user." password=".$password;
		
	}
	public function getHost()
	{
		return $this->host;
	}
	public function getPort()
	{
		return $this->port;
	}
	public function getUser()
	{
		return $this->user;
	}
	public function getPassword()
	{
		return $this->password;
	}
	function open()
	{
		if($this->db === NULL)
		{
			$this->db = pg_Connect($this->connection);
		}
		
	}
	function close()
	{
		if($this->db != NULL)
		{
			pg_close($this->db);
		}
		
	}
	
	// Chạy câu SQL; nếu lỗi thì ghi vào error log của PHP và trả về false.
	private function run($sql, $params = null)
	{
		if(!$this->db)
		{
			error_log("PostgreSQLTier: khong ket noi duoc database | SQL: ".substr($sql, 0, 2000));
			return false;
		}
		$result = ($params === null) ? @pg_exec($this->db, $sql) : @pg_query_params($this->db, $sql, $params);
		if($result === false)
		{
			error_log("PostgreSQLTier: ".trim(pg_last_error($this->db))." | SQL: ".substr($sql, 0, 2000));
		}
		return $result;
	}
	public function validColumn($name)
	{
		return '"'.$name.'"';
	}
	function exec($msg)
	{
		$sql = $msg->get("query");
		$this->open();
		$result = $this->run($sql);
		if($result === false)
		{
			// Lenh ghi that bai thi dung han, khong chay tiep voi du lieu luu do dang.
			throw new RuntimeException("Loi ghi du lieu, xem chi tiet trong error log");
		}
		$error = pg_result_error($result);
		
		if($error != "")
		{
			return -1;
		}
		//$this->close();
		return 1;
	}
	
	function getValue($msg)
	{
		$sql = $msg->get("query");
		$this->open();
		$result = $this->run($sql);
		if($result === false)
		{
			return "";
		}
		
		$numrows = pg_numrows($result);	
		$value = "";
		if($numrows>0)
		{
			$row = pg_fetch_array($result, 0);
			$value = $row[0];
		}
		//$this->close();
		return $value;
	}
	function getArray($msg)
	{
		$sql = $msg->get("query");
		$this->open();
		$data = [];
		$result = $this->run($sql);
		if($result === false)
		{
			return $data;
		}
		$numrows = pg_numrows($result);	
		
		$fields = pg_num_fields($result);
		for($i=0; $i<$numrows; $i++)
		{
			$row = pg_fetch_array($result, $i);
			$arr = [];
			for($j =0; $j<$fields; $j++)
			{
				$arr[count($arr)] = $row[$j];
			}
			$data[count($data)] = $arr;
		}

		return $data;
	}
	function getTable($msg)
	{
		$sql = $msg->get("query");
		$this->open();
		$dt = new DataTable("");
		$result = $this->run($sql);
		if($result === false)
		{
			return $dt;
		}
		$numrows = pg_numrows($result);	
		$fields = pg_num_fields($result);
		for($n =0; $n<$fields; $n++)
		{
			$fieldname = pg_field_name($result, $n);
			$fieldtype = pg_field_type($result, $n);
			if($fieldtype == 'int2' || $fieldtype == 'int4' || $fieldtype== 'int8')
			{
				$fieldtype = 'i';
			}else if($fieldtype == 'float8')
			{
				$fieldtype = 'r';
			}else if($fieldtype == 'timestamp')
			{
				$fieldtype = 'd';
			}
			else{
				$fieldtype = '';
			}
			$dt->addColumn($fieldname);
			$dt->getColumns()[count($dt->getColumns())-1]->setType($fieldtype);
		}
		for($i=0; $i<$numrows; $i++)
		{
			$row = pg_fetch_array($result, $i);
			
			for($j =0; $j<$fields; $j++)
			{
				$dt->setValueAt($row[$j], $i, $j);

			}
		}
		return $dt;
	}
	
}

?>