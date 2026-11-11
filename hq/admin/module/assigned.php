
<div class="input" style="height:auto; min-height:32px" >
	<span id="assign_list"></span>
	<a class="margin-left" href="javascript:openPopup('<?php echo URL;?>admin/module/assigned/?ac=view&employee_type=employee')"><i class="icon-plus"></i></a>
	<span class="autocomplete" ><input class="search_input" id="myInput" placeholder="Search..." autocomplete="off"></span>
</div>
<script>
function loadAssigned()
{
	var _url = "<?php echo URL;?>admin/module/assigned/?ac=assigned_list&rel_id=<?php echo $value;?>";
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			var arr = message.split("\n");
			var ctr = document.getElementById('assign_list');
			var s = '';
			for(var i=0; i<arr.length; i++)
			{
				var items= arr[i].split("\t");
				if(items.length==2)
				{
					s = s + ' <a href="javascript:viewAssigned(\'' +items[0] + '\')">' + items[1] + '</a><a href="javascript:removeAssign(\'' +items[0] + '\')" class="margin-left">' + '<i class="icon-minus"></i></a>';
				}
			}
			if(ctr != null)
			{
				ctr.innerHTML = s;
			}
		}
		
	}, true);
}
loadAssigned();
function selUser(id, rel_id)
{
	if(id == "")
	{
		return;
	}
	var _url = "<?php echo URL;?>admin/module/assigned/?ac=add_assign&rel_id=<?php echo $value;?>&resource_id=" + id + "&type=<?php echo $table_name;?>&module_id=<?php echo $module_id;?>";
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadAssigned();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
	closePopup();
	
	
}
function removeAssign(id)
{
	var r = confirm("Are your sure to remove?");
	if (r == false) {
		return;
	} 
	var _url = "<?php echo URL;?>admin/module/assigned/?ac=remove_assign&id=" + id;
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				loadAssigned();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
}
function viewAssigned(id)
{
	openPopup('<?php echo URL;?>admin/module/assigned/?ac=assign_view&assign_id=' + id);
}
function addStatus(status_id, assign_id)
{
	var ctr = document.getElementById('editdescription_assign_status');
	var description = '';
	if(ctr != null)
	{
		description = ctr.value;
	}
	var _url = "<?php echo URL;?>admin/module/assigned/?ac=add_status&status_id=" + status_id + "&assign_id=" + assign_id;
	_url = _url + '&description=' + encodeURIComponent(description);
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				closePopup();
				loadAssigned();
			}else
			{
				alert(message);
			}
			
		}
		
	}, true);
}
</script>
<script>


var _url = "<?php echo URL;?>admin/module/assigned/?ac=search_user&search="
autocomplete(document.getElementById("myInput"), _url, function(input, id){ input.value = ""; selUser(id, '<?php echo $value;?>');});
</script>
