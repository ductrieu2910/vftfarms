<?php

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "langs";
}
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());
if($ac == "langs")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$pageIndex = 1;
	if($rel_id == "ROOT")
	{
		$pageIndex = 0;
	}
	$sql = "SELECT id, name FROM res_lang WHERE status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	?>
	<div class="row">
		<div class="col m12">
			  <label for="edittype" class="label">Language:</label>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col m8">
			<select class="select" id="editlang<?php echo $pageIndex;?>">
				<?php
				for($i =0; $i<$numrows; $i++)
				{
					$row = $result[$i];
				?>
				<option value="<?php echo $row[0];?>"><?php echo $row[1];?></option>
				<?php
				}
				?>
			  </select>
		</div>
		<div class="col m4">
		<a class="button btn-default" href="javascript:viewLang<?php echo $pageIndex;?>()">View</a>
		<a class="button btn-default" href="javascript:cloneLine<?php echo $pageIndex;?>()">Clone</a>
		<a class="button btn-default" href="javascript:viewLangContent<?php echo $pageIndex;?>()">Content</a>
		
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col m12">
			<div id="pnLang<?php echo $pageIndex;?>"></div>
		</div>
	</div>
	<script>
		var lang_id = '';
		function viewLang<?php echo $pageIndex;?>()
		{
			var ctr = document.getElementById('editlang<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("select lang");
				ctr.focus();
				return;
			}
			lang_id = ctr.value;
			
			var _url = '<?php echo URL;?>admin/module/lang_module/?ac=lang_line&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id + '&pageIndex=<?php echo $pageIndex;?>&name=<?php echo $name;?>';
			loadPage('pnLang<?php echo $pageIndex;?>', _url, function(status, message)
			{
				if(status== 0)
				{
					
					
				}
				
			}, false);
			
		}
		
		
		function delLangLineModule<?php echo $pageIndex;?>(id)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/module/lang_module/?ac=delLangLine';
			_url = _url + '&id=' + id;
		
	
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function addLangLineModule<?php echo $pageIndex;?>(lang_id)
		{
			
			var ctr = document.getElementById('editname<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("Please input name");
				ctr.focus();
				return;
			}
			var name = ctr.value;
			
			ctr = document.getElementById('editdescription<?php echo $pageIndex;?>');
			
			var description = ctr.value;
			var _url = '<?php echo URL;?>admin/module/lang_module/?ac=addLangLine&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id+ '&name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(description);
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function cloneLine<?php echo $pageIndex;?>()
		{
			if(lang_id == '')
			{
				alert('Please select view before clone');
			}
			var ctr = document.getElementById('editlang<?php echo $pageIndex;?>');
			if(ctr.value == '')
			{
				alert("select lang");
				ctr.focus();
				return;
			}
			var to_lang_id = ctr.value;
			
			var result = confirm("Want to clone?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/module/lang_module/?ac=cloneLine&rel_id=<?php echo $rel_id;?>';
			_url = _url + '&from_lang_id=' + lang_id;
			_url = _url + '&lang_id=' + to_lang_id;
		
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewLang<?php echo $pageIndex;?>();
					}else
					{
						alert(message);
					}
					
				}
				
			}, true);
		}
	</script>
	<?php
}else if($ac == "lang_line")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$pageIndex = "";
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	
	$sql = "SELECT id, name, description FROM res_lang_line WHERE lang_id='".$lang_id."' AND rel_id='".$rel_id."' AND status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$description = "";
	if($numrows>0)
	{
		$id = $result[0][0];
		$description = $result[0][1];
	}
	?>
	<div class="responsive">
		
		<table class="table-all bordered">
			<thead>
				<tr>
					<th width="200">Name</th>
					<th >Description</th>
					<th width="20"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($i=0; $i<$numrows; $i++)
				{
					$row = $result[$i];
					$id = $row[0];
					$name = $row[1];
					$description = $row[2];
					
				?>
				<tr>
				
					<td><input type="text" onblur="saveLine('res_lang_line', 'name', '', '<?php echo $id;?>', this, function(status, message){})" class="input" maxlength="100" value="<?php echo $name;?>"></td>
					<td><input type="text" onblur="saveLine('res_lang_line', 'name', '', '<?php echo $id;?>', this, function(status, message){})" class="input" maxlength="250" value="<?php echo $description;?>"></td>
					<td><a class="button"  href="javascript:delLangLineModule<?php echo $pageIndex;?>('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
				</tr>
				<?php
				}
				?>
				<tr>
				<td><input type="text" id="editname<?php echo $pageIndex;?>" class="input" maxlength="100"></td>
			
				<td><input type="text" id="editdescription<?php echo $pageIndex;?>" class="input" maxlength="250"></td>
				<td><a class="button" href="javascript:addLangLineModule<?php echo $pageIndex;?>('<?php echo $lang_id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<br>
	<?php
}else if($ac == "delLangLine")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE res_lang_line SET write_date=NOW()";
	$sql = $sql.", status =1 WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "addLangLine")
{
	
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$sql = "SELECT id FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$lang_id."' AND name='".$name."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	if($numrows == 0)
	{
		
		$id = $appSession->getTool()->getId();
		$sql = "INSERT INTO res_lang_line(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql.", lang_id";
		$sql = $sql.", name";
		$sql = $sql.", description";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.", '".$lang_id."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".str_replace("'", "''", $description)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}else{
		$row = $result[0];
		$id = $row[0];
		
		$sql = "UPDATE res_lang_line SET status=0, description='".str_replace("'", "''", $description)."' WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	echo "OK";
	
}else if($ac == "cloneLine")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$from_lang_id = "";
	if(isset($_REQUEST['from_lang_id']))
	{
		$from_lang_id = $_REQUEST['from_lang_id'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	
	$sql = "SELECT name, description FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$from_lang_id."'";
	$msg->add("query", $sql);
	$result_line = $appSession->getTier()->getArray($msg);
	$numrows_line = count($result_line);	
	for($i =0; $i<$numrows_line; $i++)
	{
		$row = $result_line[$i];
		$name = $row["name"];
		$description = $row[1];
		$sql = "SELECT id FROM res_lang_line WHERE rel_id='".$rel_id."' AND lang_id='".$lang_id."' AND name='".str_replace("'", "''", $name)."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getArray($msg);
		$numrows = count($result);	
		
		if($numrows == 0)
		{
			$description = "";
			if(isset($_REQUEST['description']))
			{
				$description = $_REQUEST['description'];
			}
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_lang_line(";
			$sql = $sql."id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", rel_id";
			$sql = $sql.", lang_id";
			$sql = $sql.", name";
			$sql = $sql.", description";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", NOW()";
			$sql = $sql.", NOW()";
			$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".$rel_id."'";
			$sql = $sql.", '".$lang_id."'";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", ''";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
		}
	}
	echo "OK";
}
?>