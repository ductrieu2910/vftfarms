<?php


$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d2.module_id='".$module_id."' AND d2.group_id='".$module_model_id."'";
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());
$msg->add("query", $sql);


$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$values = [];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$values[$i] = $arr;
}



$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename, d4.name FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module d4 ON(d1.module_id = d4.id)WHERE d1.module_id='".$module_id."' AND d1.group_id='".$module_model_id."' AND d1.status =0 ORDER BY d1.create_date ASC";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$module_caption = "Page";
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$arr[3] = $row[3];
	$models[$i] = $arr;
	$module_caption = $row[4];
				
}
$_url = "";
for($i =0; $i<count($models); $i++)
{
	if($models[$i][3] == "url")
	{
		$_url = findValue($values, $models[$i][0]);
		
	}
}

$numrows_grouped = 0;
$sub_count = 0;
$result_grouped =[];
$sql = "SELECT d1.id, d1.name, d1.rel_id, d1.icon, d2.icon, d1.type, d2.type, lg.description  FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND d1.publish=1 AND d1.grouped = 1 ";
if($root_module_id != "")
{
	$sql = $sql." AND (d1.parent_id='".$module_id."' OR d1.parent_id='".$root_module_id."')";
}else{
	$sql = $sql." AND d1.parent_id='".$module_id."'";
}
$sql = $sql." ORDER BY d1.sequence ASC";

$msg->add("query", $sql);
$result_grouped = $appSession->getTier()->getArray($msg);
$numrows_grouped = count($result_grouped);

if($_url != "")
{
	$_url = str_replace("{addons}", $appSession->getConfig()->getProperty("addons"), $_url);
?>		
<?php
	$_url = str_replace("{addons}", "admin", $_url);
	include( ABSPATH .$_url.'/index.php' );
?>

<?php
}
?>
