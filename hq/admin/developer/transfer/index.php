<?php

	$ac = "";
	if(isset($_REQUEST['ac']))
	{
		$ac = $_REQUEST['ac'];
	}
	if($ac == "")
	{
		$ac = "view";
	}

?>
<?php 
if($ac == "view")
{
	$func_select = "";
	if(isset($_REQUEST['func_select']))
	{
		$func_select = $_REQUEST['func_select'];
	}
	$table_id = "";
	if(isset($_REQUEST['table_id']))
	{
		$table_id = $_REQUEST['table_id'];
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$root_company_id = $appSession->getConfig()->getProperty("root_company_id");
	

	$sql = "SELECT id, name FROM ir_database_table WHERE status =0 ORDER BY name ASC";

	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $root_company_id);
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);

	$numrows = count($result);
?>

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
		 <option value="<?php echo $_id;?>" <?php if($table_id == $_id){ echo " selected ";}?>><?php echo $name;?></option>
		 <?php
		  }
		  ?>
		</select>
	</div>
	<div class="col m1">
		<button class="button" onclick="addTable()" style="width:100%">+</button>
	</div>
</div>

<div class="row ">
	<div class="col m12">
		<div id="pnDatabaseDetail"></div>
	</div>
</div>	
<script>
	function selectTable(table_id)
	{
		var _url = '<?php echo URL;?>admin/install/table/?ac=list&func_select=<?php echo $func_select;?>&table_id=' + table_id;
		showpage('pnDatabaseDetail', _url);
	}
	
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
		var _url = '<?php echo URL;?>admin/install/table/?ac=new&id=<?php echo $id;?>&name=' + encodeURIComponent(name);
		loadPage('pnDatabaseDetail', _url, function (status, message) {
			if (status == 0) {
				if (message.length == 36) {
					selectTable(message);
				}
				else {
					alert(message);
				}
			}
		}, true);
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
		var _url = '<?php echo URL;?>admin/install/table/?ac=addColumn&table_id=' + table_id;
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
	function delColumn(table_id, column_name)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/install/table/?ac=delColumn&table_id=' + table_id;
		_url = _url + "&column_name=" + column_name;
		
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
		var _url = '<?php echo URL;?>admin/install/table/?ac=delTable&table_id=' + table_id;
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
	<?php
	if($table_id != "")
	{
	?>
	selectTable('<?php echo $table_id;?>');
	<?php
	}
	?>
</script>	 
<?php
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
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	if($numrows>0)
	{
		echo $name." is exist";
		exit();
	}
	$id = $appSession->getTool()->getId();
	
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
	$sql = $sql.", '".$appSession->getConfig()->getProperty("root_company_id")."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$database_id."'";
	$sql = $sql.", '".$name."'";
	$sql = $sql.")";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	$s = "";
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
		$sql = $sql.", '".$appSession->getConfig()->getProperty("root_company_id")."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$id."'";
		$sql = $sql.", '".$column_name."'";
		$sql = $sql.", '".$column_type."'";
		$sql = $sql.", '".$column_size."'";
		$sql = $sql.")";
		if($s != "")
		{
			$s = $s.";";
		}
		$s = $s.$sql;
	}
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $s);
	$result = $appSession->getTier()->exec($msg);
	
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
	$sql = "SELECT MAX(code) AS code FROM ir_database_table_column WHERE table_id='".$table_id."'";
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	$code = "1";
	if($numrows>0)
	{
		$row = $result[0];
		$code = $row[0];
		$code = $appSession->getTool()->toInt($code) + 1;
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
	$sql = $sql.", '".$appSession->getConfig()->getProperty("root_company_id")."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$table_id."'";
	$sql = $sql.", '".$column_name."'";
	$sql = $sql.", '".$column_type."'";
	$sql = $sql.", '".$column_size."'";
	$sql = $sql.")";
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
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
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
	
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
	
	$sql = "SELECT id, name, type, size FROM ir_database_table_column WHERE table_id='".$table_id."' AND status =0";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	
	
?>
	<a class="button" href="javascript:delTable('<?php echo $table_id;?>')">-</a>
	<div class="responsive">
		<form id="frmdbDetail" name="frmdbDetail">
		<table class="table-all bordered">
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
					<td><input type="text" class="text" value="<?php echo $column_name;?>" onblur="saveLine('ir_database_table_column', 'name', '<?php echo $column_id;?>', this, function(status, message){})"></td>
					
					 <td>
						<select id="column_type" class="select" onblur="saveLine('ir_database_table_column', 'type', '<?php echo $column_id;?>', this, function(status, message){})">
							<option value="varchar" <?php if($column_type == "varchar"){ echo " selected" ; }?>>Text</option>
							<option value="smallint" <?php if($column_type == "smallint"){ echo " selected" ; }?> >Boolean</option>
							<option value="int" <?php if($column_type == "int"){ echo " selected" ; }?>>Integer</option>
							<option value="datetime" <?php if($column_type == "datetime"){ echo " selected" ; }?>>Datetime</option>
							<option value="float" <?php if($column_type == "float"){ echo " selected" ; }?>>Float</option>
							<option value="text" <?php if($column_type == "text"){ echo " selected" ; }?>>Long text</option>
						</select>
					</td>
					<td><input type="text" class="text" value="<?php echo $column_size;?>" onblur="saveLine('ir_database_table_column', 'size', '<?php echo $column_id;?>', this, function(status, message){})"></td>
					<?php } ?>

					<td><a href="javascript:removeLine('ir_database_table_column', '<?php echo $table_id;?>', function(status, message){ selectTable('<?php echo $table_id;?>'); })">-</a></td>
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
					<option value="text">Long text</option>
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
<?php
}
?>
