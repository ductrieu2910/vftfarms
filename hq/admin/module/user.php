<?php
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$sql = "SELECT d2.name, d3.name FROM res_assign d1 LEFT OUTER JOIN res_user d2 ON(d1.resource_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d1.resource_id = d3.id) WHERE d1.id='".$id."'";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$sName = "";
	if(count($values)>0)
	{
		$sName = $values[0][0];
		if($sName == "")
		{
			$sName = $values[0][1];
		}
	}
	

	


?>
<input type="hidden" id="<?php echo $name;?>" value="<?php echo $value;?>">
<div class="input-group">
	<input class="input" id="<?php echo $name;?>_value" readonly value="<?php echo $sName;?>">
	<div class="input-group-append">
		<a class="btn btn-light" href="javascript:openPopup('<?php echo URL;?>admin/module/user/?ac=view&employee_type=<?php echo $employee_type;?>')">...</a>
	</div>
</div>
<script>
function selUser(id, name)
{
	document.getElementById('<?php echo $name;?>').value = id;
	document.getElementById('<?php echo $name;?>_value').value = name;
	closePopup();
}
</script>