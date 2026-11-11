<?php


$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$pageIndex = '';
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
	
	$sql = "SELECT receipt_no FROM voucher WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$code = $appSession->getTier()->getValue($msg);

?>
<div class="row">
	<div class="col m12">
		<?php echo $appSession->getLang()->find("Number of code");?>:<?php echo $code;?>-xxxxxx
		<input type="text" class="input" id="editnumber">
		
	<div>
	
	
</div>
<div class="row">
	<div class="col m12">
		<?php echo $appSession->getLang()->find("Code lenght");?>:
		<input type="text" class="input" id="editlenght">
	<div>
</div>
<div class="row">
	<div class="col m12">
		<br>
		<input type="button" class="button btn-primary" onclick="createCodes()" value="<?php echo $appSession->getLang()->find("Create");?>">
	<div>
</div>
<script>
	function createCodes(){
		var ctr = document.getElementById('editnumber');
		if(ctr.value == ""){
			alert("Enter number code");
			ctr.focus();
			return;
		}
		if(numeric(ctr.value) == false){
			alert("Please enter number");
			ctr.focus();
			return;
		}
		var num = ctr.value;
		ctr = document.getElementById('editlenght');
		if(ctr.value == ""){
			alert("Enter lenght code");
			ctr.focus();
			return;
		}
		if(numeric(ctr.value) == false){
			alert("Please enter number");
			ctr.focus();
			return;
		}
		var len = ctr.value;
		var _url = '<?php echo URL.$appSession->getConfig()->getProperty("addons")."/voucher_generated_code/?ac=create&rel_id=".$rel_id."&code=".$code;?>&num='+ num + '&len=' + len;
		loadPage('gotoTop', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message.indexOf("OK") !=-1)
				{
					closePopup();
					relLoadTable<?php echo $pageIndex;?>();
				}
				else{
					alert(message);
				}
			}
			
		}, true);
	}
</script>
<?php
}else if($ac == "create")
{
	function getRandomString($length = 8) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$string = '';

		for ($i = 0; $i < $length; $i++) {
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}
		return $string;
	}
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$code = '';
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$num = 0;
	if(isset($_REQUEST['num']))
	{
		$num = $appSession->getTool()->toInt($_REQUEST['num']);
	}
	$len = '';
	if(isset($_REQUEST['len']))
	{
		$len = $appSession->getTool()->toInt($_REQUEST['len']);
	}
	if($len<strlen($_REQUEST['num'])){
		$len = strlen($_REQUEST['num']);
	}
	$nums = '';
	for($i =0; $i<$num; $i++)
	{
		$c = "";
		while(true)
		{
			$c = getRandomString($len);
			if($appSession->getTool()->indexOf($nums, $c.";") == -1)
			{
				break;
			}
		}
		$nums = $nums.$c.";";
		
		$builder = $appSession->getTier()->createBuilder("voucher_line");
		$id = $appSession->getTool()->getId();
		$builder->add("id", $id);
		
		$builder->add("create_uid", $appSession->getUserInfo()->getId());
		$builder->add("write_uid", $appSession->getUserInfo()->getId());
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$builder->add("status", 0);
		$builder->add("voucher_id", $rel_id);
		$builder->add("code", $code."-".$c);
		
		$sql = $appSession->getTier()->getInsert($builder);
		$msg->add("query", $sql);
		echo $sql;
		$appSession->getTier()->exec($msg);
	}
	
	echo "OK";
}
?>

	