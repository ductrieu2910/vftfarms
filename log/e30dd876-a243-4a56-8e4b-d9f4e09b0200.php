<?php
$msg = $appSession->getTier()->createMessage();
$sql = "SELECT id, name FROM survey WHERE status=0 ORDER BY name ASC";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);
echo $appSession->getTool()->respTable($dt);
?>