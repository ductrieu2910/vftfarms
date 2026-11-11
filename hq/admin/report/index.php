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

if($ac == "view")
{
	$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.module_id";
	$sql = $sql." FROM ir_module_report d1 WHERE d1.status =0 AND d1.publish=1 AND d1.rel_id='".$module_id."'";
	$sql = $sql." ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	
	 $sql = " SELECT d2.id, d2.name, dg.description AS name_lg FROM ir_module_rel d1";
     $sql = $sql. " LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id)";
     $sql = $sql. " LEFT OUTER JOIN ir_action_type d3 ON(d2.type_id = d3.id)";
     $sql = $sql. " LEFT OUTER JOIN res_lang_line dg ON(d2.id = dg.rel_id AND dg.status =0 AND dg.lang_id='".$appSession->getConfig()->getProperty("lang_id") ."' AND dg.name='ir_action_name')";
     $sql = $sql. " WHERE d1.status =0 AND d2.status =0 AND d1.type='action_view'";
     $sql = $sql. " AND d1.module_id='".$module_id."'";
     $sql = $sql. " ORDER BY d2.sequence ASC, d2.name ASC";
	$msg->add("query", $sql);
	$reports = $appSession->getTier()->getArray($msg);
	  
	
?>
<div class="row  padding-small">
	<div class="col m12 responsive" >
		<table class="table">
			<thead>
				<tr class="text-left">
					<th  style="width: 20px">#</th>
					<th>Name</th>
					<th  style="width: 20px">Word</th>
					<th  style="width: 20px">Excel</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 0;
				for($i =0; $i<count($values); $i++)
				{
					$report_id = $values[$i][0];
					$module_id = $values[$i][3];
					$no  = $no + 1;
				?>
				<tr>
					<td style="width: 20px; text-align:center; valign-text:middle"><?php echo ($no);?></td>
					<td style="valign-text:middle"><a href="javascript:report('<?php echo $report_id;?>', '<?php echo $module_id;?>', 'view')"><?php echo $values[$i][2];?></a></th>
					<td style="valign-text:middle"><a href="javascript:report('<?php echo $report_id;?>', '<?php echo $module_id;?>', 'word')">Word</a></td>
					<td style="valign-text:middle"><a href="javascript:report('<?php echo $report_id;?>', '<?php echo $module_id;?>', 'excel')">Excel</a></td>
					
				</tr>
				<?php
				}
				?>
				<?php
				for($i =0; $i<count($reports); $i++)
				{
					$action_id = $reports[$i][0];
					$name = $reports[$i][2];
					if($name == "")
					{
						$name = $reports[$i][1];
					}
					$no  = $no + 1;
				?>
				<tr>
					<td style="width: 20px; text-align:center; valign-text:middle"><?php echo $no;?></td>
					<td style="valign-text:middle"><a href="javascript:action('<?php echo $action_id;?>', '<?php echo $name;?>', 'view')"><?php echo $name;?></a></th>
					<td style="valign-text:middle"><a href="javascript:action('<?php echo $action_id;?>', '<?php echo $name;?>', 'word')">Word</a></td>
					<td style="valign-text:middle"><a href="javascript:action('<?php echo $action_id;?>', '<?php echo $name;?>', 'excel')">Excel</a></td>
					
				</tr>
				<?php
				}
				?>
				
			</tbody>
		</table>
	</div>
</div>
<script>
	var selected_report_id = '';
	var selected_type = '';
	var selected_module_id = '';
	function report(report_id, module_id, type)
	{
		selected_report_id = report_id;
		selected_module_id = module_id;
		selected_type = type;
		openPopup('<?php echo URL;?>admin/module/filters/?module_id='+module_id+ '&root_module_id=' + module_id + '&module_model_id=&filters=&pageIndex=1');
	}
	function loadFilter1(data)
	{
		window.open('<?php echo URL;?>admin/report/?ac=report&report_id=' + selected_report_id + '&type=' + selected_type + '&data=' + encodeURIComponent(data) + '&module_id=' + selected_module_id);
		
	}
	var params = new Array();
	function action(action_id, $name, view_type)
	{
		params = new Array();
		var _url = '<?php echo URL;?>admin/report/?ac=action_param';
		_url = _url + '&action_id=' + action_id;
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(status == 0){
				if(message != "")
				{
					var lines = message.split("\n");
					for(var i =0; i<lines.length; i++)
					{
						var items = lines[i].split("\t");
						if(items.length>4)
						{
							params.push(items);
						}
					}
					travelParamValue(action_id, 0, view_type, name);
					
				}
			}
			
		}, true);
	}
	function travelParamValue(action_id, index, view_type, name)
	{
		if(index>=params.length)
		{
			var s = "";
			for(var i =0; i<params.length; i++)
			{
				s = s + '<br>';
				s = s + '<div class="row">';
					s = s + '<div class="col m4">';
					s = s + params[i][2] + ': '  ;
					s = s + '</div>';
					s = s + '<div class="col m8">';
					if(params[i][3] == "date")
					{
						s = s + '<input type ="date" class="input" onchange ="valueChange(this, ' + i + ');"/>';
					}else if(params[i][3] == "checkbox")
					{
						var items = params[i][4].split(";");
						
						for(var j =0; j<items.length; j++)
						{
							var idx = items[j].indexOf("=");
							if(idx != -1)
							{
								var k = items[j].substring(0, idx);
								var v = items[j].substring(idx + 1);
								
								s = s + '<input type ="checkbox" class="checkbox" value ="' +k + '" onchange ="valueChange(this, ' + i + ');"/>' + v + "<br>";
							}
						}
						
					}else if(params[i][3] == "textbox")
					{
						s = s + '<input type ="text" class="input" onchange ="valueChange(this, ' + i + ');"/>';
					}else if(params[i][3] == "select")
					{
						s = s + '<select class="select" onchange ="valueChange(this, ' + i + ');">';
						var items = params[i][4].split(";");
						
						for(var j =0; j<items.length; j++)
						{
							var idx = items[j].indexOf("=");
							if(idx != -1)
							{
								var k = items[j].substring(0, idx);
								var v = items[j].substring(idx + 1);
								
								s = s + '<option value ="' +k + '">' + v + "</option>";
							}
						}
						s = s + '</select>';
					}
					s = s + '</div>';
				s = s + '</div>';
			
			}
			s = s + "<br>";
				s = s + '<div class="row">';
					s = s + '<div class="col m4">&nbsp;';
					s = s + '</div>';
					s = s + '<div class="col m8">';
					s = s + '<button class="btn btn-primary" onclick="doAction(\'' +action_id + '\', \'' +view_type + '\', \'' + name + '\')">Filter</button>';
					s = s + '</div>';
				s = s + '</div>';
			ctr = document.getElementById('pnFullDialogContent');
			if(ctr != null)
			{
				ctr.innerHTML = s;
				document.getElementById('popView').style.display='block';
			}
			return;
		}
		var _url = '<?php echo URL;?>api/action/param/?type=' + params[index][3];
		_url = _url + '&id=' + action_id;
		_url = _url + '&user_id=<?php echo $appSession->getConfig()->getProperty("user_id");?>';
		_url = _url + '&company_id=<?php echo $appSession->getConfig()->getProperty("company_id");?>' ;
		_url = _url + '&value=' + encodeURIComponent(params[index][4]);
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(status == 0){
				params[index][4] = message;
				if(params[index][3] == "date")
				{
					params[index][5] = message;
				}
				
				travelParamValue(action_id, index + 1, view_type, name)
			}
			
		}, true);
	}
	function valueChange(theInput, index)
	{
		
		
		if(theInput.type == "checkbox")
		{
			if(theInput.checked == true)
			{
				var s = params[index][5];
				if(s != "")
				{
					s = s + ",";
				}
				s = s + theInput.value;
				params[index][5] = s;
			}else{
				var s = params[index][5];
				var items = s.split(",");
				s = "";
				for(var i =0; i<items.length; i++)
				{
					if(items[i] != theInput.value)
					{
						if(s != "")
						{
							s = s + ",";
						}
						s = s + items[i];
					}
				}
				params[index][5] = s;
			}
		}else{
			params[index][5] = theInput.value;
		}
		
		
	}
	function doAction(action_id, view_type, name)
	{
		var _url = "<?php echo URL;?>api/action/?action_id=" + action_id + "&view_type=" + view_type + "&view_name=" + encodeURIComponent(name);
		var data = "";
		for(var i =0; i<params.length; i++)
		{
			if(data != "")
			{
				data = data + "&";
			}
			data = data + params[i][1] + "=" + encodeURIComponent(params[i][5]);
		}
		_url = _url + "&data=" + encodeURIComponent(data);
		window.open(_url);
	}
</script>
<?php
}else if($ac == "report")
{
	$report_id = '';
	if(isset($_REQUEST['report_id']))
	{
		$report_id = $_REQUEST['report_id'];
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
		$appSession->getConfig()->getProperty("company_id", $company_id);
	}
	$data = '';
	if(isset($_REQUEST['data']))
	{
		$data = $_REQUEST['data'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$sql = "SELECT d1.name, d2.id AS resource_id,  d2.type AS resource_type";
	$sql = $sql." FROM ir_module_report d1 LEFT OUTER JOIN res_resource d2 ON(d1.id = d2.rel_id AND d2.status=0) WHERE d1.status =0 AND d1.id='".$report_id."'";
	$sql = $sql." ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values)>0 && $values[0][1] != "")
	{
		$params = 'ac=report&action_id='.$values[0][1].'&data='.$appSession->getTool()->urlEncode($data);
		$params = $params.'&module_id='.$module_id;
		$params = $params.'&report_id='.$report_id;
		$params = $params.'&user_id='.$appSession->getUserInfo()->getId();
		$params = $params.'&company_id='.$appSession->getUserInfo()->getCompanyId();
		$params = $params.'&db_id='.API_ID;
		$s = $appSession->getTool()->httpPost(URL."api/action/", $params);
		
	}else{
		
		$params = 'report_id='.$report_id.'&data='.$appSession->getTool()->urlEncode($data);
		$params = $params.'&module_id='.$module_id;
		$params = $params.'&user_id='.$appSession->getUserInfo()->getId();
		$params = $params.'&company_id='.$appSession->getUserInfo()->getCompanyId();
		$params = $params.'&api_id='.API_ID;
		$s = $appSession->getTool()->httpPost(URL_REPORT, $params);
	}
	if($type == "excel" || $type == "word")
	{
		$sql = "SELECT d1.name";
		$sql = $sql." FROM ir_module_report d1 WHERE d1.id='".$report_id."'";
		
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		$report_name = "";
		if(count($values)>0)
		{
			$report_name = $values[0][0];
			if($type == "excel")
			{
				header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
				header("Content-type:   application/x-msexcel; charset=utf-8");
				header("Content-Disposition: attachment; filename=".$report_name.".xls"); 
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
			}else{
				header("Content-Type:   application/vnd.ms-word; charset=utf-8");
				header("Content-type:   application/x-msexcel; charset=utf-8");
				header("Content-Disposition: attachment; filename=".$report_name.".doc"); 
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
			}
			
			 header("Pragma: no-cache");
			 header("Expires: 0");
		}
		
	}
	echo "<html>";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '<link rel="stylesheet" href="'.URL.'assets/report/css.css">';
	echo '<body>';
	echo $s;
	echo '</body>';
	echo '</html>';
	
}else if($ac == "action_param")
{
	$action_id = '';
	if(isset($_REQUEST['action_id']))
	{
		$action_id = $_REQUEST['action_id'];
	}
	$sql = "SELECT d1.id, d1.code, d1.name, d1.type, d1.value, dg.description AS name_lg";
    $sql = $sql.", '' AS result";
    $sql = $sql." FROM ir_action_param d1";
    $sql = $sql." LEFT OUTER JOIN res_lang_line dg ON(d1.id = dg.rel_id AND dg.status =0 AND dg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND dg.name='ir_action_name')";
    $sql = $sql." WHERE d1.status =0 AND d1.ir_id='".$action_id."'";
    $sql = $sql." ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		if($i>0)
		{
			echo "\n";
		}
		$name = $values[$i][5];
		if($name == "")
		{
			$name = $values[$i][2];
		}
		echo $values[$i][0]."\t".$values[$i][1]."\t".$name."\t".$values[$i][3]."\t".$values[$i][4]."\t";
	}
}
?>