<?php

validUser($appSession);

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac == "view")
{
	$id = "";

	$prev_view_module_id = "";
	$arr_params = explode('&', $params);
	$paramList =[];
	for($p=0; $p<count($arr_params); $p++)
	{
		$arrItem = explode('=', $arr_params[$p]);
		if(count($arrItem)>1)
		{
			if($arrItem[0] == "rel_id")
			{
				$id = $arrItem[1];
			}
		}
	}
	$sql = "SELECT id, name FROM ir_database_table WHERE database_id='".$id."' AND status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
?>
<div class="row">
	<div class="col m12" style="text-align:right">
		<a href="javascript:applyDatabase()" class="button" >Apply</a>
	</div>
</div>
<div class="row ">
	<div class="col m11">
		<select onchange="selectTable(this.value)" class="select">
		  <option value="">Select table</option>
		  <?php
		  for($i =0; $i<$numrows; $i++)
		  {
			  $row = $result[$i];
			  $_id = $row[0];
			  $name = $row[1];
		 ?>
		 <option value="<?php echo $_id;?>"><?php echo $name;?></option>
		 <?php
		  }
		  ?>
		</select>
	</div>
	<div class="col m1">
		<button class="button" onclick="addTable()" style="width:100%">+</button>
	</div>
</div>
<div class="row">
	<div class="col m12">
		<div id="pnDatabaseDetail"></div>
	</div>
</div>
<script>
	function addTable()
	{
		var name = prompt("Please enter table name", ""); 
		if(name == null)
		{
			return;
		}
		if (name == '') { 
			alert("Name is not empty");
		} 
		var _url = '<?php echo URL;?>addons/database/table.php?ac=new&id=<?php echo $id;?>&name=' + encodeURIComponent(name);
		loadPage('pnDatabaseDetail', _url, function (status, message) {
			if (status == 0) {
				if (message.length == 36) {
					loadTables(message);
				}
				else {
					alert(message);
				}
			}
		}, true);
	}
	function selectTable(table_id)
	{
		var _url = '<?php echo URL;?>admin/developer/database/?ac=list&table_id=' + table_id;
		showpage('pnDatabaseDetail', _url);
	}
	function addColumn(table_id)
	{
		var ctr = document.getElementById('column_name');
		if(ctr.value == '')
		{
			alert('Enter column name');
			ctr.focus();
			return;
		}
		var column_name= ctr.value;
		
		var column_type = document.getElementById('column_type').value;
		
		ctr = document.getElementById('column_size');
		if(ctr.value == 'varchar' && column_size == '')
		{
			alert("Enter size");
			ctr.focus();
			return;
		}
		column_size = ctr.value;
		var _url = '<?php echo URL;?>admin/developer/database/?ac=addColumn&table_id=' + table_id;
		_url = _url + "&column_name=" + column_name;
		_url = _url + "&column_type=" + column_type;
		_url = _url + "&column_size=" + column_size;

		loadPage('pnDatabaseDetail', _url, function (status, message) {
			if (status == 0) {
				if (message == "OK") {
					selectTable(table_id);
				}
				else {
					alert(message);
				}
			}
		}, true);
	}
	function delColumn(table_id, id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/database/?ac=delColumn&id=' + id;
		
		
		loadPage('pnDatabaseDetail', _url, function (status, message) {
			if (status == 0) {
				if (message == "OK") {
					selectTable(table_id);
				}
				else {
					alert(message);
				}
			}
		}, true);
	}
	function delTable(table_id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/database/?ac=delTable&table_id=' + table_id;
		loadPage('pnDatabaseDetail', _url, function (status, message) {
			if (status == 0) {
				if (message == "OK") {
					loadTables('');
				}
				else {
					alert(message);
				}
			}
		}, true);
	}
	function applyDatabase()
	{
		var result = confirm("<?php echo $appSession->getLang()->find('Want to apply?');?>");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/developer/database/?ac=apply&id=<?php echo $id;?>';
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						alert("Data is updated");
					}
					else{
						alert(message);
					}
				}
				
			}, true);
	}
	loadTables('');
	
</script>
<?php
}else if($ac == "list")
{
	$table_id = "";
	if(isset($_REQUEST['table_id']))
	{
		$table_id = $_REQUEST['table_id'];
	}
	$func_select = "";
	if(isset($_REQUEST['func_select']))
	{
		$func_select = $_REQUEST['func_select'];
	}
	
	$sql = "SELECT id, name, type, size FROM ir_database_table_column WHERE table_id='".$table_id."' AND status =0 ORDER BY create_date ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<a class="button" href="javascript:delTable('<?php echo $table_id;?>')">-</a>
	<div class="responsive">
		<form id="frmdbDetail" name="frmdbDetail">
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th width="120">Type</th>
					<th width="100">Size</th>
					<th width="20">#</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($i=0; $i<$numrows; $i++)
				{
					$row = $result[$i];
					$column_id = $row[0];
					$column_name = $row[1];
					$column_type = $row[2];
					$column_size = $row[3];
			
					if($column_name != "")
					{
				?>
				<tr>
				<?php if($column_name == "id"
				|| $column_name == "create_uid"
				|| $column_name == "create_date"
				|| $column_name == "create_date"
				|| $column_name == "write_uid"
				|| $column_name == "write_date"
				|| $column_name == "company_id"
				|| $column_name == "status"
				|| $column_name == "sequence"
				){?>
					<td><?php echo $column_name;?></td>
					<td><?php echo $column_type;?></td>
					<td><?php echo $column_size;?></td>
					<?php }else{ ?>
					<td><input type="text" class="text" value="<?php echo $column_name;?>" onblur="saveColumn('name', '<?php echo $column_id;?>', this)"></td>
					<td><?php echo $column_type;?></td>
					<td><input type="text" class="text" value="<?php echo $column_size;?>" onblur="saveColumn('size', '<?php echo $column_id;?>', this)"></td>
					<?php } ?>

					<td><a href="javascript:delColumn('<?php echo $table_id;?>', '<?php echo $column_id;?>')">-</a></td>
				</tr>
				<?php
					}
				}
				?>
				<tr>
				<td><input type="text" id="column_name" class="input"></td>
				<td>
				<select id="column_type" class="select">
					<option value="varchar">Text</option>
					<option value="smallint">Boolean</option>
					<option value="int">Integer</option>
					<option value="datetime">Datetime</option>
					<option value="float">Float</option>
				</select>
				</td>
				<td><input type="text" id="column_size" class="input"></td>
				<td><a class="button" href="javascript:addColumn('<?php echo $table_id;?>')">+</a></td>
				</tr>
			</tbody>
		</table>
		</form>
		
	</div>
	<br>
	<br>
	<script>
		 
		 
		 function saveColumn(name, id, theInput)
		 {
			
			
			var _url = '<?php echo URL;?>admin/developer/database/?ac=saveColumn';
			_url = _url + '&id=' + id;
			_url = _url + "&name=" + encodeURIComponent(name.trim());
			_url = _url + "&value=" + encodeURIComponent(theInput.value.trim());
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						//loadParams();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		 }
	</script>
	<?php
}else if($ac == "saveColumn")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$index = '';
	if(isset($_REQUEST['index']))
	{
		$index = $_REQUEST['index'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$value = '';
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}

	$sql = "UPDATE ir_database_table_column SET write_date=NOW()";
	$sql = $sql.", ".$name."='".str_replace("'", "''", $value)."'";
	$sql = $sql." WHERE id='".$id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	echo "OK";
}else if($ac == "new")
{
	$database_id = "";
	if(isset($_REQUEST['id']))
	{
		$database_id = $_REQUEST['id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sql = "SELECT id FROM ir_database_table WHERE name='".$name."' AND status =0 AND database_id='".$database_id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	if($numrows>0)
	{
		echo $name." is exist";
		exit();
	}
	$id = gen_uuid();
	
	$columns = [ 
		["id","varchar", "36"],
		["create_uid","varchar", "36"],
		["create_date", "datetime", "29"],
		["write_uid","varchar", "36"],
		["write_date", "datetime", "29"],
		["company_id","varchar", "36"],
		["status", "smallint", "5"],
	];
	$sql = "INSERT INTO ir_database_table(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", database_id";
	$sql = $sql.", name";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$database_id."'";
	$sql = $sql.", '".$name."'";
	$sql = $sql.")";

	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	for($i=0; $i<count($columns); $i++)
	{
		$column_name = $columns[$i][0];
		$column_type = $columns[$i][1];
		$column_size = $columns[$i][2];
		
		$sql = "INSERT INTO ir_database_table_column(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", table_id";
		$sql = $sql.", name";
		$sql = $sql.", type";
		$sql = $sql.", size";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$id."'";
		$sql = $sql.", '".$column_name."'";
		$sql = $sql.", '".$column_type."'";
		$sql = $sql.", '".$column_size."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}
	
	
	echo $id;
	
}else if($ac == "addColumn")
{
	$table_id = "";
	if(isset($_REQUEST['table_id']))
	{
		$table_id = $_REQUEST['table_id'];
	}
	
	
	$column_name = "";
	if(isset($_REQUEST['column_name']))
	{
		$column_name = $_REQUEST['column_name'];
	}
	$column_type = "";
	if(isset($_REQUEST['column_type']))
	{
		$column_type = $_REQUEST['column_type'];
	}
	$column_size = "";
	if(isset($_REQUEST['column_size']))
	{
		$column_size = $_REQUEST['column_size'];
	}
	
	if($column_type == "datetime")
	{
		$column_size = "29";
	}else if($column_type == "smallint")
	{
		$column_size = "5";
	}else if($column_type == "float")
	{
		$column_size = "17";
	}else if($column_type == "int")
	{
		$column_size = "5";
	}
	
	$sql = "INSERT INTO ir_database_table_column(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", table_id";
	$sql = $sql.", name";
	$sql = $sql.", type";
	$sql = $sql.", size";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$table_id."'";
	$sql = $sql.", '".$column_name."'";
	$sql = $sql.", '".$column_type."'";
	$sql = $sql.", '".$column_size."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	
	echo "OK";
}else if($ac == "delColumn")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_database_table_column set status =1 WHERE id='".$id."'";
	$result = pg_exec($db, $sql);
	echo "OK";
	
}
else if($ac == "delTable")
{
	$table_id = "";
	if(isset($_REQUEST['table_id']))
	{
		$table_id = $_REQUEST['table_id'];
	}
	$sql = "UPDATE ir_database_table SET status =1";
	$sql = $sql.", write_date=NOW()";
	$sql = $sql." WHERE id='".$table_id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "apply")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	function buildColumn($type, $column_name, $column_type, $column_size, $is_null, $primary_key)
	{
		if($type == "postgresql")
		{
			$column = $column_name;
			if("ntext" == $column_type)
			{
				$column_type = "text";
			}
				
			if ("datetime" == $column_type || "smalldatetime" == $column_type )
			{
				$column_type = "timestamp";
			}
				
			if("varchar" == $column_type || "nvarchar"== $column_type)
			{
				if($column_size != "")
				{
					$sz = intval($column_size);
					if($sz>100)
					{
						$column = $column." character varying";
						
					}else{
						$column = $column." character varying(";
						$column = $column.$column_size;
						$column = $column.")";
					}
				}
				else
				{
					$column = $column." text";
				}
			}
			else{
				$column = $column." ".$column_type;
			}
					
			if($primary_key != "")
			{
				 $column = $column." PRIMARY KEY";
			}
				 
			if("1"== $is_null)
			{
				$column = $column + " NOT NULL";
			}
			return $column;
		}else if($type == "mysql")
		{
			$column = $column_name;
			if("varchar" == $column_type  || "nvarchar" == $column_type  || "char" == $column_type  || "nchar" == $column_type )
			{
				
				$column = $column." ";
				$column = $column." ".$column_type;
				$column = $column."(";
				$column = $column." ".$column_size;
				$column = $column.")";
			}
			else{
				$column = $column." ";
				$column = $column." ".$column_type;
			}
			return $column;
		}
		return $column_name;
	}
	function addColumn($table_name, $name, $type, $size)
	{
		return "ALTER TABLE ".$table_name." ADD COLUMN ".buildColumn("postgresql", $name, $type, $size, "", "");
	}
	$sql = "SELECT name FROM ir_database WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$db_name = "";
	if(count($result)>0)
	{
		$row = $result[0];
		$db_name = $row[0];
	}
	$sql = "SELECT table_name FROM information_schema.tables";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	$tables =[];
	for($i=0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		$tables[count($tables)] = $row[0];
	}
	$sql = "SELECT id, name, description, write_date";
	$sql = $sql." FROM ir_database_table WHERE status= 0 AND database_id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	for($i=0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		$name = $row[1];
		$hasItem = false;
		for($n=0; $n<count($tables); $n++)
		{
			if($tables[$n] == $name)
			{
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			$sql = "CREATE TABLE IF NOT EXISTS ".$name;
			$sql = $sql."(";
			$sql = $sql." id character varying(36) NOT NULL,";
			$sql = $sql." create_uid character varying(36),";
			$sql = $sql." create_date timestamp without time zone,";
			$sql = $sql." write_uid character varying(36),";
			$sql = $sql." write_date timestamp without time zone,";
			$sql = $sql." company_id character varying(36),";
			$sql = $sql." status smallint,";
			$sql = $sql." CONSTRAINT ".$name."_pkey PRIMARY KEY (id)";
			$sql = $sql. ")";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
			
		}
		$sql = "SELECT * FROM ".$name." WHERE 1=0";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
	
		$fields = count($dt->getColumns());
		$columns = [];
		for($n =0; $n<$fields; $n++)
		{
			$fieldname = $dt->getColumns()[$n]->getName();
			$columns[count($columns)] = $fieldname;
		}
		$sql = "SELECT name, type, size";
		$sql = $sql." FROM ir_database_table_column WHERE status= 0 AND table_id='".$row[0]."'";
		$msg->add("query", $sql);
		$result_column =  $appSession->getTier()->getArray($msg);
		$numrows_column = count($result_column);
		
		for($j =0; $j<$numrows_column; $j++)
		{
			$row_column = $result_column[$j];
			$column_name = $row_column[0];
			$column_type = $row_column[1];
			$column_size = $row_column[2];
			if($column_name != "")
			{
				$hasItem = false;
				for($n=0; $n<count($columns); $n++)
				{
					if($columns[$n] == $column_name)
					{
						$hasItem = true;
						break;
					}
				}
				if($hasItem == false)
				{
					$sql= addColumn($name, $column_name, $column_type, $column_size);
					$r = $appSession->getTier()->exec($msg);
				}
			}
		}
		
	}
	echo "OK";
}
?>

	