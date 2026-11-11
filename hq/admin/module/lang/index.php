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
	$caption = "";
	if(isset($_REQUEST['caption']))
	{
		$caption = $_REQUEST['caption'];
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
		<div class="col m10">
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
		<div class="col-2">
		<a class="button btn-default" href="javascript:viewLang<?php echo $pageIndex;?>()">View</a>
		
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
			
			var _url = '<?php echo URL;?>admin/module/lang/?ac=lang_line&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id + '&pageIndex=<?php echo $pageIndex;?>&name=<?php echo $name;?>&caption=<?php echo $caption;?>';
			loadPage('pnLang<?php echo $pageIndex;?>', _url, function(status, message)
			{
				if(status== 0)
				{
					
					
				}
				
			}, false);
			
		}
		
		
		function delLangLine<?php echo $pageIndex;?>(id)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>addons/module/lang/?ac=delLangLine';
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
		function addLangLine<?php echo $pageIndex;?>()
		{
			
			var name = '<?php echo $name;?>';
			
			var ctr = document.getElementById('editdescription<?php echo $pageIndex;?>');
			
			var description = ctr.value;
			var _url = '<?php echo URL;?>admin/module/lang/?ac=addLangLine&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id+ '&name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(description);
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						alert("Language is saved");
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
			var _url = '<?php echo URL;?>admin/module/lang/?ac=cloneLine&rel_id=<?php echo $rel_id;?>';
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
	$caption = "";
	if(isset($_REQUEST['caption']))
	{
		$caption = $_REQUEST['caption'];
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
	
	$sql = "SELECT id, description FROM res_lang_line WHERE lang_id='".$lang_id."' AND rel_id='".$rel_id."' AND status =0 AND name='".$name."'";
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
	<div class="row">
		<div class="col m12">
			<?php echo $caption;?>:
		</div>
	</div>
	<div class="row">
		
		<div class="col m12">
		<textarea class="textarea" rows="4" id="editdescription<?php echo $pageIndex;?>"><?php echo $description;?></textarea>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col m12"><a href="javascript:addLangLine<?php echo $pageIndex;?>()" class="btn btn-primary">Save</a></div>
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
	$result = $appSession->getTier()->exec($sql);
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
		
		$sql = "UPDATE res_lang_line SET status=0, description='".str_replace("'", "''", $description)."', write_date=NOW() WHERE id='".$id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	echo "OK";
	
}
?>