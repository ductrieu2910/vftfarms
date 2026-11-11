<?php
/*
require_once(ABSPATH.'api/WebService.php' );
$device_id= "23035bb2-e356-435f-ab62-6d13ed2da8d6";
$id = "12333";
$data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=".$device_id."&message=".$appSession->getTool()->urlEncode("type=timesheet_resource_add&resource_id=".$id);
$ws =new WebService($appSession);
$ws->sendMessage($data);
*/
$id = "ada30d88-77f9-401e-c652-f0554deac1ab";
$data = "from=".$appSession->getConfig()->getProperty("company_id")."&to=".$appSession->getTool()->urlEncode("[{\"id\": \"23035bb2-e356-435f-ab62-6d13ed2da8d6\", \"typ\": \"\"}]")."&body=".$appSession->getTool()->urlEncode("type=timesheet_resource_add&resource_id=".$id);

echo $appSession->getTool()->httpPost(SERVICE_URL."message_index", $data);
?>