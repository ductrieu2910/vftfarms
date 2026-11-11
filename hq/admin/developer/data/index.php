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
	$type = "sql";
	
?>
<div class="row-padding">
	<div class="col m12">
		<div id="pnParams"></div>
	</div>
</div>
	
	<br>
	<div class="row-padding">
		<div class="col m12">
			<div id="pnRel"></div>
		</div>
	</div>
	
	<br>
	<div class="row-padding">
		<div class="col m12">
			<div id="pnWhere"></div>
		</div>
	</div>
	
	<br>
	<div class="row-padding">
		<div class="col m12">
			<div id="pnHaving"></div>
		</div>
	</div>
	
	<br>
	<div class="row-padding">
		<div class="col m12">
			<div id="pnOrderBy"></div>
		</div>
	</div>
	
	<script>
		function loadWhere()
		{
			var _url = '<?php echo URL;?>admin/developer/data/?ac=where&id=<?php echo $id;?>';

			loadPage('pnWhere', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		function loadRel()
		{
			var _url = '<?php echo URL;?>admin/developer/data/?ac=line&data_id=<?php echo $id;?>&type=<?php echo $type;?>';
			
			loadPage('pnRel', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		loadRel();
		
		function loadHaving()
		{
			var _url = '<?php echo URL;?>admin/developer/data/?ac=having&id=<?php echo $id;?>';

			loadPage('pnHaving', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		function loadOrderBy()
		{
			var _url = '<?php echo URL;?>admin/developer/data/?ac=order_by&id=<?php echo $id;?>';

			loadPage('pnOrderBy', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		function loadParams()
		{
			var _url = '<?php echo URL;?>admin/developer/data/?ac=params&id=<?php echo $id;?>';
			
			loadPage('pnParams', _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		loadWhere();
		loadHaving();
		loadOrderBy();
		loadParams();

	</script>
<?php
}else if($ac == "line")
{

	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$data_id = '';
	if(isset($_REQUEST['data_id']))
	{
		$data_id = $_REQUEST['data_id'];
	}
	
		function buildCategory($lines, $parent_id)
		{
				$count = 0;
				for($j =0; $j<count($lines); $j++)
				{
					if($lines[$j][1] == $parent_id )
					{
						$count += 1;
					}
				}
				for($j =0; $j<count($lines); $j++)
				{
					if($lines[$j][1] == $parent_id )
					{
						$id = $lines[$j][0];
						$name = $lines[$j][2];
						$parent_name = "";
						for($n =0; $n<count($lines); $n++)
						{
							if($lines[$n][0] == $parent_id)
							{
								$parent_name =$lines[$n][2];
							}
						}
						
				?>
				<tr >
					<td width="15" align="right" ><img src="<?php echo URL;?>assets/img/arrow_child.gif" border="0" /></td>
					<td style="padding-left:5px; padding-top:5px"><a href="javascript:loadLine('<?php echo $id;?>')"><?php echo $name;?></a> <a href="javascript:addCategory('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a> <a href="javascript:removeCategory('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a> 
					<?php if($parent_id !=""){?>
					<div id="pnRelation<?php echo $id;?>"></div>
					<script>
						loadPage('pnRelation<?php echo $id;?>', '<?php echo URL;?>addons/data/index.php?ac=relationship&id=<?php echo $id;?>&name=<?php echo $name;?>&parent_name=<?php echo $parent_name;?>', function(status, message)
						{
							if(status== 0)
							{
								
								
							}
							
						}, false);
					</script>
					<?php }?>
					<div id="pnLine<?php echo $id;?>"></div>
					</td>
				  </tr>
				  
				  <tr>
					<td width="15" <?php if($j<($count-1)){?>style="background:url(<?php echo URL;?>assets/img/arrow_child_space.gif) repeat-y" <?php }?>></td>
					<td style="padding-left:5px"><table width="100%" border="0" cellpadding="0" cellspacing="0"><?php echo buildCategory($lines, $id);?></table></td>
				</tr>
				<?php
					
				}
			}
			
		}
		$sql = "SELECT id, parent_id, name, relationship FROM ir_data_line WHERE status =0 AND data_id='".$data_id."' ORDER BY create_date ASC";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		$lines = [];
		for($j =0; $j<$numrows; $j++)
		{
			$row = $result[$j];
			$arr = [];
			$arr[0] = $row[0];
			$arr[1] = $row[1];
			$arr[2] = $row[2];
			$arr[3] = $row[3];
			$lines[$j] = $arr;
		}
		?>
		<a href="javascript:addCategory('')">Tables: <img src="<?php echo URL;?>assets/img/add.png"></a>
		<div class="row">
			<div class="col container m12">
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<?php echo buildCategory($lines, "");?>
				</table>
			</div>
			
		</div>
	<script>
	var parent_id;
	function addCategory(pid)
	{
		parent_id = pid;
		var _url = '<?php echo URL;?>admin/developer/data/?ac=tables';
		openPopup(_url);
	}
	function loadLineData(id)
	{
		loadPage('pnLine' + id, '<?php echo URL;?>admin/developer/data/?ac=lineData&id=' + id, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	<?php
	for($j =0; $j<count($lines); $j++)
	{
		$id = $lines[$j][0];
	?>
	loadLineData('<?php echo $id;?>') ; 
	<?php
	}
	?>
	function addColumn(theSelect, id)
	{
		if(theSelect.value == '')
		{
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/data/?ac=addColumn&name=' + theSelect.value;	
		_url = _url + '&id=' + id;
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadLineData(id);
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function removeColumn(name, id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/data/?ac=removeColumn&id=' + id;
		_url = _url + '&name=' + name;		
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0 )
			{
				if(message == "OK")
				{
					loadLineData(id);
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function doCategory(name)
	{
		var _url = '<?php echo URL;?>admin/developer/data/?ac=addLine&name=' + encodeURIComponent(name);	
		_url = _url + '&parent_id=' + parent_id;
		_url = _url + '&data_id=<?php echo $data_id;?>';
		closePopup();
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					loadRel();
					loadWhere();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	function removeCategory(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?>admin/developer/data/?ac=removeLine&id=' + id;	
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0 )
			{
				if(message == "OK")
				{
					loadRel();
					loadWhere();
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
		
	}
	
	</script>
		<?php
	
	
	
}else if($ac == "tables")
{
	$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema='public'  ORDER BY table_name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
?>
<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th nowrap="nowrap"><?php echo $appSession->getLang()->find('Name');?></th>
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = $result[$j];
				
				$name = $row[0];
				
				
		
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			
			<td style="width:40px" style="text-align:center; vertical-align:middle"><a href="javascript:doCategory('<?php echo $name; ?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
			<td><?php echo $name; ?></td>
		  </tr>
		 <?php
			}
			?>
		</tbody>
	</table>
</div>
<?php	
}else if($ac == "addLine")
{
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$data_id = '';
	if(isset($_REQUEST['data_id']))
	{
		$data_id = $_REQUEST['data_id'];
	}
		
	$sql = "INSERT INTO ir_data_line(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", name";
	$sql = $sql.", parent_id";
	$sql = $sql.", data_id";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", 0";
	$sql = $sql.", '".str_replace("'", "''", $name)."'";
	$sql = $sql.", '".$parent_id."'";
	$sql = $sql.", '".$data_id."'";
	$sql = $sql.")";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeLine")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE ir_data_line SET status =1";
	$sql = $sql.", write_date=NOW()";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "lineData")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT name, data FROM ir_data_line WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	if($numrows>0)
	{
		$row = $result[ 0];
		$name = $row[0];
		$columns = explode(';', $row[1]);
		$sql = "SELECT * FROM ".$name." WHERE 1=0";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		$fields =[];	
		$i = count($dt->getColumns());
		for ($j = 0; $j < $i; $j++) {
			$fields[$j] = $dt->getColumns()[$j]->getName();
		}
		?>
		<?php
		for($i=0; $i<count($columns); $i++)
		{
			if($columns[$i] != "")
			{
		?>
		<?php echo $columns[$i];?><a href="javascript:removeColumn('<?php echo $columns[$i];?>', '<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a>&nbsp;&nbsp;
		<?php
			}
		}
		?>
		<select class="select" onchange="addColumn(this, '<?php echo $id;?>')">
			<option value=""></option>
			<?php 
			for($i=0; $i<count($fields); $i++)
			{
				$hasItem = false;
				for($j =0; $j<count($columns); $j++)
				{
					if($fields[$i] == $columns[$j])
					{
						$hasItem = true;
						break;
					}
				}
				if($hasItem == false)
				{
			?>
			<option value="<?php echo $fields[$i];?>"><?php echo $fields[$i];?></option>
			<?php
				}
			}
			?>
			
		</select>
		<?php
		 
		 
	}
}else if($ac == "addColumn")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sql = "SELECT data FROM ir_data_line WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$data = "";
	if($numrows>0)
	{
		$row = $result[0];
		$data = $row[0];
	}
	if($data != "")
	{
		$data = $data.";";
	}
	$data = $data.$name;
	$sql = "UPDATE ir_data_line SET write_date=NOW()";
	$sql = $sql.", data='".str_replace("'", "''", $data)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeColumn")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sql = "SELECT data FROM ir_data_line WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	
	if($numrows>0)
	{
		$row =$result[0];
		$columns = explode(';', $row[0]);
		$data = "";
		for($i =0; $i<count($columns); $i++)
		{
			if($columns[$i] != $name)
			{
				if($data != "")
				{
					$data = $data.";";
				}
				$data = $data.$columns[$i];
			}
		}
		$sql = "UPDATE ir_data_line SET write_date=NOW()";
		$sql = $sql.", data='".str_replace("'", "''", $data)."'";
		$sql = $sql." WHERE id='".$id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	
	}
	echo "OK";
}else if($ac == "params")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
	
	?>
	<a href="javascript:addParam()">Params: <img src="<?php echo URL;?>assets/img/add.png"></a>
		<div class="row">
			<div class="col container m12">
				<table width="100%">
				<?php
				$sql = "SELECT id, name, type, data FROM parameter WHERE status =0 AND rel_id='".$id."' ORDER BY create_date ASC";
				$msg->add("query", $sql);
				$result = $appSession->getTier()->getArray($msg);
				$numrows = count($result);	
				for($i=0; $i<$numrows; $i++)
				{
					$row =$result[$i];
					$_id = $row[0];
					$name = $row[1];
					$type = $row[2];
					$data = $row[3];

					
				?>
				<tr>
					<td style="width:150px"><input type="text" class="input" value="<?php echo $name;?>"  id="paramkey<?php echo $i;?>" onblur="saveParam(<?php echo $i;?>, '<?php echo $_id;?>')"></td>
					<td style="width:150px">
					<select type="text" class="input" id="paramtype<?php echo $i;?>" onchange="saveParam(<?php echo $i;?>, '<?php echo $_id;?>')">
					<option value=""></option>
					<option value="number" <?php if($type == "number"){ echo " selected "; }?>>Number</option>
					<option value="date" <?php if($type == "date"){ echo " selected "; }?>>Date</option>
					</select>
					</td>
					<td><input type="text" class="input" value="<?php echo $data;?>" id="paramvalue<?php echo $i;?>" onblur="saveParam(<?php echo $i;?>, '<?php echo $_id;?>')"></td>
					<td style="width:20" ><a href="javascript:removeParam('<?php echo $_id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
				</tr>
				<?php
				
				}
				?>
				</table>
			</div>
			
		</div>
		<script>
			function addParam()
			{
				var _url = '<?php echo URL;?>addons/data/index.php?ac=addParam';
				_url = _url + '&rel_id=<?php echo $id;?>';
				
				loadPage('contentView', _url, function(status, message)
				{
					if(status== 0)
					{
						if(message == "OK")
						{
							loadParams();
						}else
						{
							alert(message);
						}
						
					}
					
				}, true);
			}
			function saveParam(index, id)
			{
				var ctr = document.getElementById("paramkey" + index);
				var name = ctr.value;
				
				ctr = document.getElementById("paramtype" + index);
				var type = ctr.value;
				ctr = document.getElementById("paramvalue" + index);
				var value = ctr.value;
				
				var _url = '<?php echo URL;?>admin/developer/data/?ac=saveParam';
				_url = _url + '&id=' + id;
				_url = _url + "&name=" + encodeURIComponent(name);
				_url = _url + "&type=" + encodeURIComponent(type);
				_url = _url + "&data=" + encodeURIComponent(value);
			
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
			function removeParam(id)
			{
				var result = confirm("Want to delete?");
				if (!result) {
					return;
				}
				var _url = '<?php echo URL;?>admin/developer/data/?ac=removeParam&id=' + id;	
				loadPage('contentView', _url, function(status, message)
				{
					if(status== 0 )
					{
						if(message == "OK")
						{
							loadParams();
						}else
						{
							alert(message);
						}
						
					}
					
				}, true);
				
			}
		</script>
	<?php
}else if($ac == "addParam")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$id = $appSession()->getTool()->getId();
	$sql = "INSERT INTO parameter(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", rel_id";
	$sql = $sql.", name";
	$sql = $sql.", type";
	$sql = $sql.", data";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", NOW()";
	$sql = $sql.", NOW()";
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", ''";
	$sql = $sql.", ''";
	$sql = $sql.", ''";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeParam")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE parameter SET status =1, write_date=NOW()";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "saveParam")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$sql = "UPDATE parameter SET write_date=NOW()";
	$sql = $sql.", name='".str_replace("'", "''", $name)."'";
	$sql = $sql.", type='".str_replace("'", "''", $type)."'";
	$sql = $sql.", data='".str_replace("'", "''", $data)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	echo "OK";
}else if($ac == "where")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
			
	$sql = "SELECT condition FROM ir_data WHERE id='".$id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$conditions = [];
	if($numrows>0)
	{
		$row =$result[0];
		$conditions = explode(',', $row[0]);
		
	}
	?>
		<div class="row">
			<div class="col container m12">
				<table cellSpacing="0" cellPadding="0" width="100%" border="0">
				<tr>
				<td></td>
				</tr>
				<tr>
					<td height="23">Where<a href="javascript:addCondition(0)"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
				</tr>
				
				<tr>
					<td>
						<div id="condition"></div>
					</td>
				</tr>
			</table>
			</div>
			
		</div>

		<script type="text/javascript">
			function FilterLine(filter_id, parent_id, column_name, func, op, value, logic)
			{
				this.filter_id = filter_id;
				this.parent_id = parent_id;
				this.column_name = column_name;
				this.func = func;
				this.op = op;
				this.value = value;
				this.logic = logic;
			}
			var mParams = new Array();
			
			var mColumns = new Array();
			function Column(Name, Caption, Type)
			{
				this.Name = Name;
				this.Caption = Caption;
				this.Type = Type;
			}
			mParams.push(new Column("{rel_id}", "rel_id", ""));
			mParams.push(new Column("{company}", "company", ""));
			mParams.push(new Column("{parent_company}", "parent_company", ""));
			mParams.push(new Column("{child_company}", "child_company", ""));
			mParams.push(new Column("{root_company}", "root_company", ""));
			mParams.push(new Column("{companies}", "companies", ""));
			mParams.push(new Column("{assigned}", "assigned", ""));
			mParams.push(new Column("{user_id}", "user_id", ""));
			mParams.push(new Column("{category_id}", "category_id", ""));
			<?php
			$sql = "SELECT id, name, type, data FROM parameter WHERE status =0 AND rel_id='".$id."' ORDER BY create_date ASC";
			
			$result = pg_exec($db, $sql);
			$numrows = pg_numrows($result);	
			for($i=0; $i<$numrows; $i++)
			{
				$row = pg_fetch_array($result, $i);
				$_id = $row["id"];
				$name = $row["name"];
				$type = $row["type"];
				$data = $row["data"];
			?>
			mParams.push(new Column("{<?php echo $name;?>}", "<?php echo $name;?>", "<?php echo $type;?>"));
			<?php
			}
			?>
			<?php
			$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$id."' AND status =0";
	
			$result = pg_exec($db, $sql);
			$numrows = pg_numrows($result);	
			for($i =0; $i<$numrows; $i++)
			{
				$row = pg_fetch_array($result, $i);
				$name = $row["name"];
				$sql = "SELECT d1.name, d1.type, d1.size FROM ir_database_table_column d1 LEFT OUTER JOIN ir_database_table d2 ON(d1.table_id = d2.id) WHERE d2.name='".$name."' AND d1.status =0";
				$sql = $sql." ORDER BY d1.name ASC";
				
				$result1 = pg_exec($db, $sql);
				$numrows1 = pg_numrows($result1);	
				
				for($n=0; $n<$numrows1; $n++)
				{
					$row1 = pg_fetch_array($result1, $n);
					$column_name = $row1["name"];
					$column_type = $row1["type"];
					$column_size = $row1["size"];
				?>
				mColumns.push(new Column("<?php echo $name;?>.<?php echo $column_name;?>", "<?php echo $name;?>.<?php echo $column_name;?>", "<?php echo $column_type;?>"));
				<?php
				}
			}
			?>
			var operator_string=[
			["equal", "equal"],
			["not_equal", "not equal"],
			["in", "in"],
			["not_in", "not in"],
			["less", "less"],
			["less_or_equal", "less or equal"],
			["greater", "greater"],
			["greater_or_equal", "greater or equal"],
			["begins_with", "begins with"],
			["not_begins_with", "doesn't begin with"],
			["contains", "contains"],
			["not_contains", "doesn't contain"],
			["ends_with", "ends with"],
			["not_ends_with", "doesn't end with"],
			["is_empty", "is empty"],
			["is_not_empty", "is not empty"],
			["is_null", "is null"],
			["is_not_null", "is not null"]
			];
			var operator_number=[
			["equal", "equal"],
			["not_equal", "not equal"],
			["less", "less"],
			["less_or_equal", "less or equal"],
			["greater", "greater"],
			["greater_or_equal", "greater or equal"],
			["is_null", "is null"],
			["is_not_null", "is not null"]
			];
		
			var mFilterLines = new Array();
			<?php
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
					
					$logic = "";
					$lines = explode(';', $conditions[$i]);
					for($j=0; $j<count($lines); $j++)
					{
						$index = strpos($lines[$j], '='); if($index === false) $index = -1;
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
							}
						}
					}
					?>
					mFilterLines.push(new FilterLine(<?php echo $filter_id;?>, <?php echo $parent_id;?>, "<?php echo $column_name;?>", "<?php echo $func;?>", "<?php echo $o;?>", "<?php echo $value;?>", "<?php echo $logic;?>"));
					<?php
				}
			}
			?>
			
			function TravelColumn(parent_id, space)
			{
				var str = "";
				for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					var selected = "";
					if (line.parent_id == parent_id)
					{
						str += '<div class="row">';
						str += '<div class="col m12" style="padding-left:' + space + 'px">';
						str += "<table border =\"0\" style=\"border:#CCCCCC\"><tr>";
						
						str += "<td><select class='select' id=\"cbLogic_" + line.filter_id + "\" onchange=\"cbLogicChanged('" + line.filter_id + "')\">";
						if (line.logic == "AND")
						{
							selected = "selected ";
						}
						
						str += "<option value=\"AND\" " + selected + ">AND</option>";
						selected = "";
						if (line.logic == "OR")
						{
							selected = "selected ";
						}
						str += "<option value=\"OR\" " + selected + ">OR</option>";
						selected = "";
						str += "</select></td>";
						
						str += "<td><select class=\"select\" id=\"cbColumnName_" + line.filter_id + "\" onchange=\"cbColumnNameChanged('" + line.filter_id + "')\">";
						str += "<option value=\"\"></option>";
						var selectedColumn = null;
						for (var j = 0; j < mColumns.length; j++)
						{
							var c = mColumns[j];
							if (c.Name == line.column_name)
							{
								selectedColumn = c;
								selected = "selected";
							}
							str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
							selected = "";
						}
						str += "</select></td>";
						if(selectedColumn != null  && selectedColumn.Name != "")
						{
							if (selectedColumn.Type == "datetime")
							{
								str += "<td><select class='select' id=\"cbFunction_" + line.filter_id + "\" onchange=\"cbFunctionChanged('" + line.filter_id + "')\">";

								if (line.func == "")
								{
									selected = "selected ";
								}
								str += "<option value = \"\" " + selected + "></opition>";
								selected = "";
								if (line.func == "YEAR")
								{
									selected = "selected ";
								}
								str += "<option value = \"YEAR\" " + selected + ">YEAR</opition>";
								selected = "";
								if (line.func == "MONTH")
								{
									selected = "selected ";
								}
								str += "<option value = \"MONTH\" " + selected + ">MONTH</opition>";
								selected = "";
								if (line.func == "DAY")
								{
									selected = "selected ";
								}
								str += "<option value = \"DAY\" " + selected + ">DAY</opition>";
								selected = "";
								str += "</select></td>";
							}
							
							str += "<td><select class='select' id=\"cbOperator_" + line.filter_id + "\" onchange=\"cbOperatorChanged('" + line.filter_id + "')\">";
							str += "<option value=''></option>";
							var type = selectedColumn.Type;
							
							var ops = operator_string;

							if (type =="smallint" || type=="number" || type =="int" || type == "datetime" || type=="time")
							{
								ops = operator_number;
							}
							for(var j=0; j<ops.length; j++)
							{
								selected = "";
								if (line.op == ops[j][0])
								{
									selected = "selected ";
								}
								str += "<option value='"+ ops[j][0] +"' " + selected + ">" + ops[j][1] + "</option>";
								
								
							}
							

							str += "</select></td>";
							
		
							
							str += " <td><select class='select' id=\"cbColumnValue_1_" + line.filter_id + "\" onchange=\"cbColumnValue_1Changed('" + line.filter_id + "')\">";
							str += "<option value=\"\"></option>";
							for (var j = 0; j < mParams.length; j++)
							{
								var c = mParams[j];
								if (line.value == c.Name)
								{
									selected = " selected ";
								}
								str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
								selected = "";
							}
							for (var j = 0; j < mColumns.length; j++)
							{
								var c = mColumns[j];
								if (line.value == c.Name)
								{
									selected = " selected ";
								}
								str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
								selected = "";
							}
							str += "</select></td>";
							
							str += "";
						}

						
						
						
						str += "<td>";
						str += '<a href="javascript:addCondition('+line.filter_id+')"><img src="<?php echo URL;?>assets/img/add.png"></a>';
						str += "</td>";
						str += "<td>";
						str += '<a href="javascript:removeCondition('+line.filter_id+')"><img src="<?php echo URL;?>assets/img/remove.png"></a>';
						str += "</td>";
						str += "</tr>";
						
						str += "</table></div></div>";
						var innerStr = TravelColumn(line.filter_id, space + 20);
						if (innerStr != "")
						{
							str +=  innerStr;
						}
					}
				}
				
				return str;
			}
			function buidWhere()
			{
				
				document.getElementById('condition').innerHTML =TravelColumn(0, 0);
			}
		
		    function addCondition(parent_id) {
				var id = 0;
				for(var i=0; i<mFilterLines.length; i++)
				{
					if(mFilterLines[i].filter_id>id)
					{
						id = mFilterLines[i].filter_id;
					}
				}
				id = id + 1;
				var item = new FilterLine(id, parent_id, "", "", "", "", "",  "AND");
				mFilterLines.push(item);
				buidWhere();
		        
		    }
			function findTree(id)
			{
				var s = "";
				for(var i=0; i<mFilterLines.length; i++)
				{
					if(mFilterLines[i].parent_id == id)
					{
						s = s + "[" + mFilterLines[i].filter_id + "]";
						s = s + findTree(mFilterLines[i].filter_id);
					}
				}
				return s;
			}
			function removeCondition(id)
			{
				var arr = Array();
				var s = "[" + id + "]" + findTree(id);
				for(var i=0; i<mFilterLines.length; i++)
				{
					if(s.indexOf("[" + mFilterLines[i].filter_id + "]") == -1)
					{
						arr.push(mFilterLines[i]);
					}
				}
				mFilterLines = arr;
				buidWhere();
				saveConidition();
				
			}
		    
			function cbLogicChanged(id)
			{
				
		        var cb = document.getElementById('cbLogic_' + id);
			
				for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.logic = cb.value;
						buidWhere();
						saveConidition();
						break;
					}
				}
			}
		    function cbColumnNameChanged(id) {
		        var cb = document.getElementById('cbColumnName_' + id);
			
				for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.column_name = cb.value;
						buidWhere();
						saveConidition();
						break;
					}
				}
		        
		    }
		    function cbOperatorChanged(id) {
		        var cb = document.getElementById('cbOperator_' + id);
		        
		        for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.op = cb.value;
						buidWhere();
						saveConidition();
					}
				}
		    }
		   
		    function cbColumnValue_1Changed(id) {
		        var cb = document.getElementById('cbColumnValue_1_' + id);
		        
		        for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.value = cb.value;
						buidWhere();
						saveConidition();
					}
				}
		    }
		   
		   
		  
		   
		  
		    function tfFunctionValueBlur(id) {
		        var cb = document.getElementById('tfFunctionValue_' + id);
		        for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.func = cb.value;
						buidWhere();
						saveConidition();
					}
				}
		    }
		    function cbFunctionChanged(id) {
		        var cb = document.getElementById('cbFunction_' + id);
		        
		       for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(line.filter_id == id)
					{
						line.func = cb.value;
						buidWhere();
						saveConidition();
					}
				}
		    }
			function saveConidition()
			{
				var data = "";
				for(var i=0; i<mFilterLines.length; i++)
				{
					var line = mFilterLines[i];
					if(data != "")
					{
						data = data + ",";
					}
					data = data + "i=" +line.filter_id + ";p=" +line.parent_id + ";n=" +line.column_name + ";f=" +line.func + ";o=" +line.op + ";v=" +line.value + ";l=" + line.logic;
				}
				var _url = '<?php echo URL;?>addons/data/index.php?ac=saveConidition';
				_url = _url + '&id=<?php echo $id;?>';
				_url = _url + "&data=" + encodeURIComponent(data);
				
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
			buidWhere();
		    
		</script>
		
	<?php
}else if($ac == "saveConidition")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$sql = "UPDATE ir_data SET write_date=NOW()";
	$sql = $sql.", condition='".str_replace("'", "''", $data)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
else if($ac == "relationship")
{
	$pageIndex = round(microtime(true) * 1000);
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$parent_name = '';
	if(isset($_REQUEST['parent_name']))
	{
		$parent_name = $_REQUEST['parent_name'];
	}
	$sql = "SELECT relationship FROM ir_data_line WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$conditions = [];
	if($numrows>0)
	{
		$row = $result[0];
		$conditions = explode(',', $row[0]);
	}
	?>
		<div class="row">
			<div class="col container m12">
				<table cellSpacing="0" cellPadding="0" width="100%" border="0">
				<tr>
				<td></td>
				</tr>
				<tr>
					<td height="23">Relation<a href="javascript:addCondition<?php echo $pageIndex;?>(0)"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
				</tr>
				
				<tr>
					<td>
						<div id="condition<?php echo $pageIndex;?>"></div>
					</td>
				</tr>
			</table>
			</div>
			
		</div>

		<script type="text/javascript">
			function FilterLine(filter_id, parent_id, column_name, func, op, value, logic)
			{
				this.filter_id = filter_id;
				this.parent_id = parent_id;
				this.column_name = column_name;
				this.func = func;
				this.op = op;
				this.value = value;
				this.logic = logic;
			}
			var mParams<?php echo $pageIndex;?> = new Array();
			
			var mColumns<?php echo $pageIndex;?> = new Array();
			function Column(Name, Caption, Type)
			{
				this.Name = Name;
				this.Caption = Caption;
				this.Type = Type;
			}
			<?php
			$sql = "SELECT id, name, type, data FROM parameter WHERE status =0 AND rel_id='".$id."' ORDER BY create_date ASC";
			$result = pg_exec($db, $sql);
			$numrows = pg_numrows($result);	
			for($i=0; $i<$numrows; $i++)
			{
				$row = pg_fetch_array($result, $i);
				$_id = $row["id"];
				$name = $row["name"];
				$type = $row["type"];
				$data = $row["data"];
			?>
			mParams<?php echo $pageIndex;?>.push(new Column("{<?php echo $name;?>}", "<?php echo $name;?>", "<?php echo $type;?>"));
			<?php
			}
			?>
			<?php
			$names = [$parent_name, $name];
			for($i =0; $i<count($names); $i++)
			{
				
				$name = $names[$i];
				$table_index = 0;
				for($j =0; $j<$i; $j++)
				{
					if($names[$j] == $name)
					{
						$table_index = $table_index + 1;
					}
				}
				
				$sql = "SELECT d1.name, d1.type, d1.size FROM ir_database_table_column d1 LEFT OUTER JOIN ir_database_table d2 ON(d1.table_id = d2.id) WHERE d2.name='".$name."' AND d1.status =0";
				$sql = $sql." ORDER BY d1.name ASC";
				
				$result1 = pg_exec($db, $sql);
				$numrows1 = pg_numrows($result1);	
				
				if($table_index>0)
				{
					$name = $name."".$table_index;
				}
				
				for($n=0; $n<$numrows1; $n++)
				{
					$row1 = pg_fetch_array($result1, $n);
					$column_name = $row1["name"];
					$column_type = $row1["type"];
					$column_size = $row1["size"];
	
				?>
				mColumns<?php echo $pageIndex;?>.push(new Column("<?php echo $name;?>.<?php echo $column_name;?>", "<?php echo $name;?>.<?php echo $column_name;?>", "<?php echo $column_type;?>"));
				<?php
				}
			}
			?>
			var operator_string<?php echo $pageIndex;?>=[
			["equal", "equal"],
			["not_equal", "not equal"],
			["in", "in"],
			["not_in", "not in"],
			["less", "less"],
			["less_or_equal", "less or equal"],
			["greater", "greater"],
			["greater_or_equal", "greater or equal"],
			["begins_with", "begins with"],
			["not_begins_with", "doesn't begin with"],
			["contains", "contains"],
			["not_contains", "doesn't contain"],
			["ends_with", "ends with"],
			["not_ends_with", "doesn't end with"],
			["is_empty", "is empty"],
			["is_not_empty", "is not empty"],
			["is_null", "is null"],
			["is_not_null", "is not null"]
			];
			var operator_number<?php echo $pageIndex;?>=[
			["equal", "equal"],
			["not_equal", "not equal"],
			["less", "less"],
			["less_or_equal", "less or equal"],
			["greater", "greater"],
			["greater_or_equal", "greater or equal"],
			["is_null", "is null"],
			["is_not_null", "is not null"]
			];
		
			var mFilterLines<?php echo $pageIndex;?> = new Array();
			<?php
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
					$lines = explode(';', $conditions[$i]);
					for($j=0; $j<count($lines); $j++)
					{
						$index = strpos($lines[$j], '='); if($index === false) $index = -1;
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
							}
						}
					}
					?>
					mFilterLines<?php echo $pageIndex;?>.push(new FilterLine(<?php echo $filter_id;?>, <?php echo $parent_id;?>, "<?php echo $column_name;?>", "<?php echo $func;?>", "<?php echo $o;?>", "<?php echo $value;?>", "<?php echo $logic;?>"));
					<?php
				}
			}
			?>
			
			function TravelColumn<?php echo $pageIndex;?>(parent_id, space)
			{
				var str = "";
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					var selected = "";
					if (line.parent_id == parent_id)
					{
						str += '<div class="row">';
						str += '<div class="col m12" style="padding-left:' + space + 'px">';
						str += "<table border =\"0\" style=\"border:#CCCCCC\"><tr>";
						
						str += "<td><select class='select' id=\"cbLogic<?php echo $pageIndex;?>_" + line.filter_id + "\" onchange=\"cbLogicChanged('" + line.filter_id + "')\">";
						if (line.logic == "AND")
						{
							selected = "selected ";
						}
						
						str += "<option value=\"AND\" " + selected + ">AND</option>";
						selected = "";
						if (line.logic == "OR")
						{
							selected = "selected ";
						}
						str += "<option value=\"OR\" " + selected + ">OR</option>";
						selected = "";
						str += "</select></td>";
						
						str += "<td><select class=\"select\" id=\"cbColumnName<?php echo $pageIndex;?>_" + line.filter_id + "\" onchange=\"cbColumnNameChanged<?php echo $pageIndex;?>('" + line.filter_id + "')\">";
						str += "<option value=\"\"></option>";
						var selectedColumn = null;
						for (var j = 0; j < mColumns<?php echo $pageIndex;?>.length; j++)
						{
							var c = mColumns<?php echo $pageIndex;?>[j];
							if (c.Name == line.column_name)
							{
								selectedColumn = c;
								selected = "selected";
							}
							str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
							selected = "";
						}
						str += "</select></td>";
						if(selectedColumn != null  && selectedColumn.Name != "")
						{
							if (selectedColumn.Type == "datetime")
							{
								str += "<td><select class='select' id=\"cbFunction<?php echo $pageIndex;?>_" + line.filter_id + "\" onchange=\"cbFunctionChanged<?php echo $pageIndex;?>('" + line.filter_id + "')\">";

								if (line.func == "")
								{
									selected = "selected ";
								}
								str += "<option value = \"\" " + selected + "></opition>";
								selected = "";
								if (line.func == "YEAR")
								{
									selected = "selected ";
								}
								str += "<option value = \"YEAR\" " + selected + ">YEAR</opition>";
								selected = "";
								if (line.func == "MONTH")
								{
									selected = "selected ";
								}
								str += "<option value = \"MONTH\" " + selected + ">MONTH</opition>";
								selected = "";
								if (line.func == "DAY")
								{
									selected = "selected ";
								}
								str += "<option value = \"DAY\" " + selected + ">DAY</opition>";
								selected = "";
								str += "</select></td>";
							}
							
							str += "<td><select class='select' id=\"cbOperator<?php echo $pageIndex;?>_" + line.filter_id + "\" onchange=\"cbOperatorChanged<?php echo $pageIndex;?>('" + line.filter_id + "')\">";
							str += "<option value=''></option>";
							var type = selectedColumn.Type;
							
							var ops = operator_string<?php echo $pageIndex;?>;

							if (type =="smallint" || type=="number" || type =="int" || type == "datetime" || type=="time")
							{
								ops = operator_number<?php echo $pageIndex;?>;
							}
							for(var j=0; j<ops.length; j++)
							{
								selected = "";
								if (line.op == ops[j][0])
								{
									selected = "selected ";
								}
								str += "<option value='"+ ops[j][0] +"' " + selected + ">" + ops[j][1] + "</option>";
								
								
							}
							

							str += "</select></td>";
							
							
							
							
							str += " <td><select class='select' id=\"cbColumnValue<?php echo $pageIndex;?>_1_" + line.filter_id + "\" onchange=\"cbColumnValue_1Changed<?php echo $pageIndex;?>('" + line.filter_id + "')\">";
							str += "<option value=\"\"></option>";
							for (var j = 0; j < mParams<?php echo $pageIndex;?>.length; j++)
							{
								var c = mParams<?php echo $pageIndex;?>[j];
								if (line.value == c.Name)
								{
									selected = " selected ";
								}
								str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
								selected = "";
							}
							for (var j = 0; j < mColumns<?php echo $pageIndex;?>.length; j++)
							{
								var c = mColumns<?php echo $pageIndex;?>[j];
								if (line.value == c.Name)
								{
									selected = " selected ";
								}
								str += "<option value=\"" + c.Name + "\" " + selected + ">" + c.Caption + "</option>";
								selected = "";
							}
							str += "</select></td>";
			
						}

						
						
						
						str += "<td>";
						str += '<a href="javascript:addCondition<?php echo $pageIndex;?>('+line.filter_id+')"><img src="<?php echo URL;?>assets/img/add.png"></a>';
						str += "</td>";
						str += "<td>";
						str += '<a href="javascript:removeCondition<?php echo $pageIndex;?>('+line.filter_id+')"><img src="<?php echo URL;?>assets/img/remove.png"></a>';
						str += "</td>";
						str += "</tr>";
						
						str += "</table></div></div>";
						var innerStr = TravelColumn<?php echo $pageIndex;?>(line.filter_id, space + 20);
						if (innerStr != "")
						{
							str +=  innerStr;
						}
					}
				}
				
				return str;
			}
			function buidWhere<?php echo $pageIndex;?>()
			{
				
				document.getElementById('condition<?php echo $pageIndex;?>').innerHTML =TravelColumn<?php echo $pageIndex;?>(0, 0);
			}
		
		    function addCondition<?php echo $pageIndex;?>(parent_id) {
				var id = 0;
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					if(mFilterLines<?php echo $pageIndex;?>[i].filter_id>id)
					{
						id = mFilterLines<?php echo $pageIndex;?>[i].filter_id;
					}
				}
				id = id + 1;
				var item = new FilterLine(id, parent_id, "", "", "", "", "",  "AND");
				mFilterLines<?php echo $pageIndex;?>.push(item);
				buidWhere<?php echo $pageIndex;?>();
		        
		    }
			function findTree<?php echo $pageIndex;?>(id)
			{
				var s = "";
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					if(mFilterLines<?php echo $pageIndex;?>[i].parent_id == id)
					{
						s = s + "[" + mFilterLines<?php echo $pageIndex;?>[i].filter_id + "]";
						s = s + findTree(mFilterLines<?php echo $pageIndex;?>[i].filter_id);
					}
				}
				return s;
			}
			function removeCondition<?php echo $pageIndex;?>(id)
			{
				var arr = Array();
				var s = "[" + id + "]" + findTree<?php echo $pageIndex;?>(id);
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					if(s.indexOf("[" + mFilterLines<?php echo $pageIndex;?>[i].filter_id + "]") == -1)
					{
						arr.push(mFilterLines<?php echo $pageIndex;?>[i]);
					}
				}
				mFilterLines<?php echo $pageIndex;?> = arr;
				buidWhere<?php echo $pageIndex;?>();
				saveConidition<?php echo $pageIndex;?>();
				
			}
		    
			function cbLogicChanged<?php echo $pageIndex;?>(id)
			{
				
		        var cb = document.getElementById('cbLogic<?php echo $pageIndex;?>_' + id);
			
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.logic = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
						break;
					}
				}
			}
		    function cbColumnNameChanged<?php echo $pageIndex;?>(id) {
		        var cb = document.getElementById('cbColumnName<?php echo $pageIndex;?>_' + id);
			
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.column_name = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
						break;
					}
				}
		        
		    }
		    function cbOperatorChanged<?php echo $pageIndex;?>(id) {
		        var cb = document.getElementById('cbOperator<?php echo $pageIndex;?>_' + id);
		        
		        for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.op = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
					}
				}
		    }
		    
		    function cbColumnValue_1Changed<?php echo $pageIndex;?>(id) {
		        var cb = document.getElementById('cbColumnValue<?php echo $pageIndex;?>_1_' + id);
		        
		        for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.value = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
					}
				}
		    }
		   
		   
		  
		   
		  
		    function tfFunctionValueBlur<?php echo $pageIndex;?>(id) {
		        var cb = document.getElementById('tfFunctionValue<?php echo $pageIndex;?>_' + id);
		        for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.func = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
					}
				}
		    }
		    function cbFunctionChanged<?php echo $pageIndex;?>(id) {
		        var cb = document.getElementById('cbFunction<?php echo $pageIndex;?>_' + id);
		        
		       for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(line.filter_id == id)
					{
						line.func = cb.value;
						buidWhere<?php echo $pageIndex;?>();
						saveConidition<?php echo $pageIndex;?>();
					}
				}
		    }
			function saveConidition<?php echo $pageIndex;?>()
			{
				var data = "";
				for(var i=0; i<mFilterLines<?php echo $pageIndex;?>.length; i++)
				{
					var line = mFilterLines<?php echo $pageIndex;?>[i];
					if(data != "")
					{
						data = data + ",";
					}
					data = data + "i=" +line.filter_id + ";p=" +line.parent_id + ";n=" +line.column_name + ";f=" +line.func + ";o=" +line.op + ";v=" +line.value + ";l=" + line.logic;
				}
				var _url = '<?php echo URL;?>addons/data/index.php?ac=saveRelation';
				_url = _url + '&id=<?php echo $id;?>';
				_url = _url + "&data=" + encodeURIComponent(data);
				
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
			buidWhere<?php echo $pageIndex;?>();
		    
		</script>
	<?php
}else if($ac == "saveRelation")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$sql = "UPDATE ir_data_line SET write_date=NOW()";
	$sql = $sql.", relationship='".str_replace("'", "''", $data)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "having")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT group_by FROM ir_data WHERE id='".$id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$group_by = [];
	if($numrows>0)
	{
		$row = $result[0];
		$group_by = explode(',', $row[0]);
	}
	
?>
<div class="row">
	<div class="col container m1">
	Group:
	</div>
	<div class="col container m10">
		<?php
		for($j=0; $j<count($group_by); $j++)
		{
			if($group_by[$j] != "")
			{
		?>
			<?php echo $group_by[$j];?><a href="javascript:removeHaving('<?php echo $group_by[$j];?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a>
		<?php
			}
		}
		?>
		
		<table><tr><td>
		
		<select class="select" id="havingColumn">
<?php
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$id."' AND status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	for($i =0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		$name = $row[2];
		$sql = "SELECT d1.name, d1.type, d1.size FROM ir_database_table_column d1 LEFT OUTER JOIN ir_database_table d2 ON(d1.table_id = d2.id) WHERE d2.name='".$name."' AND d1.status =0";
		$sql = $sql." ORDER BY d1.name ASC";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		$numrows1 = count($result1);	
		
		for($n=0; $n<$numrows1; $n++)
		{
			$row1 = $result1[$n];
			$column_name = $row1[0];
			$column_type = $row1[1];
			$column_size = $row1[2];
			
			$hasItem = false;
			for($j=0; $j<count($group_by); $j++)
			{
				$find = $name.".".$column_name;
				if($group_by[$j] == $find)
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
		?>
		<option value="<?php echo $name;?>.<?php echo $column_name;?>"><?php echo $name;?>.<?php echo $column_name;?></option>
		<?php
			}
		}
	}
	?>
		</select>
		</td>
		<td><a href="javascript:addHaving()"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
		</tr>
		</table>
		</div>
		
	</div>
	<script>
		function addHaving()
		{
			var ctr = document.getElementById('havingColumn');
			if(ctr.value == '')
			{
				alert("Select column");
				ctr.focus();
				return;
			}
			var _url = '<?php echo URL;?>addons/data/index.php?ac=addHaving';
			_url = _url + '&id=<?php echo $id;?>';
			_url = _url + "&column=" + ctr.value;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						loadHaving();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function removeHaving(column)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
		
			var _url = '<?php echo URL;?>addons/data/index.php?ac=removeHaving';
			_url = _url + '&id=<?php echo $id;?>';
			_url = _url + "&column=" + column;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						loadHaving();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
	</script>
	<?php
	
}else if($ac == "addHaving")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$column = '';
	if(isset($_REQUEST['column']))
	{
		$column = $_REQUEST['column'];
	}
	$sql = "SELECT group_by FROM ir_data WHERE id='".$id."' AND status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	$group_by = "";
	if($numrows>0)
	{
		$row = $result[0];
		$group_by = $row[0];
	}
	if($group_by !="")
	{
		$group_by = $group_by.",";
	}
	$group_by = $group_by.$column;
	
	$sql = "UPDATE ir_data SET write_date=NOW()";
	$sql = $sql.", group_by='".str_replace("'", "''", $group_by)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeHaving")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$column = '';
	if(isset($_REQUEST['column']))
	{
		$column = $_REQUEST['column'];
	}
	$sql = "SELECT group_by FROM ir_data WHERE id='".$id."' AND status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	$arr = [];
	if($numrows>0)
	{
		$row = $result[0];
		$arr = explode(',', $row[0]);
	}
	$s = "";
	for($i=0; $i<count($arr); $i++)
	{
		if($arr[$i] != "")
		{
			if($arr[$i] != $column)
			{
				if($s != "")
				{
					$s = $s.",";
				}
				$s = $s.$arr[$i];
			}
		}
	}
	$sql = "UPDATE ir_data SET write_date=NOW()";
	$sql = $sql.", group_by='".str_replace("'", "''", $s)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
else if($ac == "order_by")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "SELECT order_by FROM ir_data WHERE id='".$id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$arr = [];
	if($numrows>0)
	{
		$row = $result[0];
		$arr = explode(',', $row[0]);
	}?>
	<div class="row">
	<div class="col container m1">
	Order By:
	</div>
	<div class="col container m10">
		<?php
		for($j=0; $j<count($arr); $j++)
		{
			if($arr[$j] != "")
			{
		?>
			<?php echo $arr[$j];?><a href="javascript:removeOrder('<?php echo $arr[$j];?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a>
		<?php
			}
		}
		?>
		
		<table><tr><td>
		
		<select class="select" id="orderByColumn">
<?php
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$id."' AND status =0";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	for($i =0; $i<$numrows; $i++)
	{
		$row = $result[$i];
		$name = $row[2];
		$sql = "SELECT d1.name, d1.type, d1.size FROM ir_database_table_column d1 LEFT OUTER JOIN ir_database_table d2 ON(d1.table_id = d2.id) WHERE d2.name='".$name."' AND d1.status =0";
		$sql = $sql." ORDER BY d1.name ASC";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		$numrows1 = count($result1);	
		
		for($n=0; $n<$numrows1; $n++)
		{
			$row1 =$result1[$n];
			$column_name = $row1[0];
			$column_type = $row1[1];
			$column_size = $row1[2];
			
			$hasItem = false;
			for($j=0; $j<count($arr); $j++)
			{
				$find = $name.".".$column_name;
				$s = str_replace(" ASC", "", $arr[$j]);
				$s = str_replace(" DESC", "", $s);
				
				if($s == $find)
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == false)
			{
		?>
		<option value="<?php echo $name;?>.<?php echo $column_name;?>"><?php echo $name;?>.<?php echo $column_name;?></option>
		<?php
			}
		}
	}
	?>
		</select>
		</td>
		<td><select class="select" id="orderByColumnSort">
				<option value="ASC">ASC</option>
				<option value="DESC">DESC</option>
			</select>
		</td>
		<td><a href="javascript:addOrderBy()"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
		</tr>
		</table>
		</div>
		
	</div>
	<script>
		function addOrderBy()
		{
			var ctr = document.getElementById('orderByColumn');
			if(ctr.value == '')
			{
				alert("Select column");
				ctr.focus();
				return;
			}
			var column = ctr.value + " " + document.getElementById('orderByColumnSort').value;
			var _url = '<?php echo URL;?>addons/data/index.php?ac=addOrderBy';
			_url = _url + '&id=<?php echo $id;?>';
			_url = _url + "&column=" + column;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						loadOrderBy();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function removeOrder(column)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
		
			var _url = '<?php echo URL;?>addons/data/index.php?ac=removeOrder';
			_url = _url + '&id=<?php echo $id;?>';
			_url = _url + "&column=" + column;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						loadOrderBy();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
	</script>
	<?php
}else if($ac == "addOrderBy")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$column = '';
	if(isset($_REQUEST['column']))
	{
		$column = $_REQUEST['column'];
	}
	$sql = "SELECT order_by FROM ir_data WHERE id='".$id."' AND status =0";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$order_by = "";
	if($numrows>0)
	{
		$row = $result[0];
		$order_by = $row[0];
	}
	if($order_by !="")
	{
		$order_by = $order_by.",";
	}
	$order_by = $order_by.$column;
	
	$sql = "UPDATE ir_data SET write_date=NOW()";
	$sql = $sql.", order_by='".str_replace("'", "''", $order_by)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "removeOrder")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$column = '';
	if(isset($_REQUEST['column']))
	{
		$column = $_REQUEST['column'];
	}
	$sql = "SELECT order_by FROM ir_data WHERE id='".$id."' AND status =0";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$arr = [];
	if($numrows>0)
	{
		$row =$result[0];
		$arr = explode(',', $row[0]);
	}
	$s = "";
	for($i=0; $i<count($arr); $i++)
	{
		if($arr[$i] != "")
		{
			if($arr[$i] != $column)
			{
				if($s != "")
				{
					$s = $s.",";
				}
				$s = $s.$arr[$i];
			}
		}
	}
	$sql = "UPDATE ir_data SET write_date=NOW()";
	$sql = $sql.", order_by='".str_replace("'", "''", $s)."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "saveResource")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$resource = "";
	if(isset($_REQUEST['resource']))
	{
		$resource = $_REQUEST['resource'];
	}

	$sql = "SELECT id FROM res_resource WHERE rel_id='".$rel_id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	$id = "";
	if($numrows == 0)
	{
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO res_resource(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}else{
		$row = $result[0];
		$id = $row[0];
	}
	$sql = "UPDATE res_resource SET write_date=NOW()";
	$sql = $sql.", resource='".str_replace("'", "''", $resource)."'";
	$sql = $sql." WHERE id ='".$id."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>

	