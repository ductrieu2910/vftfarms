<?php
$msg = $appSession->getTier()->createMessage();
$arr_params = explode('&', $params);
$rel_id = "";
$parent_code = "";
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$rel_id = $arrItem[1];
		}
		if($arrItem[0] == "parent_code")
		{
			$parent_code = $arrItem[1];
		}
	}
}
$sql = "SELECT code FROM product_classification WHERE id='".$rel_id."'";
$msg->add("query", $sql);
$value = $appSession->getTier()->getValue($msg);
if($value != "")
{
	$parent_code = $value;
}


?>
<a href="javascript:addAttribute('', '');"><i class="icon-plus"> <?php echo $appSession->getLang()->find("New");?></i></a> | <a href="javascript:copyFrom();"><i class="icon-share"> <?php echo $appSession->getLang()->find("Copy");?></i></a>
<br>
<div id="pnAttribute" style="padding-top:5px"></div>
<script>
function loadAttribute()
{
	var _url = '<?php echo URL;?>admin/attribute/line/?rel_id=<?php echo $rel_id;?>';
	loadPage('pnAttribute', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
loadAttribute();
function addAttribute(parent_id, category_id)
{
	var name = prompt("Please enter name", "");
	if (name == null || name == "") {
		return;
	}
	var _url = "<?php echo URL;?>admin/attribute/action/?ac=addAttribute&rel_id=<?php echo $rel_id;?>&code=<?php echo $parent_code;?>&parent_id=" + parent_id + '&category_id=' + category_id;
	_url = _url + "&name=" + encodeURIComponent(name);
	
	loadPage('pnAttribute', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.length == 36)
			{
				loadAttribute();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
	
}

function delAttribute(id)
{
	var result = confirm("Want to delete?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/attribute/action/?ac=delAttribute&id=' + id;

	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadAttribute();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function copyFrom()
{
	
	openPopup('<?php echo URL;?>admin/module/list_search/?ac=search&module_id=a247574c-64a5-4492-8327-878e745f8644&module_model_id=23038a0d-d30f-4ec6-a0d2-d5c27d70c82f&func=selClassificationCopy')
}
function selClassificationCopy(id, name)
{
	
	closePopup();
	var _url = '<?php echo URL;?>admin/attribute/action/?ac=copyAttribute&to_id=<?php echo $rel_id;?>&from_id=' + id;
	
	loadPage('pnAttribute', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK") != -1)
			{
				loadAttribute();
			}else{
				alert(message);
			}
		}
		
	}, true);
}
function uploadSheet(id, attribute_id)
{
	var ctr = document.getElementById(id);
	if(ctr != null)
	{
		if(ctr.length == 0){
			alert("Select file");
			return;
		}
		var data = new FormData()
		data.append('file', ctr.files[0])
		data.append('attribute_id', attribute_id)

		fetch('<?php echo URL;?>/api/attribute/upload_sheet/', {
		  method: 'POST',
		  body: data
		}).then(
			response => { loadAttribute(); } 
		  ).then(
			success => console.log(success) 
		  ).catch(
			error => console.log(error) 
		  );
	}
}

</script>