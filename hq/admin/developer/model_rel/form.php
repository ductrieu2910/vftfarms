<?php
require_once('../../config.php' );
require_once('../../tool.php' );
$id = "";
if(isset($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
}
$ac = "new";
if($id != "")
{
	$ac = "edit";
}
$cview = "contentView";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}


$page_id = "model_rel";
$table_name = "ir_model_rel";
$model_id = "";

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


$sql = "SELECT d1.model_id FROM ".$table_name." d1 WHERE d1.id ='".$id."'";
$result = pg_exec($db, $sql);
$numrows = pg_numrows($result);
if($numrows>0)
{
	$row = pg_fetch_array($result, 0);
	$model_id = $row["model_id"];
	
}
$sql = "SELECT d1.id, d1.name FROM ir_model d1 WHERE d1.status =0 ORDER BY d1.name ASC";
$result_model = pg_exec($db, $sql);
$numrows_model = pg_numrows($result_model);
?>
<header class="container" >
	<h3 class="text-teal"><?php echo __('Relation');?></h3>
</header>
<div class="row">
	
	<div class="col m8">
		<a class="button theme" href="javascript:<?php echo $page_id;?>saveLine()"><?php echo __('Save');?></a>
		
	</div>
	<div class="col m4" style="text-align:right">
		
		<?php
		if($ac != 'new')
		{
		?>
		<a class="button" href="javascript:delLine()"><?php echo __('Delete');?></a>
		<?php
		}
		?>
		<a class="button" href="javascript:<?php echo $page_id;?>back()"><?php echo __('Back');?></a>
	</div>
</div>
<br>
<div class="container">
	
	<div class="row">
		<div class="col m2">
			  <label for="editmodel_id" class="label"> Name:</label>
			</div>
			<div class="col m10">
			  <select class="select" id="editmodel_id" name="editmodel_id">
				<option value=""></option>
				<?php
				for($i =0; $i<$numrows_model; $i++)
				{
					$row = pg_fetch_array($result_model, $i);
					$_id = $row["id"];
					$_name = $row["name"];
				?>
				<option value="<?php echo $_id;?>"><?php echo $_name;?></option>
				<?php
				}
				?>
			  </select>
			</div>
	
	</div>
</div>
<br>
<div class="row">
	<div class="col m8">
		<a class="button theme" href="javascript:<?php echo $page_id;?>saveLine()"><?php echo __('Save');?></a>
		
	</div>
	<div class="col m4" style="text-align:right">
		<?php
		if($ac != 'new')
		{
		?>
		<a class="button" href="javascript:delLine()"><?php echo __('Delete');?></a>
		<?php
		}
		?>
		<a class="button" href="javascript:<?php echo $page_id;?>back()"><?php echo __('Back');?></a>
	</div>
</div>
<script>
	function <?php echo $page_id;?>back()
	{
		showpage('<?php echo $cview;?>', '<?php echo URL;?>pages/<?php echo $page_id;?>/index.php');
	}
	function <?php echo $page_id;?>saveLine()
	{
		var ctr = document.getElementById('editmodel_id');
		if(ctr.value == '')
		{
			alert("<?php echo __('Please, enter name');?>");
			ctr.focus();
			return false;
		}
		
		var model_id = ctr.value;
		
		
	
		var _url = '<?php echo URL;?>pages/<?php echo $page_id;?>/index.php?ac=saveLine';
		_url = _url + '&id=<?php echo $id;?>';
		_url = _url + '&re_id=<?php echo $re_id;?>';
		_url = _url + '&name=' + encodeURIComponent(name);
		

		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					<?php echo $page_id;?>back();
				}
				else{
					alert(message);
				}
			}
			
		}, true);
	
	}
</script>

