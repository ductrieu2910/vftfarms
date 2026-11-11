<?php
$msg = $appSession->getTier()->createMessage();
$arr_params = explode('&', $params);
$rel_id = "";
$type = "";
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$rel_id = $arrItem[1];
		}
		if($arrItem[0] == "type")
		{
			$type = $arrItem[1];
		}
	}
}
$appSession->getLang()->load($appSession->getTier(), "TASK", $appSession->getConfig()->getProperty("lang_id"));

?>
<a href="javascript:addTask();"><i class="icon-plus"> <?php echo $appSession->getLang()->find("New");?></i></a>
<br>
<div id="pnTask" style="padding-top:5px"></div>
<script>

function loadTask()
{
	var _url = "<?php echo URL;?>admin/task/action/?ac=line&rel_id=<?php echo $rel_id;?>";
	loadPage("pnTask", _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
loadTask();

function addTask()
{
	var _url = '<?php echo URL;?>admin/task/action/?ac=form&rel_id=<?php echo $rel_id;?>&table_name=<?php echo $type;?>';

	openPopup(_url);
}
function editTask(id)
{
	var _url = '<?php echo URL;?>admin/task/action/?ac=form&rel_id=<?php echo $rel_id;?>&table_name=<?php echo $type;?>&id=' + id;

	openPopup(_url);
}

function doAddTask(id, assignedList)
{
	var resource = "";
	for(var i =0; i<assignedList.length; i++)
	{
		if(resource != "")
		{
			resource = resource + ";";
		}
		resource = resource + assignedList[i][0] + "," + assignedList[i][1] + "," + assignedList[i][2] + "," + assignedList[i][3] + "," + assignedList[i][4] + "," + assignedList[i][5]+ "," + assignedList[i][6];
	}
	
	var ctr = document.getElementById('edittask_start_date');
	if(ctr.value == "")
	{
		alert("Please enter start date");
		ctr.focus();
		return;
	}
	var start_date = ctr.value;
	ctr = document.getElementById('edittask_end_date');
	if(ctr.value == "")
	{
		alert("Please enter end date");
		ctr.focus();
		return;
	}
	var end_date = ctr.value;
	ctr = document.getElementById('edittask_priority_id');
	var priority_id = ctr.value;
	
	ctr = document.getElementById('edittask_status_id');
	var status_id = ctr.value;
	
	ctr = document.getElementById('edittask_proccess_id');
	var proccess_id = ctr.value;
	
	ctr = document.getElementById('edittask_description');
	var description = ctr.value;
	closePopup();
	var _url = '<?php echo URL;?>admin/task/action/?ac=addTask&rel_id=<?php echo $rel_id;?>&id=' + id;
	_url = _url + "&start_date=" + encodeURIComponent(start_date);
	_url = _url + "&end_date=" + encodeURIComponent(end_date);
	_url = _url + "&priority_id=" +priority_id;
	_url = _url + "&status_id=" + status_id;
	_url = _url + "&proccess_id=" +proccess_id;
	_url = _url + "&params=" + encodeURIComponent('<?php echo $params;?>');
	_url = _url + "&description=" + encodeURIComponent(description);
	_url = _url + "&resource=" + encodeURIComponent(resource);
	
	loadPage('pnTask', _url, function(status, message)
	{
		if(status== 0)
		{
			
			if(message.length == 36)
			{
				
				loadTask();
				
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}

function delTask(id)
{
	var result = confirm("Want to delete?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/task/action/?ac=delTask&id=' + id;

	loadPage('pnTask', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadTask();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}

</script>