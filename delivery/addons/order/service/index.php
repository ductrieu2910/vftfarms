<?php
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
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
?>
	<style>
		.button {
		  border: none;
		  color: white;
		  padding: 16px 32px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  font-size: 16px;
		  margin: 4px 2px;
		  width:140px;
		  transition-duration: 0.4s;
		  cursor: pointer;
		}

		.button1 {
		  background-color: white; 
		  color: black; 
		  border: 2px solid #4CAF50;
		}

		.button1:hover {
		  background-color: #4CAF50;
		  color: white;
		}

		.button2 {
		  background-color: white; 
		  color: black; 
		  border: 2px solid #008CBA;
		}

		.button2:hover {
		 
		 
		}
	</style>
	<?php

	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}

	$category_id = '';
	if(isset($_REQUEST['category_id']))
	{
		$category_id = $_REQUEST['category_id'];
	}

	
	$sql = "SELECT d1.id, d1.name, d1.percent, d1.value FROM account_service d1 WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='ROOT') AND d1.category_id='".$category_id."' ORDER BY d1.sequence ASC, d1.name ASC";
	
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	
	$sql = "SELECT d1.id, d1.service_id, d1.percent FROM account_service_line_local d1 WHERE d1.status =0 AND d1.rel_id='".$rel_id."'";
	$msg->add("query", $sql);
	$dt_rel = $appSession->getTier()->getTable($msg);
	
	
	?>
	
	
	<div class="row">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 p-0">
		<?php 
		for($j=0; $j<$dt->getRowCount(); $j++)
		{
			
				$id = $dt->getString($j, "id");
				$name = $dt->getString($j, "name");
				
				$line_id = "";
				for($i =0; $i<$dt_rel->getRowCount(); $i++)
				{
					if($dt_rel->getString($i, "service_id") == $id || $dt_rel->getString($i, "percent") == $dt->getString($j, "percent"))
					{
						$line_id = $dt_rel->getString($i, "id");
						break;
					}
					
				}
				
			
				
		?>
		<button class="button <?php if($line_id == ""){ echo " button1"; }else{ echo " button2"; } ?>" <?php if($line_id == ""){ ?> onclick="addService('<?php echo $id;?>')" <?php }else{ ?> onclick="removeService('<?php echo $line_id;?>')" <?php } ?>><?php echo $name;?></button>
		<?php
			
		}
		?>
		</div>
	</div>
	
	
	<script>
	

	function addService(service_id)
	{
		var _url = '<?php echo URL;?>api/sale_action/?ac=addService&rel_id=<?php echo $rel_id;?>&service_id=' + service_id;
		
		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message.indexOf("OK") != -1)
				{
					
					var ctr = document.getElementById('frmdialogClose');
					if(ctr != null)
					{
						ctr.click();
					}
					if('<?php echo $category_id;?>' == "PAYMENT")
					{
						loadPayment();
					}else{
						loadSale('<?php echo $rel_id; ?>', false);
					}
					
					
				}else{
					alert(message);
				}
			}
			
		}, true);
	}
	function removeService(id)
	{
		var _url = '<?php echo URL;?>api/sale_action/?ac=removeService&id=' + id;
		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message.indexOf("OK") != -1)
				{
					var _url = '<?php echo URL;?>addons/order/service/?ac=view&rel_id=<?php echo $rel_id;?>&category_id=<?php echo $category_id;?>';
					loadPage('popupContent', _url, function(status, message)
					{
						if(status== 0)
						{
							
						}
						
					}, false);
					
					if('<?php echo $category_id;?>' == "PAYMENT")
					{
						loadPayment();
					}else{
						loadSale('<?php echo $rel_id; ?>', false);
					}
					
				}else{
					alert(message);
				}
			}
			
		}, true);
	}
	</script>
	
<?php
}
?>

		