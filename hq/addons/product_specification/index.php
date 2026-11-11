<?php
$msg = $appSession->getTier()->createMessage();
$rel_id = "";
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$pageIndex = 0;
if(isset($_REQUEST['pageIndex']))
{
	$pageIndex = $_REQUEST['pageIndex'];
}
if($ac == "view")
{
	
?>
<a href="javascript:addSpecification<?php echo $pageIndex;?>();"><i class="icon-plus"></i></a>
<br>
<div id="pnSpecificationList<?php echo $pageIndex;?>" style="padding-top:5px"></div>
<script>
function loadSpecificationList<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/product_specification/?ac=list&rel_id=<?php echo $rel_id;?>&pageIndex=<?php echo $pageIndex;?>';
	loadPage('pnSpecificationList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}

loadSpecificationList<?php echo $pageIndex;?>();
function addSpecification<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/product_specification/?ac=add&rel_id=<?php echo $rel_id;?>&pageIndex=<?php echo $pageIndex;?>';
	
	loadPage('pnSpecificationList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadSpecificationList<?php echo $pageIndex;?>();
		}
		
	}, true);
}
function removeSpecification<?php echo $pageIndex;?>(id)
{
	var _url = '<?php echo URL;?>addons/product_specification/?ac=del&rel_id=<?php echo $rel_id;?>&pageIndex=<?php echo $pageIndex;?>&id=' + id;
	
	loadPage('pnMetaList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadSpecification<?php echo $pageIndex;?>();
		}
		
	}, false);
}
</script>
<?php
}else if($ac == "list")
{
	$sql = "SELECT id, name, unit_id, op1, value, padding_left, padding_right, result_text FROM product_specification WHERE status =0 AND rel_id='".$rel_id."' ORDER BY create_date ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	$sql = "SELECT id, name FROM product_unit WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_companay_id")."')";
	$msg->add("query", $sql);
	$unitList = $appSession->getTier()->getArray($msg);
	
	$opList =[["", ""], ["=", "="], [">", ">"], [">=", ">="], ["<", "<"], ["<=", "<="], ["select", "Select"], ["select2", "Select 2"], ["option", "Option"]];
	?>
	<div class="responsive">
	<table class="table">
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$_id = $dt->getString($i, "id");
			$_name = $dt->getString($i, "name");
			$unit_id = $dt->getString($i, "unit_id");
			$op1 = $dt->getString($i, "op1");
			$result_text = $dt->getString($i, "result_text");
			$padding_left = $dt->getFloat($i, "padding_left");
			$padding_right = $dt->getFloat($i, "padding_right");
		?>
		<tr>
			<td colspan="5"><input type="text" class="input" value="<?php echo $_name;?>" onblur="saveLine('product_specification', 'name', '', '<?php echo $_id;?>', this, function(status, message){})">
			
			
			
			
			
			
					</td>
			
			<td width="30" rowspan="2"><a href="javascript:removeSpecification<?php echo $pageIndex;?>('<?php echo $_id;?>');"><i class="icon-minus"></i></a></td>
		</tr>
		<tr>
		  <td><select class="select" style="max-width:120px" onchange="saveLine('product_specification', 'unit_id', '', '<?php echo $_id;?>', this, function(status, message){})">
			<option value=""></option>
			<?php
			for($j =0; $j<count($unitList); $j++)
			{
				$unit_unit_id = $unitList[$j][0];
				$unit_name = $unitList[$j][1];
			?>
			<option value="<?php echo $unit_unit_id;?>"  <?php if($unit_unit_id == $unit_id){ echo " selected ";}?>><?php echo $unit_name;?></option>
			<?php
			}
			?>
			</select></td>
	      <td><select class="select" style="max-width:120px" onchange="opChanged('<?php echo $_id;?>', this, <?php echo $i;?>); saveLine('product_specification', 'op1', '', '<?php echo $_id;?>', this, function(status, message){})">
				<?php
				for($j =0; $j<count($opList); $j++)
				{
					$op_id = $opList[$j][0];
					$op_name = $opList[$j][1];
				?>
				<option value="<?php echo $op_id;?>"  <?php if($op_id == $op1){ echo " selected ";}?>><?php echo $op_name;?></option>
				<?php
				}
				?>
			</select></td>
	      <td><div id="pnValue<?php echo $i;?>"></div>
			<input type="hidden" id="editresult_text<?php echo $i;?>" value="<?php echo $result_text;?>">	</td>
	      <td><input type="number" class="input" style="width:60; text-align:right" value="<?php echo $padding_left;?>" onblur="saveLine('product_specification', 'padding_left', 'number', '<?php echo $_id;?>', this, function(status, message){})"></td>
	      <td><input type="number" class="input" style="width:60; text-align:right" value="<?php echo $padding_right;?>" onblur="saveLine('product_specification', 'padding_right', 'number', '<?php echo $_id;?>', this, function(status, message){})"></td>
	  </tr>
		<?php
		}
		?>
	</table>
	</div>
	<script>
		function buildItem(id, i, op){
			var ctr = document.getElementById('editresult_text' + i);
			var value = '';
			if(ctr != undefined)
			{
				value = ctr.value;
			}
			var s = '<input type="text" class="input" value="' + value + '" onblur="saveLine(\'product_specification\', \'result_text\', \'\', \'' + id + '\', this, function(status, message){})">';
			if(op == ">" || op == ">=" || op == "<" || op == "<=")
			{
				if(numeric(value) == false)
				{
					value = 0;
				}
				s = '<input type="number" class="input" value="' + value + '" onblur="saveLine(\'product_specification\', \'result_text\', \'\', \'' + id + '\', this, function(status, message){})">';
			}else if(op == "select" || op == "select2" || op == "option")
			{
				s = '<a href="javascript:addSelect(\'' + id + '\', ' + i + ', \'' + op + '\')"><i class="icon-plus"></i></a>';
				if(value != "")
				{
					var arr = value.split(';');
					for(var j=0; j<arr.length; j++)
					{
						s = s + '<br>' + arr[j] +  ' &nbsp; <a href="javascript:removeSelect(\'' + id + '\', ' + i + ', \'' + op + '\', ' + j + ')"><i class="icon-minus"></i></a>'
					}
				}
				
			}
			
			document.getElementById('pnValue'+i).innerHTML = s;
		}
		function addSelect(id, i, op)
		{
			var name = prompt("Please enter name", "");

			if (name == null || name == "") {
			  return;
			} 
			var ctr = document.getElementById('editresult_text'+i);
			var value = ctr.value;
			if(value != "")
			{
				value = value + ";";
			}
			value = value + name;
			ctr.value = value;
		
			buildItem(id, i, op);
			saveLine('product_specification', 'result_text', '', id, ctr, function(status, message){});
		}
		function removeSelect(id, i, op, ji)
		{
			var ctr = document.getElementById('editresult_text'+i);
			var value = ctr.value;
			var arr = value.split(';');
			value = "";
			for(var j=0; j<arr.length; j++)
			{
				if(ji != j)
				{
					if(value != "")
					{
						value = value + ";";
					}
					value = value + arr[j];
				}
			}
			ctr.value = value;
			buildItem(id, i, op);
			saveLine('product_specification', 'result_text', '', id, ctr, function(status, message){});
		}
		function opChanged(id, theInput, i)
		{
			
			var op = theInput.value;
			buildItem(id, i, op);
		}
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$_id = $dt->getString($i, "id");
			$op1 = $dt->getString($i, "op1");
			?>
			 buildItem('<?php echo $_id;?>', <?php echo $i;?>, '<?php echo $op1;?>');
			<?php
		}
		?>
	</script>
	<?php
	
	
}else if($ac == "add")
{
	$builder = $appSession->getTier()->createBuilder("product_specification");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("rel_id", $rel_id);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);

	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE product_specification SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}
?>
