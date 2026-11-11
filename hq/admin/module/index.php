<?php
	
	$msg = $appSession->getTier()->createMessage();
	
	
	$module_id = "";
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
	$root_module_id = "";
	if(isset($_REQUEST['root_module_id']))
	{
		$root_module_id = $_REQUEST['root_module_id'];
	}
	$model_name = "";
	if(isset($_REQUEST['model_name']))
	{
		$model_name = $_REQUEST['model_name'];
	}
	if($model_name == "")
	{
		$model_name = "table";
	}
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$params = "";
	if(isset($_REQUEST['params']))
	{
		$params = $_REQUEST['params'];
	}
	
	$view_module_id = $model_name;
	$sql = "SELECT d1.id, d1.name, d1.data FROM ir_module_line d1 WHERE d1.status =0";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql. " AND d1.module_id='".$module_id."'";
	}
	
    $sql = $sql." AND (d1.type='".$model_name."' OR d1.id='".$model_name."')";
	
	$sql = $sql." ORDER BY d1.sequence ASC, d1.create_date ASC LIMIT 1";


	$msg->add("query", $sql);
	$result_line = $appSession->getTier()->getArray($msg);
	$numrows_line = count($result_line);

	$data = "";
	if($numrows_line>0)
	{
		$row = $result_line[0];
		//$view_module_id = $row[0];
		$view_name = $row[1];
		$data = $row[2];
	}
	$sql = "SELECT ac_name, ac_value FROM res_user_group_per WHERE status =0 AND group_id='".$appSession->getUserInfo()->getGroupId()."' AND module_name='".$module_id."'";
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);
	$perList = array();
	for($i = 0; $i < $numrows; $i++) 
	{
		$row = $result[$i];
		$arr = array();
		$arr[0] = $row[0];
		$arr[1] = $row[1];
		$perList[$i] = $arr;
	}
	function canPer($perList, $type)
	{
		
		for($i =0; $i<count($perList); $i++)
		{
			
			if($perList[$i][0] == $type && $perList[$i][1] == "0")
			{
				return false;
			}
		}
		return true;
	}
	
	function findValue($values, $id)
	{
		for($i =0; $i<count($values); $i++)
		{
			if($values[$i][0] == $id)
			{
				return $values[$i][1];
			}
		}
		return "";
	}
	function parseModel($appSession, $module_id, $model_name, $module_model_id, $id, $params, $layouts, $layout_id, $pageIndex, $root_module_id, $rel_id, $view_module_id, $perList)
	{
		
		if($model_name == "table")
		{
			include( ABSPATH .'admin/module/table.php' );
		}if($model_name == "treetable")
		{
			include( ABSPATH .'admin/module/treetable.php' );
		}
		else if($model_name == "form")
		{
			
			include( ABSPATH .'admin/module/form.php' );
		}else if($model_name == "dropdown")
		{
			include( ABSPATH .'admin/module/dropdown.php' );
		}else if($model_name == "list")
		{
			
			include( ABSPATH .'admin/module/list.php' );
		}else if($model_name == "page")
		{
			include( ABSPATH .'admin/module/page.php' );
			
		}else if($model_name == "multiple")
		{
			include( ABSPATH .'admin/module/multiple.php' );
		}else if($model_name == "resource")
		{
			include( ABSPATH .'admin/module/resource.php' );
		}else if($model_name == "convert")
		{
			include( ABSPATH .'admin/module/convert.php' );
		}else if($model_name == "mail")
		{
			include( ABSPATH .'admin/module/mail.php' );
		}else if($model_name == "action")
		{
			ob_clean();
			include( ABSPATH .'admin/module/action.php' );
		}
	}
	function parseLine($appSession, $layouts, $parent_id, $module_id, $id, $params, $root_module_id, $view_module_id, $perList)
	{
		for($i =0; $i<count($layouts); $i++)
		{
			if($layouts[$i][1]== $parent_id)
			{
				$_id = $layouts[$i][0];
				$type = $layouts[$i][2];
				$value = $layouts[$i][3];
				
				$data = $layouts[$i][4];
				if($type == "row")
				{
					echo "<div class=\"row\">";
						parseLine($appSession,  $layouts, $_id, $module_id, $id, $params, $root_module_id, $view_module_id, $perList);
					echo "</div>";
				}else if($type == "col")
				{
					echo "<div class=\"col m".$value."\" >";
						if($data != "")
						{
							$pos = strpos($data, ',');
							if ($pos !== false)
							{
								$model_name = substr($data, 0, $pos);
								$module_model_id = substr($data, $pos + 1);
							
								parseModel($appSession, $module_id, $model_name, $module_model_id, $id, $params, $layouts, $layouts[$i][0], "", $root_module_id, "", $view_module_id, $perList);
							}
						}
						parseLine($appSession, $layouts, $_id, $module_id, $id, $params, $root_module_id, $view_module_id, $perList);
					echo "</div>";
				}
				
				
			}
		}
	}
	$arr = explode(':', $data);

	$layouts = [];
	for($i=0; $i<count($arr); $i++)
	{
		$item = explode(';', $arr[$i]);
		$_id = "";
		$parent_id = "";
		$type = "";
		$value = "";
		$data = "";
		for($j=0; $j<count($item); $j++)
		{
			$arrItem = explode('=', $item[$j]);
			if(count($arrItem)>0)
			{
				if($arrItem[0] == "id")
				{
					$_id = $arrItem[1];
				}else if($arrItem[0] == "parent_id")
				{
					$parent_id = $arrItem[1];
				}else if($arrItem[0] == "type")
				{
					$type = $arrItem[1];
				}else if($arrItem[0] == "value")
				{
					$value = $arrItem[1];
				}else if($arrItem[0] == "data")
				{
					$data = $arrItem[1];
				}
			}
		}
		if($_id != "")
		{
			$layouts[count($layouts)] = [$_id, $parent_id, $type, $value, $data];
		}
	}
	if($model_name == "view")
	{
		include( ABSPATH .'admin/module/view.php' );
	}else{
		parseLine($appSession, $layouts, "", $module_id, $id, $params, $root_module_id, $view_module_id, $perList);
	}
	
	
?>
<script>
function irRunAction(id, type, params, onresult)
{
	
	var ac = 'view';
	if(type== 'report')
	{
		ac = "report_action";
	}
	var _url = "<?php echo URL;?>api/action/?ac="+ ac + "&action_id=" + id;
	
	if(params != "")
	{
		_url = _url + "&data=" + encodeURIComponent(params);
	}
	if(type == "blank")
	{
		window.open(_url);
	}else if(type == "report")
	{
		_url = _url + "&type=view";
		window.open(_url);
	}else if(type == "popup" )
	{
		
		openPopup(_url);
	}else if(type == "page" )
	{
		
		openPopup(_url);
	}else{
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(onresult != null)
				{
					onresult(type, status, message);
				}
				
			}
			
		}, true);
	}
	
	
}

function irAction(id, type, params, onresult)
{
	
	irRunAction(id, type, params, onresult);
	
}

</script>

