<?php

validUser($appSession);
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
	$root_company_id = $appSession->getConfig()->getProperty("root_company_id");
	
	
?>
<div id="pnInstall"></div>
<script>
function loadDatabase()
{
	var _url = '<?php echo URL;?>admin/install/?ac=database&company_id=<?php echo $root_company_id;?>';
	loadPage('pnInstall', _url, function(status, message)
	{
		if(status == 0)
		{
		}
		
	}, false);
}
loadDatabase();
function createDB()
{
	var ctr = document.getElementById('editresource_id');
	var resource_id = ctr.value;
	var result = confirm("Want to create DB?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/install/?ac=createDB&company_id=<?php echo $root_company_id;?>&resource_id='+resource_id;
	
	loadPage('pnInstall', _url, function(status, message)
	{
		if(status == 0)
		{
			if(message.indexOf("OK") != -1)
			{
				loadDatabase();
			}else{
				alert(message);
			}
		}
		
	}, true);
	
}

function updateDB()
{

	var result = confirm("Want to update DB?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/install/?ac=updateDB&company_id=<?php echo $root_company_id;?>';
	
	loadPage('pnInstall', _url, function(status, message)
	{
		if(status == 0)
		{
			if(message.indexOf("OK") != -1)
			{
				alert("Data is update");
			}else{
				alert(message);
			}
		}
		
	}, true);
	
}
function publishDB(theInput)
{
	var _url = '<?php echo URL;?>admin/install/?ac=createDB&company_id=<?php echo $root_company_id;?>';
	loadPage('pnInstall', _url, function(status, message)
	{
		if(status == 0)
		{
			if(message.indexOf("OK") != -1)
			{
				loadDatabase();
			}else{
				alert(message);
			}
		}
		
	}, true);
}

function transferTable(name)
{
	var result = confirm("Want to transfer?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/install/?ac=transferTable&company_id=&company_id=<?php echo $root_company_id;?>&name=' + encodeURIComponent(name);
	loadPage('pnInstall', _url, function(status, message)
	{
		if(status == 0)
		{
			if(message.indexOf("OK") != -1)
			{
				alert("Data is transfered");
			}else{
				alert(message);
			}
		}
		
	}, true);
}
</script>
<?php
}else if($ac == "database")
{
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	
	$sql = "SELECT id, name, publish FROM ir_database WHERE company_id='".$company_id."' AND status =0 ORDER BY create_date ASC";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("action", "d35342bc-4c59-4166-f5cd-979b63da68b7");
	$msg->add("query", $sql);
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	$arr = $appSession->getTier()->getArray($msg);
	$hasDatabase = false;
	for($i=0; $i<count($arr); $i++)
	{
		$hasDatabase = true;
		$database_id = $arr[$i][0];
		$database_publish = $arr[$i][2];
		if($i>0)
		{
			echo " <br>";
		}
	?>
	<?php echo $arr[$i][1];?> <input type="radio" name="db" <?php if($database_publish == "1"){ echo " checked" ; }?> onchange="publishDB(this)" id="<?php echo $database_id;?>"> 
	<?php
	}
	$sql = "SELECT id, name FROM ir_data_resource WHERE status =0 ORDER BY name ASC";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("query", $sql);
	$msg->add("company_id", COMPANY_ID);
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	$arr = $appSession->getTier()->getArray($msg);
	?>
	<div class="row">
		<div class="col m11">
			<select id="editresource_id" class="select">
			<?php
			for($i =0; $i<count($arr); $i++)
			{
			?>
			<option value="<?php echo $arr[$i][0];?>"><?php echo $arr[$i][1];?></option>
			<?php
			}
			?>
			</select>
		</div>
		<div class="col m1">
			<a href="javascript:createDB()"><img  src="<?php echo URL;?>assets/img/add.png"></a>
		</div>
	</div>
	
	
	
	<?php
}else if($ac == "createDB")
{
	$resource_id = "";
	if(isset($_REQUEST['resource_id']))
	{
		$resource_id = $_REQUEST['resource_id'];
	}
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$msg->add("action", "8ad3fe9c-e612-4640-d340-69111bfa0ae8");
	$msg->add("resource_id", $resource_id);
	$msg->add("company_id", $company_id);
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	$code = $appSession->getTier()->getData($msg);
	
	echo "OK";
	
}else if($ac == "updateDB")
{
	
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$msg->add("action", "629a0341-681b-4db2-ba6d-ff31278bb2df");
	$msg->add("root_company_id", $company_id);
	$code = $appSession->getTier()->getData($msg);
	
	echo "OK";
	
}
else if($ac == "transferTable")
{
	
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$msg = $appSession->getTier()->createMessage();
	$msg->add("action", "629a0341-681b-4db2-ba6d-ff31278bb2df");
	$msg->add("company_id", $company_id);
	$code = $appSession->getTier()->getData($msg);
	
	echo "OK";
}
?>

	