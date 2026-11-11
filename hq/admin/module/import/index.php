<?php 

require_once('../../../config.php' );
$ac = "view";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$module_id = "";
if(isset($_REQUEST['module_id']))
{
	$module_id = $_REQUEST['module_id'];
}
if(isset($_POST['module_id']))
{
	$module_id = $_POST['module_id'];
}


$model_id = "";
if(isset($_REQUEST['model_id']))
{
	$model_id = $_REQUEST['model_id'];
}
if(isset($_POST['model_id']))
{
	$model_id = $_POST['model_id'];
}


$module_model_id = "";
if(isset($_REQUEST['module_model_id']))
{
	$module_model_id = $_REQUEST['module_model_id'];
}
if(isset($_POST['module_model_id']))
{
	$module_model_id = $_POST['module_model_id'];
}
function findValue($values, $id)
{
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][0] == $id)
		{
			return $values[$i][1];
		}
	}
	return "";
}

?>
<!DOCTYPE html>
<html>
<body>
<?php



$target_dir = "";
$header =[];
if(isset($_POST["submit"])|| isset($_REQUEST["submit"])) 
{
	
	$target_dir = ABSPATH."disk/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	
	if (file_exists($target_file)) {
		$deleted = unlink($target_file);
	}
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	  
		$file = fopen($target_file, "r") or exit("Unable to open file!");
		
		$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."' AND d1.status =0";

		$msg = $appSession->getTier()->createMessage();
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		$values = [];
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$arr = [];
			$arr[0] = $row[0];
			$arr[1] = $row[1];
			$values[$i] = $arr;
		}


	
		$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";

		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		$models = [];
		$columns_model_id = "";
		for($i =0; $i<$numrows; $i++)
		{
			$row = $result[$i];
			$arr = [];
			$arr[0] = $row[0];
			$arr[1] = $row[1];
			$arr[2] = $row[2];
			$arr[3] = $row[3];
			$models[$i] = $arr;
			if($row[3] == "columns")
			{
				$columns_model_id = $row[0];
			}
						
		}
		$columns = [];
		$table_name = "";
		$data_id = "";
		for($i =0; $i<count($models); $i++)
		{
			if($models[$i][1] == $columns_model_id)
			{
				$caption = "";
				$name = "";
				$type = "";
				
				
				for($j =0; $j<count($models); $j++)
				{
					if($models[$j][1] == $models[$i][0])
					{
						$value = findValue($values, $models[$j][0]);
						if($models[$j][3] == 'caption')
						{
							$caption = $value;
						}
						else if($models[$j][3] == 'name')
						{
							$name = $value;
						}else if($models[$j][3] == 'type')
						{
							$type = $value;
						}
					}
				}
				$index = strpos($name, "__");
				if($index !==false)
				{
					$name = substr($name, $index + 2);
				}
				$hasItem = false;
				
				
			}
			if($models[$i][3] == "src" || $models[$i][2] == "data")
			{
				$data_id = findValue($values, $models[$i][0]);
				
			}
		}
		if($data_id == "")
		{
			echo "Data is not support";
		}else
		{
			
			


			$condition = "";
			$group_by = "";
			$order_by = "";
			$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$data_id."' AND status =0";
			$msg->add("query", $sql);
			$result_data_sql = $appSession->getTier()->getArray($msg);
			$numrows_data_sql = count($result_data_sql);	
			if($numrows_data_sql>0)
			{
				$row1 = $result_data_sql[0];
				$condition = $row1[0];
				$group_by = $row1[1];
				$order_by = $row1[2];
			}
			$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$data_id."' ORDER BY create_date ASC";
			$msg->add("query", $sql);
			$result_data_sql = $appSession->getTier()->getArray($msg);
			$numrows_data_sql = count($result_data_sql);
			$sql_params = [];
			for($m =0; $m<$numrows_data_sql; $m++)
			{
				$row1 = $result_data_sql[0];		
				$arr = [];
				$arr[0] = $row1[0];
				$arr[1] = $row1[1];
				$arr[2] = $row1[2];
				$sql_params[$m] = $arr;
				
			}
			
			
			$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
							
			$lines = [];
			$msg->add("query", $sql);
			$result_data_sql = $appSession->getTier()->getArray($msg);
			$numrows_data_sql = count($result_data_sql);	
			for($m =0; $m<$numrows_data_sql; $m++)
			{
				$row1 = $result_data_sql[$m];
				$arr = [];
				$arr[0] = $row1[0];
				$arr[1] = $row1[1];
				$arr[2] = $row1[2];
				$arr[3] = $row1[3];
				$arr[4] = $row1[4];
				$lines[$m] = $arr;
				
				if($table_name == "" && $row1[1] == "")
				{
					$table_name = $row1[2];
				}
				
			}
			
			
		$sql = "SELECT * FROM ".$table_name." WHERE 1=0";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		$columns = $dt->getColumns();
		$items = 0;
		$rows = 0;
		$sql_list = "";
		if($table_name != "" && count($columns)>0)
		{
			$len = 0;
			while(!feof($file))
			{
				$line = fgets($file);
				$sp = chr(9);
				
				if(count($header) == 0)
				{
					$header = explode($sp, $line);
					for($i=0; $i<count($header); $i++)
					{
						$header[$i] = trim($header[$i]);
					}
					
				}else
				{
					$data = explode($sp, $line);
					if(count($header) == count($data))
					{
						$id = $appSession->getTool()->getId();
						for($i=0; $i<count($header); $i++)
						{
							if($header[$i] == "id")
							{
								$id =  $data[$i];
								break;
							}
						}
						$sql = "INSERT INTO ".$table_name."(";
						$sql = $sql."id";
						$sql = $sql.", create_date";
						$sql = $sql.", write_date";
						$sql = $sql.", create_uid";
						$sql = $sql.", write_uid";
						$sql = $sql.", company_id";
						$sql = $sql.", status";
						$sql = $sql." )VALUES(";
						$sql = $sql."'".$id."'";
						$sql = $sql.", NOW()";
						$sql = $sql.", NOW()";
						$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
						$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
						$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
						$sql = $sql.", 0";
						$sql = $sql.")";
						
						if($sql_list != "")
						{
							$sql_list = $sql_list.";";
						}
						$sql_list = $sql_list.$sql;
						//$result = $appSession->getTier()->exec($sql);
						
						
					
						$sql = "UPDATE ".$table_name." SET status =0";
						for($i=0; $i<count($header); $i++)
						{
							for($c =0; $c<count($columns); $c++)
							{
								if($columns[$c]->getName() == $header[$i] && $header[$i] != "id")
								{
									if($data[$i] != "NULL")
									{
										$s = str_replace("'", "''", $data[$i]);
										$s = str_replace(array("\r\n", "\r", "\n"), "", $s);
										$sql = $sql.", ".$columns[$c]->getName()."='".$s."'";
									}else{
										$sql = $sql.", ".$columns[$c]->getName()."= NULL ";
									}
									
								}
							}
						}
						$sql= $sql." WHERE id='".$id."'";
						
						if($sql_list != "")
						{
							$sql_list = $sql_list.";";
						}
						$sql_list = $sql_list.$sql;
						
						$rows = $rows + 1;
						$len = $len + 1;
						if($len>=1000)
						{
							
							$msg->add("query", $sql_list);
							$result = $appSession->getTier()->exec($msg);
							$sql_list = "";
							$len = 0;
						}
						
					}
				}
			}
			if($sql_list != "")
			{
				
				$msg->add("query", $sql_list);
				$result = $appSession->getTier()->exec($msg);
			}
			fclose($file);
			echo "Rows: ".$rows."<br>";
			
		}
		
	}
		
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}

?>
<form action="index.php" method="post" enctype="multipart/form-data" onsubmit="return(validUpload());">
  Chọn file excel để import:<br>
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input type="hidden" id="module_id" name="module_id" value="<?php echo $module_id;?>">
  <input type="hidden" id="model_id" name="model_id" value="<?php echo $model_id;?>">
  <input type="hidden" id="module_model_id" name="module_model_id" value="<?php echo $module_model_id;?>">
  <br>
  <br>
  <input type="submit" value="Import" name="submit" id="submit">
</form>
<script>
function validUpload()
{
	var ctr = document.getElementById('fileToUpload');
	if(ctr.value == '')
	{
		alert("Please select file");
		ctr.focus();
		return false;
	}
	return true;
  
}
</script>
</body>
</html>
