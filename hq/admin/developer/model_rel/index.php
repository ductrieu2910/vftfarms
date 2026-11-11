<?php
	session_start();
	set_time_limit(600);
	$page_id = "model_rel";
	$table_name = "ir_model_rel";
	$columns = ["model_id", "rel_id"];
	$searchs = ["d2.name"];
	
	require_once('../../config.php' );
	require_once('../../tool.php' );
	
	$LOGIN_USER_ID = "";
	if(isset($_SESSION["user_id"]))
	{
		$LOGIN_USER_ID = $_SESSION["user_id"];
	}
	$LOGIN_COMPANY_ID = "";
	if(isset($_SESSION["company_id"]))
	{
		$LOGIN_COMPANY_ID = $_SESSION["company_id"];
	}
	if($LOGIN_COMPANY_ID == "")
	{
		$LOGIN_COMPANY_ID = COMPANY_ID;
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
	
	

?>
<?php 
if($ac == "view")
{
	
	
	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}else{
		if(isset($_SESSION[$page_id."_search"]))
		{
			$search = $_SESSION[$page_id."_search"];
		}
	}
	$_SESSION[$page_id."_search"] = $search;
	
	
	$p = 0;
	if(isset($_REQUEST['p']))
	{
		$p = $_REQUEST['p'];
	}else{
		if(isset($_SESSION[$page_id."_page"]))
		{
			$p = $_SESSION[$page_id."_page"];
		}
	}
	$_SESSION[$page_id."_page"] = $p;

	$ps = 30;
	if(isset($_REQUEST['ps']))
	{
		$ps = $_REQUEST['ps'];
	}else
	{
		if(isset($_SESSION[$page_id."_pages"]))
		{
			$ps = $_SESSION[$page_id."_pages"];
		}
	}
	$_SESSION[$page_id."_pages"] = $ps;	
	
	
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}else{
		if(isset($_SESSION[$page_id."_rel_id"]))
		{
			$rel_id = $_SESSION[$page_id."_rel_id"];
		}
	}
	$_SESSION[$page_id."_rel_id"] = $rel_id;
	
	$cview = "contentView";
	if(isset($_REQUEST['cview']))
	{
		$cview = $_REQUEST['cview'];
	}
	
?>
<div class="row">
	<div class="col m8">
		<a class="button theme" href="javascript:line('')"><?php echo __('New');?></a> <?php echo __('Relation');?>
	</div>
	<div class="col m4 right" style="text-align:right">
		<input type="text" id="editsearch" class="input" style="width:80%" value="<?php echo $search; ?>" placeholder="<?php echo __('Search');?>" onKeyDown="if(event.keyCode == 13){doSearch(0);}"> 
		<a class="button" href="javascript:doSearch(0)"><img src="<?php echo URL;?>assets/img/search.png"></a>
		
	</div>
	
</div>
<?php
	$sql = "SELECT d1.id, d2.name FROM ".$table_name." d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) WHERE d1.status =0 AND d1.rel_id='".$rel_id."'";
	if($search != "")
	{
		$sql = $sql." AND (".buildSearch($searchs, $search).")";
	}

	$arr = paging($sql, $p, $ps, "d2.name ASC");
	
	$item_count = 0;
	$sql = $arr[1];
	$result = pg_exec($db, $sql);
	$numrows = pg_numrows($result);	
	if($numrows>0)
	{
		$row = pg_fetch_array($result, 0);
		$item_count = $row[0];
	}
	
	$page_count = (int)($item_count / $ps);
	if ($item_count - ($page_count * $ps) > 0)
	{
		$page_count = $page_count + 1;
	}
	
	$start = 0;
	if($item_count>0)
	{
		$start = ($p * $ps) + 1;
	}
	$end = $p + 1;
	if((($p + 1) * $ps)<$item_count)
	{
		$end = ($p + 1) * $ps;
	}else
	{
		$end = $item_count;
	}

	$sql = $arr[0];
	
	$result = pg_exec($db, $sql);
	$numrows = pg_numrows($result);	
	
	?>

	<div class="row" style="padding-top:4px">
			<div class="col m6  pagination">
				
				<?php 
			
				if($page_count == 0)
				{
					$page_count = 1;
				}
				for($i =0; $i<$page_count; $i++)
				{
				?>
				<a class="<?php if($i == $p){ ?> active <?php } ?>" href="javascript:doSearch('<?php echo $i;?>')">&nbsp;<?php echo ($i + 1);?>&nbsp;</a>
			
				<?php 
				} 
				?>
				
				
			</div>
			<div class="col m6 right">
				<?php echo __('With checked');?>:&nbsp;&nbsp;&nbsp;<a  href="javascript:delRows()"><?php echo __('Delete');?></a>
			</div>
			
	</div>
	<div class="row responsive" style="padding-top:4px">
	<table class="table-all bordered">
		<thead>
		  <tr>
			<th style="width:30px">#</th>
			
			<th style="width:40px" style="text-align:center; vertical-align:middle"><input value = "" type="checkbox" class="check" name="[]" onclick="doHandleCheckAll(this)" /></th>
			<th style="width:30px" nowrap="nowrap"></th>
			<th nowrap="nowrap"><?php echo __('Name');?></th>
	
			<th style="width:30px" nowrap="nowrap"></th>
			
		  </tr>
		</thead>
		
		<tbody>
		  
			<?php
			for($j =0; $j<$numrows; $j++)
			{
				$row = pg_fetch_array($result, $j);
				$color = EVEN_COLOR;
				if($j %2 == 0)
				{
					$color = ODD_COLOR;
				}
				$id = $row["id"];
				$name = $row["name"];
				
				
		
			?>
			<tr>
			<td ><?php echo $j + 1; ?></td>
			
			<td style="width:40px" style="text-align:center; vertical-align:middle"><input type="checkbox" class="check" name="[]" id="<?php echo $id;?>" /></td>
			<td><a href="javascript:loadView('<?php echo $page_id;?>/form.php?id=<?php echo $id;?>')"><img src="<?php echo URL;?>assets/img/edit.png"></a></td>
			<td><?php echo $name; ?></td>

			<td style="width:40px" nowrap="nowrap"> <a href="javascript:delLine('<?php echo $id; ?>')"><img src="<?php echo URL;?>assets/img/remove.png"></a></td>
			
		  </tr>
		 <?php
			}
			?>
		</tbody>
	  </table>
	</div>
	<div class="row" style="padding-top:4px">
			<div class="col m6  pagination">
				
				<?php 
			
				
				for($i =0; $i<$page_count; $i++)
				{
				?>
				<a class="<?php if($i == $p){ ?> active <?php } ?>" href="javascript:doSearch('<?php echo $i;?>')">&nbsp;<?php echo ($i + 1);?>&nbsp;</a>
			
				<?php 
				} 
				?>
				
				
			</div>
			<div class="col m6 right">
				<?php echo __('With checked');?>:&nbsp;&nbsp;&nbsp;<a  href="javascript:delRows()"><?php echo __('Delete');?></a>
			</div>
			
	</div>
</div>
	

	<script>
	
		function line(id)
		{
			showpage('<?php echo $cview;?>', '<?php echo URL;?>pages/<?php echo $page_id;?>/form.php?id='+ id);
		}
		function delLine(id)
		{
			var result = confirm("<?php echo __('Want to delete?');?>");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>pages/<?php echo $page_id;?>/index.php?ac=delLine';
			_url = _url + '&id=' + encodeURIComponent(id);
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						
						doSearch(<?php echo $p;?>);
					}
					else{
						alert(message);
					}
				}
				
			}, true);
		}
		function doSearch(p)
		{
			var search = document.getElementById('editsearch').value;
			loadView('database/index.php?search=' + encodeURIComponent(search) + "&p=" + p);
		}
		function delRows()
		{
			var count = 0;
			var checkboxes = document.getElementsByName("[]");
			var ids = "";
			for (var i= 0; i<checkboxes.length; i++) {
				if(checkboxes[i].checked && checkboxes[i].value != "")
				{
					if(ids != "")
					{
						ids += ",";
					}
					ids += checkboxes[i].id;
					count += 1;
				}
			}
			if(ids == "")
			{
				alert("<?php echo __('Please check to delete');?>");
				return;
			}
			var result = confirm(count + ". <?php echo __('Want to delete?');?>");
			if (!result) {
				return;
			}
			delLine(ids);
		}
		
	</script>
	</div>
</div>
<?php
}else if($ac == "saveLine")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	if($id == "")
	{
		$id = gen_uuid();
	}
	$sql = "SELECT id FROM ".$table_name." WHERE id='".$id."'";
	$result = pg_exec($db, $sql);
	$numrows = pg_numrows($result);
	if($numrows == 0)
	{
		$sql = "INSERT INTO ".$table_name."(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$id."'";
		$sql = $sql.", NOW()";
		$sql = $sql.", NOW()";
		$sql = $sql.", '".$LOGIN_COMPANY_ID."'";
		$sql = $sql.", 0";
		$sql = $sql.")";
		$r = pg_exec($db, $sql);
	}
	$sql = "UPDATE ".$table_name." SET write_date=NOW()";
	for($i = 0; $i<count($columns); $i++)
	{
		$name = $columns[$i];
		$value = "";
		if(isset($_REQUEST[$name]))
		{
			$value = $_REQUEST[$name];
		}
		$sql = $sql.", ".$name."='".str_replace("'", "''", $value)."'";
	}
	$sql = $sql." WHERE id ='".$id."'";
	$r = pg_exec($db, $sql);
	echo "OK";
}else if($ac == "delLine")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$items = explode(",", $id);
	for($i =0; $i<count($items); $i++)
	{
		$id = $items[$i];
		$sql = "UPDATE ".$table_name." SET status =1";
		$sql = $sql.", write_date=NOW()";
		$sql = $sql." WHERE id='".$id."'";
		$result = pg_exec($db, $sql);
	}
	echo "OK";
}else if($ac == "apply")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$url = URL_SERVER."service/database/update?id=".$id;

	echo getPage($url);
}
?>
