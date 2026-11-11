<?php
validUser($appSession);
$appSession->getLang()->load($appSession->getTier(), "admin", $appSession->getConfig()->getProperty("lang_id"));
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$msg = $appSession->getTier()->createMessage();
if($ac == "view")
{
	$company_ids = "rel_id='".$appSession->getUserInfo()->getCompanyId()."'";
$pid = $appSession->getUserInfo()->getCompanyId();




while(true)
{
	$sql = "SELECT parent_id FROM res_company WHERE status =0 AND id='".$pid."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	if($numrows>0)
	{
		$pid = $result[0][0];
		if($pid == "")
		{
			break;
		}
		$company_ids = $company_ids." OR rel_id='".$pid."'";
	}else
	{
		break;
	}
}
$sql = "SELECT module_id FROM ir_module_rel WHERE status =0 AND (".$company_ids.")";



$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$rels =[];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$module_id = $row[0];
	$rels[count($rels)] = $module_id;
	
}
	
	
$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.icon, d2.icon AS icon_rel, d1.rel_id, lg.description FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND (d1.grouped=0 OR d1.grouped IS NULL) AND d1.type='' ORDER BY d1.sequence ASC";

$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$menus = [];
for($j =0; $j<$numrows; $j++)
{
	
	$row = $result[$j];
	
	if(developer != "1")
	{
		if($row[1] == "")
		{
			$hasItem = false;
			for($n =0; $n<count($rels); $n++)
			{
				if($rels[$n] == $row[0])
				{
					$hasItem = true;
					break;
				}
			}
			if($hasItem == true)
			{
				$menus[count($menus)] =  [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
			}
		}else{
			$menus[count($menus)] =  [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
		}
	}else{
		$menus[count($menus)] = [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
	}
	
}


$sql = "SELECT module_name, ac_name, ac_value FROM res_user_group_per WHERE status =0 AND group_id='".$appSession->getUserInfo()->getGroupId()."'";



$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);
$perList = array();
for($i = 0; $i < $numrows; $i++) 
{
	$row = $result[$i];
	$arr = array();
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$perList[$i] = $arr;
}
	
function per($appSession, $perList, $module_name, $ac_name)
{
	if($appSession->getUserInfo()->getGroupId() == "ADMIN")
	{
		return true;
	}
	
	for($i=0; $i<count($perList); $i++)
	{
		
		if($perList[$i][0] == $module_name && $perList[$i][1]== $ac_name)
		{
			if( $perList[$i][2] == "1"){
				return true;
			}
			return false;
		}
	}
	return true;
}		
function buildNav($appSession, $arr, $parent_id, $perList, $space)
{
	
	$s = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($arr[$i][1] == $parent_id)
		{
			
			if(per($appSession, $perList, $arr[$i][0], "view") == true)
			{
				$hasSub = false;
				if($arr[$i][0] != 'report')
				{
					for($j =0; $j<count($arr); $j++)
					{
						if($arr[$j][1] == $arr[$i][0])
						{
							$hasSub = true;
							break;
						}
					}
					
				}
				$s = $s."<li>";
				$name = $arr[$i][6];
				if($name == "")
				{
					$name = $arr[$i][2];
				}
				$icon = $arr[$i][3];
				if($icon == "")
				{
					$icon = $arr[$i][4];
				}
				if($icon != "")
				{
					$icon = '<i class="'.$icon.'"></i>  ';
				}
				if($hasSub == true)
				{
					$s = $s.'<span class="caret caret1" ><a href="javascript:langModule(\''.$arr[$i][0].'\');" style="padding: 0 '.$space.'px;">'.$icon.$name.'</a></span>';
					$s = $s.'<ul class="nested" style="margin:0; padding:0">';
					$s = $s.buildNav($appSession, $arr, $arr[$i][0], $perList, $space + 12);
					$s = $s.'</ul>';
				}else
				{
					$s = $s.'<a style="padding: 0 '.$space.'px;" href="javascript:langModule(\''.$arr[$i][0].'\');">'.$icon.$name.'</a>';
				}
				$s = $s."</li>";
				if($hasSub == false)	
				{
					$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.icon, d2.icon AS icon_rel, d1.rel_id, lg.description FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND d1.publish=1 AND d1.parent_id='".$arr[$i][0]."' ORDER BY d1.sequence ASC";
					$msg = $appSession->getTier()->createMessage();
					$msg->add("query", $sql);

					$childs = $appSession->getTier()->getArray($msg);
					for($c = 0; $c<count($childs); $c++)
					{
						$name = $childs[$c][6];
						if($name == "")
						{
							$name = $childs[$c][2];
						}
						$s = $s."<li>";
						$s = $s.'<a style="padding: 0 '.($space + 10).'px;" href="javascript:langModule(\''.$childs[$c][0].'\');">&nbsp;&nbsp;&nbsp;&nbsp;'.$name.'</a>';
						
						$s = $s."</li>";
					}
				}
			}
			
		}
	}
	
	return $s;
}
	
	
?>
<div class="container">
   <ul class="nav">
	<?php
	echo buildNav($appSession, $menus, "", $perList, 12);
	?>
	</ul>
</div>
<script>
	function langModule(id)
	{
		var _url = '<?php echo URL;?>admin/module_lang/?ac=langs&rel_id=' + id;
		openPopup(_url);
	}
	var toggler = document.getElementsByClassName("caret1");
	var i;

	for (i = 0; i < toggler.length; i++) {
	  toggler[i].addEventListener("click", function() {

		this.parentElement.querySelector(".nested").classList.toggle("nav-active");
		this.classList.toggle("caret-down");
	  });
	}
</script>
<?php
}else if($ac == "langs")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$pageIndex = 0;
	$sql = "SELECT id, name FROM res_lang WHERE status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	?>
	<div class="row">
		<div class="col m2">
			  <label for="edittype" class="label">Language:</label>
			</div>
		<div class="col m6">
			<select class="select" id="editlang<?php echo $pageIndex;?>">
				<?php
				for($i =0; $i<$dt->getRowCount(); $i++)
				{
					
				?>
				<option value="<?php echo $dt->getString($i, "id");?>"><?php echo $dt->getString($i, "name");?></option>
				<?php
				}
				?>
			  </select>
		</div>
		<div class="col m4">
		<a class="button" href="javascript:viewLang<?php echo $pageIndex;?>()">View</a>
	
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
			
			var _url = '<?php echo URL;?>admin/module_lang/?ac=lang_line&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id + '&pageIndex=<?php echo $pageIndex;?>';
			showpage('pnLang<?php echo $pageIndex;?>', _url);
		}
		
		
		
		function delLangLine<?php echo $pageIndex;?>(id)
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/module_lang/?ac=delLangLine';
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
		function addLangLine<?php echo $pageIndex;?>(lang_id)
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
			var _url = '<?php echo URL;?>admin/module_lang/?ac=addLangLine&rel_id=<?php echo $rel_id;?>&lang_id=' + lang_id+ '&name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(description);
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message.indexOf("OK") != -1)
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
	
	$sql = "SELECT id, name, description FROM res_lang_line WHERE lang_id='".$lang_id."' AND rel_id='".$rel_id."' AND status =0 ORDER BY create_date ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	
?>
<div class="responsive">
		
		<table class="table">
			<thead>
				<tr>
					<th width="200">Name</th>
					<th >Description</th>
					<th width="20">#</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($i=0; $i<$dt->getRowCount(); $i++)
				{
					
					$id = $dt->getString($i, "id");
					$name = $dt->getString($i, "name");
					$description = $dt->getString($i, "description");
					
				?>
				<tr>
				
					<td><input type="text" onblur="saveLine('res_lang_line', 'name', '', '<?php echo $id;?>',this , function(status, message){})" class="input" maxlength="100" value="<?php echo $name;?>"></td>
					<td><input type="text" onblur="saveLine('res_lang_line', 'description', '', '<?php echo $id;?>', this, function(status, message){})" class="input" maxlength="250" value="<?php echo $description;?>"></td>
					<td><a class="button"  href="javascript:delLangLine<?php echo $pageIndex;?>('<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
				</tr>
				<?php
				}
				?>
				<tr>
				<td><input type="text" id="editname<?php echo $pageIndex;?>" class="input" maxlength="100"></td>
			
				<td><input type="text" id="editdescription<?php echo $pageIndex;?>" class="input" maxlength="250"></td>
				<td><a class="button" href="javascript:addLangLine<?php echo $pageIndex;?>('<?php echo $lang_id;?>')"><img src="<?php echo URL;?>assets/img/add.png"></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<br>
<?php
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
	$check_id = $appSession->getTier()->getValue($msg);

	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	if($check_id == "")
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
		$sql = $sql.", '".$appSession->getConfig()->getProperty("company_id")."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$rel_id."'";
		$sql = $sql.", '".$lang_id."'";
		$sql = $sql.", '".str_replace("'", "''", $name)."'";
		$sql = $sql.", '".str_replace("'", "''", $description)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}else{
		
		
		$sql = "UPDATE res_lang_line SET status=0, description='".str_replace("'", "''", $description)."' WHERE id='".$check_id."'";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
	echo "OK";
}
?>

	