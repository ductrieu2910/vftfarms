<?php
$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$pageIndex = $appSession->getId();

$saved = "0";
$rel_id = "";
$prev_view_module_id = "";
$arr_params = explode('&', $params);
$paramList =[];
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$rel_id = $arrItem[1];
		}
		if($arrItem[0] == "view_module_id")
		{
			$prev_view_module_id = $arrItem[1];
		}
		if($arrItem[0] == "saved")
		{
			$saved = $arrItem[1];
		}
		
		$paramList[count($paramList)] = [$arrItem[0], $arrItem[1]];
	}
}

$msg = $appSession->getTier()->createMessage();


$module_caption = "";
$form_caption = "";	

$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d1.status =0 AND d2.group_id='".$module_model_id."'";
if($root_module_id != "")
{
	$sql = $sql. " AND (d2.module_id='".$module_id."' OR d2.module_id='".$root_module_id."')";
}else
{
	$sql = $sql. " AND d2.module_id='".$module_id."'";
}


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



$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename, d4.name AS module_name FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module d4 ON(d1.module_id = d4.id) WHERE d1.status =0 AND d1.group_id='".$module_model_id."'";
if($root_module_id != "")
{
	$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
}else
{
	$sql = $sql. " AND d1.module_id='".$module_id."'";
}
$sql = $sql." ORDER BY d1.create_date ASC";


$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$models = [];
$columns_model_id = "";
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$arr[3] = $row[3];
	$models[$i] = $arr;
	if($row[3] == "columns")
	{
		$columns_model_id = $row[0];
	}
	if($row[3] == "form_caption")
	{
		$form_caption = findValue($values, $row[0]);
		
	}
	
	if($module_caption == "")
	{
		$module_caption = $row[4];
	}
	
	
				
}

$columns = [];
$data_id = "";
$attributes = [];
$caption = "";
for($i =0; $i<count($models); $i++)
{
	
	if($models[$i][1] == $columns_model_id)
	{
		$column_id = "";
		
		
		for($j =0; $j<count($models); $j++)
		{
			if($models[$j][1] == $models[$i][0])
			{
				$value = findValue($values, $models[$j][0]);
				$appSession->getTool()->addAtt($attributes, $models[$i][0], $models[$j][3], $value);
				
			}
		}
		
		$columns[count($columns)] = [$models[$i][0], ""];
	}
	
	if($models[$i][3] == "src" || $models[$i][2] == "data")
	{
		$data_id = findValue($values, $models[$i][0]);
		
	}
	
}


$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
$appSession->getLang()->load($appSession->getTier(), 'form', $appSession->getConfig()->getProperty("lang_id"));



function parseForm($appSession, $layouts, $parent_id, $module_id, $id, $paramList, $columns, $attributes, $rel_id, $pageIndex, $root_module_id, $view_module_id, $table_name, $perList, $itemIndex, $cview)
{
	$msg = $appSession->getTier()->createMessage();


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
					parseForm($appSession,  $layouts, $_id, $module_id, $id, $paramList, $columns, $attributes, $rel_id, $pageIndex, $root_module_id, $view_module_id, $table_name, $perList, $itemIndex, $cview);
				echo "</div>";
			}else if($type == "col")
			{
				echo "<div class=\"col m".$value."\">";
					parseForm($appSession, $layouts, $_id, $module_id, $id, $paramList, $columns, $attributes, $rel_id, $pageIndex, $root_module_id, $view_module_id, $table_name, $perList, $itemIndex, $cview);
					if($data != "")
					{
						$pos = strpos($data, ',');
						
						if ($pos !== false)
						{
							$itemIndex = $itemIndex + 1;
							$module_model_id = substr($data, $pos + 1);
							for($c =0; $c<count($columns); $c++)
							{
								$column_id = $columns[$c][0];
								
								if($column_id == $module_model_id)
								{
									
									$caption = $appSession->getTool()->findAtt($attributes, $column_id, "caption");
									$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
									if($name == "")
									{
										$name = $column_id;
									}
									$name_language = "";
									if($name != "")
									{
										$type = $appSession->getTool()->findAtt($attributes, $column_id, "type");
										$action = $appSession->getTool()->findAtt($attributes, $column_id, "action");
										$default = $appSession->getTool()->findAtt($attributes, $column_id, "default");
									
										$width = $appSession->getTool()->findAtt($attributes, $column_id, "width");
										$readonly = $appSession->getTool()->findAtt($attributes, $column_id, "readonly");
										
										if($appSession->getTool()->indexOf($default, '|') != -1)
										{
											$index = $appSession->getTool()->indexOf($default, '{');
										
											$index2 = $appSession->getTool()->indexOf($default, '}');
										
											$k = $appSession->getTool()->substring($default, $index +1, $index2 -1);
											$k1 = $k;
											$k2 = "";
											$index = $appSession->getTool()->indexOf($k, '|');
											if($index != -1)
											{
												$k2 = $appSession->getTool()->substring($k, $index + 1);
												$k1 = $appSession->getTool()->substring($k, 0, $index);
												
											}
											
											for($pp =0; $pp<count($paramList); $pp++)
											{
												if($paramList[$pp][0] == $k1)
												{
													$k2 = $paramList[$pp][1];
												}
											}
											if($k2 == "NOW()")
											{
												$k2 = date('Y-m-d H:i:s');
											}
											$default = $k2;
											
										}else{
											for($pp =0; $pp<count($paramList); $pp++)
											{
												$default = str_replace("{".$paramList[$pp][0]."}", $paramList[$pp][1], $default);
											
											}
										}
										
							
										
										$default = str_replace("{user_id}", $appSession->getUserInfo()->getId(), $default);
										$default = str_replace("{rel_id}", $rel_id, $default);
										$default = str_replace("{parent_id}", $rel_id, $default);
										
										
										$required = $appSession->getTool()->findAtt($attributes, $column_id, "required");
										$name_language = $appSession->getTool()->replace($name, "__", "_");
										$caption = $appSession->getLang()->find($caption);
										
										
									?>
									<?php if($type == "hidden"){
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
									?>
									<input id="<?php echo $name;?>" type="hidden" value="<?php echo $value;?>"/>
									<?php
									}else{
										$name_language = $appSession->getTool()->replace($name, "__", "_");
									?>
									<div class="form-group">
									<?php if($type != "hidden" ){?>
									<label class="label" onclick="openPopup('<?php echo URL;?>admin/module/lang/?rel_id=<?php echo $id;?>&name=<?php echo $name_language;?>&caption=<?php echo $caption;?>')">
									<?php 
									if($type == "" || $type == "textarea")
									{
										$name_language = $name;
										$index = $appSession->getTool()->indexOf($name_language, "__");
										if($index != -1)
										{
											$name_language = $appSession->getTool()->substring($name_language, $index + 2);
										}
									?>
									<?php echo $caption;?>:
									<?php
									}else{
									?>
									<?php echo $caption;?>:
									<?php
									}
									?>
									
									<?php if($required == "1")
									{
									?>
									<span class="text-danger">*</span>
									<?php
									}
									?>
									</label>
									<?php } ?>
									<?php

									
									if($type == "")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											if($appSession->getTool()->indexOf($default, "result=")== -1)
											{
												$value = $default;
											}
											
										}
										
									?>
									
									<input id="<?php echo $name;?>" type="text" class="input" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> <?php if($width != ""){ ?> style="width:<?php echo $width;?>px" <?php } ?> value="<?php echo $value;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this);" <?php } ?> />
									<?php
									}
									else if($type == "float")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										
									?>
									<input onchange="calDefault<?php echo $pageIndex;?>(this)" id="<?php echo $name;?>" type="number" class="input" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> <?php if($width != ""){ ?> style="width:<?php echo $width;?>px" <?php } ?> value="<?php echo $value;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this)" <?php } ?> />
									<?php
									}else if($type == "currency")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										$value = $appSession->getCurrency()->format($appSession->getConfig()->getProperty("currency_id"), $value);
									?>
									<input onchange="calDefault<?php echo $pageIndex;?>(this)" id="<?php echo $name;?>" class="input" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> <?php if($width != ""){ ?> style="width:<?php echo $width;?>px" <?php } ?> value="<?php echo $value;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this)" <?php } ?> />
									<?php
									}
									else if($type == "integer")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
									?>
									<input onchange="calDefault<?php echo $pageIndex;?>(this)" id="<?php echo $name;?>" type="number" step="1" class="input" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> <?php if($width != ""){ ?> style="width:<?php echo $width;?>px" <?php } ?> value="<?php echo $value;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this)" <?php } ?> />
									<?php
									}else if($type == "textarea")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										$value = str_replace("<textarea>", "&lt;textarea&gt;", $value);
										$value = str_replace("</textarea>", "&lt;/textarea&gt;", $value);
									?>
									<textarea id="<?php echo $name;?>" class="textarea" rows="3" ><?php echo $value;?></textarea>
									<?php
									}else if($type == "password")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
									?>
									<input id="<?php echo $name;?>" type="password" class="input" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> <?php if($width != ""){ ?> style="width:<?php echo $width;?>px" <?php } ?> value="" placeholder="<?php echo $value;?>" <?php if($action != ""){?> onchange="elAction('<?php echo $action;?>', this)" <?php } ?> />
									<?php
									}
									else if($type == "datetime")
									{
										
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										
										$value = $appSession->getTool()->replace($value, " ", "T");
										$index = $appSession->getTool()->indexOf($value, '.');
										if($index != -1)
										{
											$value = $appSession->getTool()->substring($value, 0, $index);
										}
										
									?>
									
									<input id="<?php echo $name;?>" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> type="datetime-local" class="input" value="<?php echo $value;?>"/>
									<?php
									}else if($type == "date")
									{
										
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										
										$value = $appSession->getTool()->replace($value, " ", "T");
										$index = $appSession->getTool()->indexOf($value, 'T');
										if($index != -1)
										{
											$value = $appSession->getTool()->substring($value, 0, $index);
										}
										
									?>
									
									<input id="<?php echo $name;?>" <?php if($readonly == "1"){ echo " readonly='readonly'  "; }?> type="date" class="input" value="<?php echo $value;?>"/>
									<?php
									}else if($type == "daterange")
									{
										
										
									?>
									
									<input id="<?php echo $name;?>" type="text" name="daterange" class="input" value="Date Range"/>
									<script>
									$('input[name="daterange"]').daterangepicker({
										opens: 'left'
									  }, function(start, end, label) {
										console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
									  });
									</script>
									<?php
									}
									else if($type == "time")
									{
										$value = $columns[$c][1];
										$arr = explode(' ', $value);
										if(count($arr)>1)
										{
											$value = $arr[1];
										}
									?>
									<input id="<?php echo $name;?>" type="text" onblur="this.value=checkHourMinuteSecond(this.value)" class="input" value="<?php echo $value;?>"/>
									<?php
									}else if($type == "color")
									{
										$value = $columns[$c][1];
										
									?>
									<input id="<?php echo $name;?>" type="color" class="input" value="<?php echo $value;?>"/>
									<?php
									}else if($type == "image")
									{
										$value = $columns[$c][1];
										
									?>
									<input type="hidden" id="<?php echo $name;?>" value="<?php echo $value;?>">
									<a href="javascript:openPopup('<?php echo URL;?>document/index/?ac=view&pageIndex=<?php echo $pageIndex;?>&rel_id=<?php echo $module_id;?>&can_edit=1&img=<?php echo $name;?>&frm=frm<?php echo $pageIndex;?>')">
									<img id="img<?php echo $name;?>" width = "122" height = "92" <?php if($value != ""){?> src="<?php echo URL;?>document/action/?id=<?php echo $value;?>&width=122" <?php } ?>>
									</a>
									<?php
									}else if($type == "file")
									{
										$value = $columns[$c][1];
										
									?>
									<input type="hidden" id="<?php echo $name;?>" value="<?php echo $value;?>">
									<a href="javascript:openPopup('<?php echo URL;?>document/ac=view&pageIndex=<?php echo $pageIndex;?>&rel_id=<?php echo $module_id;?>&can_edit=1&img=<?php echo $name;?>&frm=frm<?php echo $pageIndex;?>')">
								...
									</a>
									<?php
									}else if($type == "point")
									{
										$value = $columns[$c][1];
										
									?>
									<input type="text" id="<?php echo $name;?>" name="<?php echo $name;?>" value="<?php echo $value;?>">
									<a href="javascript:window.open('<?php echo URL;?>admin/module/point/?ac=view&name=<?php echo $name;?>&value=<?php echo $value;?>')">
									...</a>
									<?php
									}
									else if($type == "checkbox")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
										
									?> <br><label>
									<input id="<?php echo $name;?>" class="checkbox" type="checkbox" <?php if($value == "1" 
									){ echo " checked ";}?>/>
									<span>&nbsp;</span>&nbsp;&nbsp;</label>
									<?php
									}else if($type == "editor")
									{
										$value = $columns[$c][1];
										if($value == "")
										{
											$value = $default;
										}
									?>
									
									<textarea id="<?php echo $name;?>" name="<?php echo $name;?>" class="textarea" rows="8" ><?php echo $value; ?></textarea>
									<script>
										tinymce.remove('#<?php echo $name;?>');
										tinymce.init({
										selector: '#<?php echo $name;?>',
										content_style: '.mce-content-body {font-size:10pt;font-family:Arial,sans-serif;}',
										toolbar: 'code | mybutton undo redo | image media | styleselect | bold italic | alignleft aligncenter alignright alignjustify fontselect fontsizeselect | bullist numlist outdent indent | link | table tabledelete',
										plugins: 'lists autolink table image code link table ',
										file_picker_types: 'file image media',
										image_uploadtab: true,
										menubar: false,
										height: 150,
									
										  document_base_url : "<?php echo URL;?>",
										  automatic_uploads: false,
										  images_upload_url: '<?php echo URL;?>/document/',
										  file_picker_types: 'image',
										  
										  file_picker_callback: function (cb, value, meta) {
											var input = document.createElement('input');
											input.setAttribute('type', 'file');
											input.setAttribute('accept', 'image/*');

											

											input.onchange = function () {
											  var file = this.files[0];

											  var reader = new FileReader();
											  reader.onload = function () {
												
												var base64 = reader.result.split(',')[1];
												var _url = '<?php echo URL;?>document/?ac=upload&data=' + encodeURIComponent(base64);
												_url = _url + "&name=" + file.name;
												_url = _url + "&company_id=<?php echo $appSession->getUserInfo()->getCompanyId();?>";
												loadPage('pnSensoryCriteria', _url, function(status, message)
												{
													if(status== 0 )
													{
														
														var sr = 'document/?id=' + message;
														 cb(sr);
													}
													
												}, true);

											  };
											  reader.readAsDataURL(file);
											};

											input.click();
										  }
									  
										
									});
									</script>
									<?php
									}else if($type == "module")
									{
										$value = $columns[$c][1];
										
										include( ABSPATH .'admin/module/module/index.php' );
									}else if($type == "sub_module")
									{
										$value = $columns[$c][1];
										
										include( ABSPATH .'admin/module/sub_module/index.php' );
									}else if($type == "assigned")
									{
										$value = $id;
										include( ABSPATH .'admin/module/assigned.php' );
									}
									else if($type == "comment")
									{
										$value = $id;
										include( ABSPATH .'admin/module/comment.php' );
									}else if($type == "action")
									{
									
									
										$appSession->getTool()->httpGet(URL."api/action/?session_id=".$appSession->getSessionId()."&action_id=".$default."&data=".$appSession->getTool()->urlEncode("rel_id=".$rel_id."&id=".$id."&cview=".$cview."&pageIndex=".$pageIndex."&module_id=".$module_id));
									}
									else if($type != "hidden")
									{
										
										$sql = "SELECT d2.name FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) WHERE d1.id='".$type."'";
										$msg->add("query", $sql);
										
										$result = $appSession->getTier()->getArray($msg);
										$numrows = count($result);	
										if($numrows>0)
										{
											$row =  $result[0];
											
											parseModel($appSession, $module_id, $row[0], $type, $columns[$c][1], "name=".$name."&action=".$action, $layouts, $layouts[$i][0], $pageIndex, $root_module_id, $rel_id, $view_module_id, $perList);
										}
									}
									?>
								</div>
									<?php
									}
									}
								}
							}
							
						}
					}
				echo "</div>";
			}
			
			
		}
	}
}
$numrows_grouped = 0;

$column = "";
$name = "";


	$condition = "";
	$group_by = "";
	$order_by = "";
	$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$data_id."' AND status =0";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	if($numrows_data_sql>0)
	{
		$row1 = $result_data_sql[0];
		$condition = $row1[0];
		$group_by = $row1[1];
		$order_by = $row1[2];
	}
	$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND (rel_id='".$data_id."' OR rel_id='".$module_id."') ORDER BY create_date ASC";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);
	$sql_params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[0];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$sql_params[$m] = $arr;
		$paramList[count($paramList)] = [$row1[0], $row1[2]];
	}
	
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
	$lines = [];
	$table_name = "";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);	
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$arr[3] = $row1[3];
		$arr[4] = $row1[4];
		$lines[$m] = $arr;
		if($table_name == "" && $row1[1] == "")
		{
			$table_name = $row1[2];
		}
	}
	$conditions = $appSession->getTier()->condition_array("");
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
	
	$isNew = false;
	if($id == "")
	{
		$id= $appSession->getTool()->getId();
	}
	
	$sql = $sql." WHERE ".$table_name.".id='".$id."'";
	
	

	$msg->add("query", $sql);
	
	$rel_ids = $module_id.",".$root_module_id;
	
	$result = $appSession->getTier()->getTable($msg);
	$parent_code = "";
	$numrows = $result->getRowCount();	
	if($numrows>0)
	{
		$row = $result->getRow(0);
		$parent_code = $row->getString($table_name."__receipt_no");
		if($parent_code == "")
		{
			$parent_code = $row->getString($table_name."__code");
		}
		
		if(count($columns)>0)
		{
			if($form_caption == "")
			{
				$form_caption = $row->getString(0);
			}
			
		}
		
		
		for($c =0; $c<count($columns); $c++)
		{
			$name = $appSession->getTool()->findAtt($attributes, $columns[$c][0], "name");
			
			$columns[$c][1] =  $row->getString($name);
			$form_caption = str_replace("{".$name."}", $columns[$c][1], $form_caption);
		}
		if($appSession->getTool()->indexOf($form_caption, "{") != -1)
		{
			for($c =0; $c<$result->getColumnCount(); $c++)
			{
				$name = $result->getColumns()[$c]->getName();
				$form_caption = str_replace("{".$name."}", $row->getString($name), $form_caption);
				
				
			}
		}
		for( $c =0; $c<$result->getColumnCount(); $c++)
		{
			
			if($appSession->getTool()->indexOf($result->getColumns()[$c]->getName(), "_id") != -1)
			{
				$_id = $row->getString($result->getColumns()[$c]->getName());
				if($_id != "")
				{
					$rel_ids = $rel_ids.",".$_id;
				}
			}
		}
		
		
	}else
	{
		$form_caption = "New - ".$module_caption;
		$isNew = true;
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
	$hasSub = false;
	for($g =0; $g<$numrows_grouped; $g++)
	{
		$row_group = $result_grouped[$g];
		if($row_group[5] == 'parent' || $row_group[6] == 'parent')
		{
			continue;
		}
		$hasSub = true;
		break;
	}
	
	
	$sql = "SELECT d2.id, d2.name, d2.description, d3.code FROM ir_module_rel d1 LEFT OUTER JOIN ir_action d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN ir_action_type d3 ON(d2.type_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND d1.type='action_form'";
	if($root_module_id != "")
	{
		$sql = $sql. " AND (d1.module_id ='".$module_id."' OR d1.module_id='".$root_module_id."')";
	}else
	{
		$sql = $sql." AND d1.module_id='".$module_id."'";
	}

	$sql = $sql." ORDER BY d2.sequence ASC, d2.name ASC";


	
	$msg->add("query", $sql);

	$actionList = $appSession->getTier()->getArray($msg);
	
	
	?>
	<div class="content-header">
		<div class="row">
			<div class="col m12" id ="navTop">
			</div>
		</div>
		<div class="row padding-top-small">
			<div class="col m8"><?php if(canPer($perList, "edit")){ ?>
			<button onclick="saveLine<?php echo $pageIndex;?>('frm<?php echo $pageIndex;?>')" class="button btn-primary"><?php echo $appSession->getLang()->find("Save");?></button>
			<?php } ?>
			<button type="button" class="button btn-secondary" onclick="back<?php echo $pageIndex;?>()"><?php echo $appSession->getLang()->find("Cancel");?></button>
				<?php
				if($isNew == false)
				{
					
					
					$sql = "SELECT d1.id, d1.name FROM ir_module_status d1 WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.company_id='".$appSession->getConfig()->getProperty("root_company_id")."' ORDER BY d1.sequence ASC, d1.name ASC";
					$msg->add("query", $sql);
					$statusList = $appSession->getTier()->getArray($msg);
					if(count($statusList)>0)
					{
						$sql = "SELECT d1.status_id FROM ir_module_status_log d1 WHERE d1.status =0 AND d1.rel_id='".$id."' ORDER BY create_date DESC LIMIT 1";
						$msg->add("query", $sql);
						$selected_status_id = $appSession->getTier()->getValue($msg);
						
								
						?>
						<a href="javascript:openPopup('<?php echo URL;?>addons/module/status_log.php?rel_id=<?php echo $id;?>')"><?php echo $appSession->getLang()->find("Status");?></a>: 
						 <select class="form-control" style="width:120px" onchange="postStatus('<?php echo $id;?>', this.value, function(status, message){})";>
							<option value=""></option>
							<?php
							for($i=0; $i<count($statusList); $i++)
							{
								$status_id = $statusList[$i][0];
								$status_name = $statusList[$i][1];
							?>
							<option value="<?php echo $status_id;?>" <?php if($status_id == $selected_status_id){ echo " selected ";}?>><?php echo $status_name;?></option>
							<?php
							}
							?>
						</select>
						<?php
					}
				}
				?>
			</div>
			<div class="col m4" style="text-align:right">
				
				<div class="dropdown-hover">
				  <span><img src="<?php echo URL;?>assets/img/more_vert.png"></span>
				  <div class="dropdown-content" style="right:0; text-align:left">
					<a href="javascript:del<?php echo $pageIndex;?>()" class="navi-link">
					<i class="icon-trash"></i> <?php echo $appSession->getLang()->find("Delete");?>
					</a>
					<?php
					for($a = 0; $a<count($actionList); $a++)
					{
					
						if($actionList[$a][3] == "action_page" || $actionList[$a][3] == "page")
						{
							
							continue;
						}
					?>
					<a href="javascript:irAction('<?php echo $actionList[$a][0]; ?>', '<?php echo $actionList[$a][3]; ?>', 'id=<?php echo $id;?>&rel_id=<?php echo $rel_id;?>&module_id=<?php echo $module_id;?>', on_action<?php echo $pageIndex;?>)"><i class="icon-arrow-right"></i> <?php echo $actionList[$a][1]; ?></a>
					<?php
						
					}
					?>
					<?php
					$sql = "SELECT id, name FROM ir_module WHERE status =0 AND publish=1 AND type = 'report_line' AND parent_id='".$module_id."' ORDER BY sequence ASC";
					$msg->add("query", $sql);
					$reports = $appSession->getTier()->getArray($msg);
					$numrows_report = count($reports);
					for($r = 0; $r<$numrows_report; $r++)
					{
					?>
					
						<i class="icon-doc"></i> <a href="<?php echo URL;?>addons/report/index.php?module_id=<?php echo $reports[$r][0];?>&model_name=form&id=<?php echo $id;?>" class="navi-link" target="_blank">
							
							<?php echo $appSession->getLang()->find($reports[$r][1]);?>
						</a>
					
					<?php
					}
					?>
					<a href="javascript:gt('contentView', '22332e18-bed2-46b5-b21e-91eef0629730', '', '', 'rel_id=<?php echo $id;?>', '')" class="navi-link">
						<i class="icon-paper-clip"></i> <?php echo $appSession->getLang()->find("Attachment");?>
					</a>
					<a href="javascript:editTask<?php echo $pageIndex;?>('<?php echo $id;?>')" class="navi-link">
						<i class="icon-user-follow"></i> <?php echo $appSession->getLang()->find("Task");?>
					</a>
					<a href="javascript:gt('contentView', '3fe9c732-157e-4a1e-f34d-c836b3114c94', '', '', 'rel_id=<?php echo $id;?>', '')" class="navi-link">
						<i class="icon-note"></i> <?php echo $appSession->getLang()->find("Note");?>
					</a>
				

					<?php
					for($g =0; $g<$numrows_grouped; $g++)
					{
						$row_group = $result_grouped[$g];
						if($row_group[5] == 'parent' || $row_group[6] == 'parent')
					  {
					  
					  $group_id = $row_group[0];
					  $group_name = $row_group[7];
					  if($group_name == "")
					  {
						  $group_name = $row_group[1];
					  }
					  $rel_id1 = $row_group[2];
					  $icon = $row_group[3];
					  if($icon == "")
					  {
						  $icon = $row_group[4];
					  }
					  
					  if($icon != "")
					  {
						  $icon = '<i class="'.$icon.'"></i> ';
					  }
					  if($rel_id1 != "")
					  {
						  $group_id = $rel_id1;
						  $rel_id1 = $row_group[0];
					  }
					  ?>
					  <a class="navi-link" href="javascript:gt('contentView', '<?php echo $group_id;?>', '', '', '', '<?php echo $rel_id1;?>');"><?php echo $icon;?><?php echo $group_name;?></a>
					  
					  <?php
						}
					  }
					  ?>
						<a href="#" class="navi-link">
							<i class="icon-question"></i> <?php echo $appSession->getLang()->find("Help");?>
						</a>
				  </div>
				</div>
					
			</div>
		</div>
	</div>
	<div class="content-body" >
		<div class="content-body-backcolor content-body-forecolor border shadow--1dp">
			<div class="row padding">
				<div class="col m4">
				<b>&nbsp;<?php echo $form_caption; ?></b>  <?php if($saved == "1"){ echo " - Data is saved. ".date("H:i:s"); }?>
				</div>
				<div class="col m8" style="text-align:right">
					<?php
					if($isNew == false){
					?>
					<table class="sub-module right">
					<?php 
					
				  for($g =0; $g<$numrows_grouped; $g++)
				  {
					 
					  $row_group = $result_grouped[$g];
					  $group_id = $row_group[0];
					  $group_name = $row_group[7];
					  if($group_name == "")
					  {
						  $group_name = $row_group[1];
					  }
					  $rel_id1 = $row_group[2];
					  $icon = $row_group[3];
					  if($icon == "")
					  {
						  $icon = $row_group[4];
					  }
					  if($row_group[5] == 'parent' || $row_group[6] == 'parent')
					  {
						  continue;
					  }
					  if($row_group[5] == 'sub-tab' || $row_group[6] == 'sub-tab')
					  {
						  continue;
					  }
					  
					  if($icon != "")
					  {
						  $icon = '<i class="'.$icon.'"></i> ';
					  }
					  if($rel_id1 != "")
					  {
						  $group_id = $rel_id1;
						  $rel_id1 = $row_group[0];
					  }
					  if($sub_count == 0)
					  {
						  echo "<tr>";
					  }
					  
					 
				  ?>
				  <td style="text-align:left"><a class="border" onclick="gt('contentView', '<?php echo $group_id;?>', '', '', 'rel_id=<?php echo $id;?>', '<?php echo $rel_id1;?>');"><?php echo $icon;?><?php echo $group_name;?></a></td>
				  <?php
					$sub_count = $sub_count + 1;
					if($sub_count == 3)
					{
						echo "</tr>";
						$sub_count = 0;
					}
				  }
				  
				  ?>
				  <?php
					for($a = 0; $a<count($actionList); $a++)
					{
						if($actionList[$a][3] == "page"){
							if($sub_count == 0)
					  {
						  echo "<tr>";
					  }
					?>
					 <td style="text-align:left"><a class="border" href="javascript:irAction('<?php echo $actionList[$a][0]; ?>', '<?php echo $actionList[$a][3]; ?>', 'id=<?php echo $id;?>&rel_id=<?php echo $rel_id;?>&module_id=<?php echo $module_id;?>', on_action<?php echo $pageIndex;?>)"> <?php echo $actionList[$a][1]; ?></a></td>
					<?php
							$sub_count = $sub_count + 1;
							if($sub_count == 3)
							{
								echo "</tr>";
								$sub_count = 0;
							}
						}
					}
					 if($sub_count != 0)
				  {
					  echo "</tr>";
				  }
					?>
				  </table>
				 <?php
					}
					?>
				</div>
			</div>
			<div class="row">
				<div class="col m12">
					
					<form id="frm<?php echo $pageIndex;?>" name="frm<?php echo $pageIndex;?>">
						<?php
							parseForm($appSession, $layouts, $layout_id, $module_id, $id, $paramList, $columns, $attributes, $rel_id, $pageIndex, $root_module_id, $view_module_id, $table_name, $perList, 0, $cview);
						?>
					</form>
				</div>
				
			</div>
		
			<?php
			if($isNew == false){
				for($g =0; $g<$numrows_grouped; $g++)
				{
					 $row_group = $result_grouped[$g];
					if($row_group[5] == 'sub-tab' || $row_group[6] == 'sub-tab')
					{
						$page_id = $row_group[0];
						$page_name = $row_group[7];
						if($page_name == "")
						{
							$page_name = $row_group[1];
						}
						
						?>
						<div class="row padding">
							<div class="col m12"><?php echo "<label class=\"label\">".$page_name."</label>";?><br>
							<div  id="pn<?php echo $page_id;?>"></div>
							</div>
						</div>
						
						<?php
					}
				}
			}
			?>
			<?php
			for($a = 0; $a<count($actionList); $a++)
			{
				if($actionList[$a][3] == "action_page")
				{
					$page_id = $actionList[$a][0];
					$page_name = $actionList[$a][1];
					
			?>
				<div class="row padding">
					<div class="col m12"><?php echo "<label class=\"label\">".$page_name."</label>";?><br>
					<div  id="pn<?php echo $page_id;?>"></div>
					</div>
				</div>
			<?php
				}
			}
			?>		
		</div>
	</div>
	
	<script>
		
		
		function back<?php echo $pageIndex;?>()
		{
			
			clearNav(navList.length -2);
			gt('<?php echo $cview;?>', '<?php echo $module_id;?>', '<?php echo $prev_view_module_id;?>', '', '<?php echo $params;?>', '<?php echo $root_module_id;?>');
		}
		<?php
		$date_format = $appSession->getConfig()->getProperty("date_format");
		$date_format = $appSession->getTool()->replace($date_format, "D", "d");
		$date_format = $appSession->getTool()->replace($date_format, "Y", "y");
		
		?>
		
		var date_format='<?php echo $date_format;?>';
		var arr_default<?php echo $pageIndex;?> =[
		<?php
		$hasItem = 0;
		for($c =0; $c<count($columns); $c++)
		{
			
			$column_id = $columns[$c][0];
			$default = $appSession->getTool()->findAtt($attributes, $column_id, "default");
			
			$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
			if($default != "")
			{
				$type = $appSession->getTool()->findAtt($attributes, $column_id, "type");
				if($hasItem >0)
				{
					echo ", ";
				}
				echo "['".$name."', '".$default."', '".$type."']";
				$hasItem = 1;
			}
			
		}
		?>
		];
		function saveLine<?php echo $pageIndex;?>(frmId)
		{
			var columns =[
			<?php
			for($c =0; $c<count($columns); $c++)
			{
				if($c>0)
				{
					echo ", ";
				}
				$column_id = $columns[$c][0];
				$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
				$type = $appSession->getTool()->findAtt($attributes, $column_id, "type");
				echo "['".$name."', '".$type."']";
			}
			?>
			];
			var arr_required =[
			<?php
			$hasItem = 0;
			for($c =0; $c<count($columns); $c++)
			{
				
				$column_id = $columns[$c][0];
				$required = $appSession->getTool()->findAtt($attributes, $column_id, "required");
				$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
				if($required != "")
				{
					if($hasItem>0)
					{
						echo ", ";
					}
					$required = str_replace("'", "\"", $required);
					echo "['".$name."', '".$required."']";
					$hasItem = 1;
				}
				
			}
			?>
			];
			var ctr = null;
			for(var i=0; i<arr_required.length; i++)
			{
				var name = arr_required[i][0];
			
				ctr = document.getElementById(arr_required[i][0]);
				
				var conditions = arr_required[i][1].split(';');
				for(var n =0; n<conditions.length; n++)
				{
					var condition = '';
					var message = '';
				
					var items = conditions[n].split(',');
					for(var j=0; j<items.length; j++)
					{
						var index = items[j].indexOf('=');
						if(index != -1)
						{
							var key = items[j].substring(0, index).trim();
							var val = items[j].substring(index + 1).trim();
							if(key == "condition")
							{
								condition = val;
							}else if(key == "message")
							{
								message = val;
							}
							
						}
					}
					if(message != "" && ctr != null)
					{
						if(ctr.type == 'number')
						{
							condition = condition.replace('{value}', ctr.value.replace("'", ""));
						}else
						{
							condition = condition.replace('{value}', "'" + ctr.value.replace("'", "") + "'");
						}
						
						if(eval(condition) == true)
						{
							alert(message);
							ctr.focus();
							return;
						}
					}
					
				}
			}
			
			var arr_unique =[
			<?php
			$hasItem = 0;
			for($c =0; $c<count($columns); $c++)
			{
				
				$column_id = $columns[$c][0];
				$unique = $appSession->getTool()->findAtt($attributes, $column_id, "unique");
				$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
				if($unique == "1")
				{
					if($hasItem > 0)
					{
						echo ", ";
					}
					echo "'".$name."'";
					$hasItem = 1;
				}
				
			}
			?>
			];
			
			var arr_sequence =[
			<?php
			$hasItem = 0;
			for($c =0; $c<count($columns); $c++)
			{
				
				$column_id = $columns[$c][0];
				$sequence = $appSession->getTool()->findAtt($attributes, $column_id, "sequence");
				$name = $appSession->getTool()->findAtt($attributes, $column_id, "name");
				if($sequence != "")
				{
					if($hasItem>0)
					{
						echo ", ";
					}
					$sequence = str_replace("'", "\"", $sequence);
					
					echo "['".$name."', '".$sequence."']";
					$hasItem = 1;
				}
				
			}
			?>
			];
			
			var params = "";
			var frm = document.getElementById(frmId);
			
			for(var i=0; i<columns.length; i++)
			{
				var name = columns[i][0];
				
				var index = name.indexOf('__');
				
				if(index !=-1)
				{
					name = name.substring(index + 2);
					if(name == "")
					{
						continue;
					}
					
					if(name != "id" && frm != null)
					{
						
						if(params != "")
						{
							params = params + "`";
						}
						for(var x =0; x<frm.elements.length; x++)
						{
							var unique = "";
							var sequence = "";
							for(var j=0; j<arr_unique.length; j++)
							{
								if(arr_unique[j] == '<?php echo $table_name;?>__' + name)
								{
									unique = "1";
								}
							}
							for(var j=0; j<arr_sequence.length; j++)
							{
								if(arr_sequence[j][0] == '<?php echo $table_name;?>__' + name)
								{
									sequence = arr_sequence[j][1];
								}
							}
							
							if(frm.elements[x].id == columns[i][0])
							{
								if(frm.elements[x].type == 'checkbox')
								{
									if(frm.elements[x].checked)
									{
										params = params + name + "~1";
									}else
									{
										params = params + name + "~0";
									}
								}else if(frm.elements[x].type == 'password' && frm.elements[x].value != "")
								{
									params = params + name + "~" +  Sha1.hash(frm.elements[x].value);
								}
								else
								{
									var value = frm.elements[x].value;
									if(columns[i][1] == "date")
									{
										
										if(value != "")
										{
											const dateObj = new Date();
											var hour = dateObj.getHours();
											hour = ('0' + hour).slice(-2);
											
											var minute = dateObj.getMinutes();
											minute = ('0' + minute).slice(-2);
										

											var second = dateObj.getSeconds();
											second = ('0' + second).slice(-2);
											value = value + " " + hour + ":" + minute + ":" + second;
											
										}
										
									}else if(columns[i][1] == "currency")
									{
										
										value = currency_parse('<?php echo $appSession->getConfig()->getProperty("currency_id");?>', value);
										
									}else if(columns[i][1] == "editor")
									{
									
										value = tinymce.get('<?php echo $table_name;?>__' + name).getContent();
										while(true)
										{
											var index = value.indexOf(' src="disk/editor/');
											
											if(index == -1)
											{
												break;
											}
											value = value.replace(' src="disk/editor/', ' src="<?php echo URL;?>disk/editor/');
											
											
										}
										
										
									}
									
									params = params + name + "~" + value.replace("~", "[007E]");
								}
								
								params = params + '~'+ unique + '~' + sequence + '~' + columns[i][1];
							
							}
						}
					}
				}
			}
			
			var _url = '<?php echo URL?>admin/module/action/?ac=save&table_name=<?php echo $table_name;?>&id=<?php echo $id;?>';
			if(params != "")
			{
				_url = _url + "&params=" + encodeURIComponent(params);
			}
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					var index = message.indexOf("OK:");
					
					if(index != -1)
					{
						var id = message.substring(index + 3);
						<?php
						if($hasSub == true)
						{
						?>
					
						clearNav(navList.length -1);
						gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', id, '<?php echo $params;?>&saved=1','<?php $root_module_id;?>');
						<?php 
						}else{
						?>
						back<?php echo $pageIndex;?>();
						<?php
						}
						?>
					}else
					{
						if(message.indexOf(';') != -1)
						{
							var arr = message.split(';');
							for(var i =0; i<arr.length; i++)
							{
								var items = arr[i].split(',');
								if(items.length>2)
								{
									if(items[2] == 'unique')
									{
										alert(items[0] + " is unique");
										
									}
								}else{
									alert(arr[i]);
								}
							}
						}else{
							alert(message);
						}
						
					}
					
				}
				
			}, true);
		}
		function del<?php echo $pageIndex;?>()
		{
			var result = confirm("Want to delete?");
			if (!result) {
				return;
			}
			saveLineValue('<?php echo $table_name;?>', 'status', '', '<?php echo $id;?>', "1", function(status, message){
				if(status == 0)
				{
					if(message == "OK")
					{
						back<?php echo $pageIndex;?>();
					}
					
				}
			}
			);
		
		}
		function elAction(id, theInput)
		{
			
			var _url = '<?php echo URL?>admin/module/action/?ac=action&id=' +id;
			_url = _url + "&value=" + encodeURIComponent(theInput.value);
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
				
					var items = message.split(';');
					for(var i=0; i<items.length; i++)
					{
						var index = items[i].indexOf('=');
						if(index != -1)
						{
						
							var name = items[i].substring(0, index).trim();
							name = '<?php echo $table_name;?>__' + name;
							var value = items[i].substring(index + 1).trim();
							var ctr = document.getElementById(name);
				
							if(ctr != null)
							{
								ctr.value = value;
							}
						}
					}
					
				}
				
			}, true);
		}
		function postStatus<?php echo $pageIndex;?>(id)
		{
			var result = confirm("Want to post status?");
			if (!result) {
				return;
			}
			var _url = '<?php echo URL;?>admin/action/?ac=postStatus';
			_url = _url + '&rel_id=<?php echo $id;?>';
			_url = _url + '&status_id=' + id;
			loadPage('pnRoutingLine', _url, function(status, message)
			{
				if(message == "OK")
				{
					gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '<?php echo $id;?>');
				}else
				{
					alert(message);
				}
				
			}, true);
		}
		
		addNav('<?php echo $appSession->getTool()->replace($form_caption, "\n", "");?>', 'contentView', '<?php echo $module_id;?>', '<?php echo $model_name;?>', '<?php echo $id;?>', '<?php echo $params;?>', '<?php echo $root_module_id;?>');
		printNav("navTop");
		
		
function calDefault<?php echo $pageIndex;?>(theInput)
{
	for(var i=0; i<arr_default<?php echo $pageIndex;?>.length; i++)
	{
		var item = arr_default<?php echo $pageIndex;?>[i];
		if(item[1].indexOf(theInput.id) != -1)
		{
			var value = item[1];
			var len = 0;
			while(true)
			{
				var index = value.indexOf("{");
				if(index == -1)
				{
					break;
				}
				var id = value.substring(index + 1);
				var index = id.indexOf("}");
				if(index == -1)
				{
					break;
				}
				id = id.substring(0, index);
		
				var ctr = frm<?php echo $pageIndex;?>[id];
				var val = 0;
				if(ctr != null)
				{
					val = ctr.value;
				}
				if(item[2] == "float" || item[2] == "integer")
				{
					if(val == '')
					{
						val = 0;
					}
				}else if(item[2] == "currency")
				{
					val = currency_parse('<?php echo $appSession->getConfig()->getProperty("currency_id");?>', val);
				}
				
				value = replaceAll(value, "{" + id + "}", val);
				len = len + 1;
				if(len>30)
				{
					break;
				}
				
			}
			try {
				value = eval(value);
			} catch (e) {
				value = 0;
			}
			
			
			var ctr = frm<?php echo $pageIndex;?>[item[0]];
			if(ctr != null)
			{
				if(item[2] == "float" || item[2] == "integer")
				{
					if(!isNaN(value))
					{
						ctr.value = value;
						calDefault<?php echo $pageIndex;?>(ctr);
					}
				}else{
					ctr.value = value;
					calDefault<?php echo $pageIndex;?>(ctr);
				}
			}
			
			
			
		}
	}
}
<?php
if($isNew == false){
	for($g =0; $g<$numrows_grouped; $g++){
		$row_group = $result_grouped[$g];
		if($row_group[5] == 'sub-tab' || $row_group[6] == 'sub-tab')
		{
			$page_id = $row_group[0];
		
			
			?>
				gt('pn<?php echo $page_id ;?>', '<?php echo $page_id;?>', '', '', 'rel_id=<?php echo $rel_id;?>&id=<?php echo $id;?>','');
			<?php
		}
	}
}
?>
<?php
for($a = 0; $a<count($actionList); $a++)
{
	if($actionList[$a][3] == "action_page")
	{
		$page_id = $actionList[$a][0];
		
		
?>
loadPage('pn<?php echo $page_id ;?>', '<?php echo URL;?>api/action/?ac=view&action_id=<?php echo $page_id;?>&data='+encodeURIComponent('id=<?php echo $id;?>&rel_id=<?php echo $rel_id;?>'), function(status, message)
{
	
	
}, false);

<?php
	}
}
?>	

function on_action<?php echo $pageIndex;?>(type, status, message)
{
		
	if(type == "load")
	{
		
		
	}else
	{
		alert(message);
	}
}
function editTask<?php echo $pageIndex;?>(id)
{
	var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&rel_id='+ id + '&module_id=<?php echo $module_id;?>&name=' + encodeURIComponent('<?php echo $form_caption;?>');
	openPopup(_url);
}

</script>