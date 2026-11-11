<?php

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
	$sql = "SELECT name, type, data FROM parameter WHERE status =0";
    $sql = $sql." AND (rel_id='".$data_id."' OR rel_id='".$module_id."')";
	$sql = $sql." ORDER BY create_date ASC";
	
	$msg->add("query", $sql);
	$result_data_sql = $appSession->getTier()->getArray($msg);
	$numrows_data_sql = count($result_data_sql);
	$sql_params = [];
	for($m =0; $m<$numrows_data_sql; $m++)
	{
		$row1 = $result_data_sql[$m];		
		$arr = [];
		$arr[0] = $row1[0];
		$arr[1] = $row1[1];
		$arr[2] = $row1[2];
		$sql_params[$m] = $arr;
		
	}
	
	$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.data, d1.relationship, d2.order_by FROM ir_data_line  d1 LEFT OUTER JOIN ir_data d2 ON(d1.data_id = d2.id) WHERE d1.data_id ='".$data_id."' AND d1.status =0";
				
	$lines = [];
	
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
		$arr[5] = $row1[5];
		$lines[$m] = $arr;
		
		if($table_name == "" && $row1[1] == "")
		{
			$table_name = $row1[2];
		}
		
	}
	if($filters != "")
	{
		if($appSession->getTool()->indexOf($filters, "assigned") != -1)
		{
			$arr = [];
			
			$conditions = explode(',', $filters);
			
			for($i =0; $i<count($conditions); $i++)
			{
				if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") == -1)
				{
					
					if($condition != "")
					{
						$condition = $condition.",";
					}
					$condition = $condition.$conditions[$i];
				}
			}
			$condition = $appSession->getTool()->replace($condition, ":", "=");
			
			
		}else
		{
			if($condition != "")
			{
				$condition = $condition.",";
			}
			$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
		}
		
	}
	$conditions = $appSession->getTier()->condition_array($condition);

	
	$sql = $appSession->getTier()->buildSQL($lines, "", $sql_params, $conditions);
	
	
	
	if($condition == "")
	{
		$sql = $sql." WHERE ".$table_name.".status=0";
	}else{
		$sql = $sql." AND ".$table_name.".status=0";
	}

	
	$sql = str_replace("{rel_id}", "'".$rel_id."'", $sql);
	$sql = str_replace("{user_id}", "'".$appSession->getUserInfo()->getId()."'", $sql);
	if($appSession->getTool()->indexOf($filters, "assigned") != -1)
	{
		$conditions = explode(',', $filters);
		$condition = "";
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
			{
				$values = "";
				$index = $appSession->getTool()->indexOf( $conditions[$i], "v:");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($conditions[$i], $index + 2);
				}
				
				$index = $appSession->getTool()->indexOf($values, ";");
				if($index != -1)
				{
					$values = $appSession->getTool()->substring($values, 0, $index);
				}
				
				$arr = $appSession->getTool()->split($values, "~");
				$values = "";
				for($j=0; $j<count($arr); $j++)
				{
					if($values != "")
					{
						$values = $values." or ";
					}
					$values = $values." resource_id='".$arr[$i]."'";
				}
				
				$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND ((".$values.") OR resource_id IN(select d3.user_id from hr_department_employee d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN res_user_company d3 ON(d2.id = d3.employee_id) where  d1.status =0 AND d2.status =0 AND d3.status=0 AND (".$appSession->getTool()->replace($values, "resource_id", "d1.department_id").")) ) AND type='".$table_name."')", $sql);
			}
		}
	}else{
		$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND (resource_id='".$appSession->getUserInfo()->getId()."' or resource_id='".$appSession->getUserInfo()->getCompanyId()."') AND type='".$table_name."')", $sql);
	}
	$sql = str_replace("{company}", "'".$appSession->getUserInfo()->getCompanyId()."'", $sql);
	$sql = str_replace("{root_company}", "'".$appSession->getConfig()->getProperty("root_company_id")."'", $sql);
	$arr = explode(',', $appSession->getConfig()->getProperty("child_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{child_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("parent_company"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}
	$sql = str_replace("{parent_company}", $c_company_id, $sql);
	
	$arr = explode(',', $appSession->getConfig()->getProperty("company_ids"));
	$c_company_id = "";
	for($n =0; $n<count($arr); $n++)
	{
		if($arr[$n] != "")
		{
			if($c_company_id != "")
			{
				$c_company_id = $c_company_id.", ";
			}
			$c_company_id = $c_company_id."'".$arr[$n]."'";
		}
		
	}

	
	$sql = str_replace("{companies}", $c_company_id, $sql);
	
	if($sort == ""&& count($lines)>0)
	{
		$sort = $lines[0][5];
	}
	$searchs = [];
	for($c =0; $c<count($columns); $c++)
	{
		if($columns[$c][1] != ""  && $columns[$c][2] == "")
		{
			$searchs[count($searchs)] = str_replace("__", ".", $columns[$c][1]);
		}
	}
	
	$search = trim($search);
	if(count($searchs)>0 && $search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
	}
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	
	if($sort != "")
	{
		$sql = $sql." ORDER BY ".$sort;
	}
	
	
	$msg->add("query", $sql);
	
	$dt_category = $appSession->getTier()->getTable($msg);
	

	?>

			
	<div class="row  padding-small">
		<div class="col m12" >
		<?php
		$arr = findLayout($layouts, $layout_id, "table");
		$find_model_id = "";
		$find_layout_id = "";
		if(count($arr)>0)
		{
			$find_model_id = $arr[0];
			$find_layout_id = $arr[1];
		}
		$child_view_type = "table";
		$child_sql = "";
		$child_columns = [];
		$child_table_name = "";	
		$child_card_columns = 3;
		$child_table_caption = $table_caption;
		
		if($find_model_id != "")
		{
			
			$child_id = "";
			$child_data_id = "";
			
			for($i =0; $i<count($models); $i++)
			{
				if($models[$i][4] == $find_model_id)
				{
					if($models[$i][3] == "columns")
					{
						$columns_model_id = $models[$i][0];
					}else if($models[$i][3] == "rel_id")
					{
						$child_id = findValue($values, $models[$i][0]);
					}else if($models[$i][3] == "src" || $models[$i][2] == "data")
					{
						$child_data_id = findValue($values, $models[$i][0]);
						
					}else if($models[$i][3] == "view_type")
					{
						$child_view_type = findValue($values, $models[$i][0]);
						
					}else if($models[$i][3] == "table_caption")
					{
						$child_table_caption = findValue($values, $models[$i][0]);
						
					}
					else if($models[$i][3] == "card_columns")
					{
						$sValue = findValue($values, $models[$i][0]);
						if($sValue != "")
						{
							$child_card_columns = $appSession->getTool()->toInt($sValue);
						}
						
					}
				}
							
			}
			
			$condition = "";
			$group_by = "";
			$order_by = "";
			$sort = "";
			$sql = "SELECT condition, group_by, order_by FROM ir_data WHERE id ='".$child_data_id."' AND status =0";
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
			$sql = "SELECT name, type, data FROM parameter WHERE status =0 AND rel_id='".$child_data_id."' ORDER BY create_date ASC";
			
			$msg->add("query", $sql);
			$result_data_sql = $appSession->getTier()->getArray($msg);
			$numrows_data_sql = count($result_data_sql);
			$sql_params = [];
			for($m =0; $m<$numrows_data_sql; $m++)
			{
				$row1 = $result_data_sql[$m];		
				$arr = [];
				$arr[0] = $row1[0];
				$arr[1] = $row1[1];
				$arr[2] = $row1[2];
				$sql_params[$m] = $arr;
				
			}

			$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.data, d1.relationship, d2.order_by FROM ir_data_line  d1 LEFT OUTER JOIN ir_data d2 ON(d1.data_id = d2.id) WHERE d1.data_id ='".$child_data_id."' AND d1.status =0";
			$child_lines = [];

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
				$arr[5] = $row1[5];
				$child_lines[$m] = $arr;
				
				if($child_table_name == "" && $row1[1] == "")
				{
					$child_table_name = $row1[2];
				}
				
			}
		
			$conditions = $appSession->getTier()->condition_array($condition);

			
			$child_sql = $appSession->getTier()->buildSQL($child_lines, "", $sql_params, $conditions);
			
			
			
			if($condition == "")
			{
				$child_sql = $child_sql." WHERE ".$child_table_name.".status=0";
			}else{
				$child_sql = $child_sql." AND ".$child_table_name.".status=0";
			}
			
			
			
			for($i =0; $i<count($models); $i++)
			{
				
				if($models[$i][1] == $columns_model_id)
				{
					$caption = "";
					$name = "";
					$type = "";
					$align = "";
					$forecolor = "";
					$backcolor = "";
					$width = "";
					$format = "";
					$total = "";
					for($j =0; $j<count($models); $j++)
					{
						if($models[$j][1] == $models[$i][0])
						{
							$value = findValue($values, $models[$j][0]);
							
							if($models[$j][3] == 'caption')
							{
								$caption = $value;
							}
							else if($models[$j][3] == 'name')
							{
								$name = $value;
							}else if($models[$j][3] == 'type')
							{
								$type = $value;
								if($type == "status")
								{
									$hasStatus = 1;
								}
							}else if($models[$j][3] == 'align')
							{
								$align = $value;
							}else if($models[$j][3] == 'forecolor')
							{
								$forecolor = $value;
							}else if($models[$j][3] == 'backcolor')
							{
								$backcolor = $value;
							}else if($models[$j][3] == 'width')
							{
								$width = $value;
							}else if($models[$j][3] == 'format')
							{
								$format = $value;
							}else if($models[$j][3] == 'total')
							{
								$total = $value;
							}
						}
					}
					$child_columns[count($child_columns)] = [$caption, $name, $type, $width, $align, $forecolor, $backcolor, $format, $total, $name, $models[$i][0]];
				}
			}
			
		}
		
		function printCard($appSession, $columns, $row, $layouts, $parent_id)
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
						echo "<div class=\"row padding-small\">";
							
							printCard($appSession,  $columns, $row, $layouts, $_id);
						echo "</div>";
					}else if($type == "col")
					{
						
						echo "<div class=\"col m".$value."\">";
							if($data != "")
							{
								$pos = strpos($data, ',');
								
								if ($pos !== false)
								{
									
									$module_model_id = substr($data, $pos + 1);
									
									for($c =0; $c<count($columns); $c++)
									{
										$column_id = $columns[$c][10];
										
										if($column_id == $module_model_id)
										{
											$caption = $columns[$c][0];
											$name = $columns[$c][1];
											$type = $columns[$c][3];
											$format = $columns[$c][7];
											$value = $row->getString($name);
											if($type == "time")
											{
												$arr = explode(' ', $value);
												if(count($arr)>1)
												{
													$value = $arr[1];
												}
											}else if($type == "datetime"  && $value != "")
											{
												if($value != "")
												{
													$value = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($value));
												}
											}else if($type == "date" && $value != "")
											{
												if($format != "")
												{
													$value = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($name), $format);
												}else{
													$value = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($value));
												}
											}else if($type == "percent" && $value != "")
											{
												$value = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($value)) ."%";
												
											}
											echo "<b>".$caption."</b>:".$value;
											
										}
									}
									
								}else{
									echo $data;
								}
							}
						echo "</div>";
					}
					
				}
			}
		}
		
		for($cc =0; $cc<$dt_category->getRowCount(); $cc++)
		{
			$_id = $dt_category->getString($cc, $table_name."__id");
			
			$sql = str_replace("{rel_id}", "'".$rel_id."'", $child_sql);
			$sql = str_replace("{category_id}", "'".$_id."'", $sql);
			$sql = str_replace("{user_id}", "'".$appSession->getUserInfo()->getId()."'", $sql);
			if($appSession->getTool()->indexOf($filters, "assigned") != -1)
			{
				$conditions = explode(',', $filters);
				$condition = "";
				for($i =0; $i<count($conditions); $i++)
				{
					if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
					{
						$values = "";
						$index = $appSession->getTool()->indexOf( $conditions[$i], "v:");
						if($index != -1)
						{
							$values = $appSession->getTool()->substring($conditions[$i], $index + 2);
						}
						
						$index = $appSession->getTool()->indexOf($values, ";");
						if($index != -1)
						{
							$values = $appSession->getTool()->substring($values, 0, $index);
						}
						
						$arr = $appSession->getTool()->split($values, "~");
						$values = "";
						for($j=0; $j<count($arr); $j++)
						{
							if($values != "")
							{
								$values = $values." or ";
							}
							$values = $values." resource_id='".$arr[$i]."'";
						}
						
						$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND ((".$values.") OR resource_id IN(select d3.user_id from hr_department_employee d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN res_user_company d3 ON(d2.id = d3.employee_id) where  d1.status =0 AND d2.status =0 AND d3.status=0 AND (".$appSession->getTool()->replace($values, "resource_id", "d1.department_id").")) ) AND type='".$table_name."')", $sql);
					}
				}
			}else{
				$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND (resource_id='".$appSession->getUserInfo()->getId()."' or resource_id='".$appSession->getUserInfo()->getCompanyId()."') AND type='".$table_name."')", $sql);
			}
			
			$sql = str_replace("{company}", "'".$appSession->getUserInfo()->getCompanyId()."'", $sql);
			if($filters != "")
			{
				if($appSession->getTool()->indexOf($filters, "assigned") != -1)
				{
					$arr = [];
					
					$conditions = explode(',', $filters);
					
					for($i =0; $i<count($conditions); $i++)
					{
						if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") == -1)
						{
							
							if($condition != "")
							{
								$condition = $condition.",";
							}
							$condition = $condition.$conditions[$i];
						}
					}
					$condition = $appSession->getTool()->replace($condition, ":", "=");
					
					
				}else
				{
					if($condition != "")
					{
						$condition = $condition.",";
					}
					$condition = $condition.$appSession->getTool()->replace($filters, ":", "=");
				}
				
			}
			$conditions = $appSession->getTier()->condition_array($condition);
			
			$msg->add("query", $sql);
			$result = $appSession->getTier()->getTable($msg);
			$numrows = $result->getRowCount();	
				
		?>
		<button class="collapsible">
		<?php
		$caption = "";
		for($c =0; $c<count($columns); $c++)
		{
			$name = $columns[$c][1];

			$value = $dt_category->getString($cc, $name);
			if($caption != "")
			{
				$caption = $caption.", ";
			}
			$caption = $caption.$value;
			echo $caption;
		}
		?>
		</button>
		<div class="collapsible-content">
			<div  id="<?php echo $id;?>">
			<?php
			if($child_view_type == "card")
			{
				
				$split_cols = 12/$child_card_columns;
				
				
	
			?>
			<div class="row">
			<?php
				
				$items = intval($numrows/$child_card_columns);
				if($items<($numrows * $child_card_columns))
				{
					$items = $items + 1;
				}
				$j =0;
				for($col=0; $col<$card_columns; $col++)
				{
					
				?>
				<div class="col m<?php echo $split_cols;?> padding-small">
					<?php
					$count =0;
					for($i=$j; $i<$numrows; $i++)
					{
						$row = $result->getRow($j);
						if($count>=$items)
						{
							break;
						}
						$j = $j + 1;
						$count = $count + 1;
						echo "&nbsp;";
				
						$_id = $row->getString($child_table_name."__id");
						$title = $child_table_caption;
						for($m =0; $m<$result->getColumnCount(); $m++)
						{
							if($appSession->getTool()->indexOf($title, '{') == -1)
							{
								break;
							}
							$column_name = $result->getColumns()[$m]->getName();
							$title = $appSession->getTool()->replace($title, "{".$column_name."}", $row->getString($column_name));
						}
						
					?>
					
					<div class="card shadow--1dp" style="width:100%">
						<div class="card-title card--border">
							<a href="javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '<?php echo $_id;?>', '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>')" ><i class="icon-arrow-right"></i><?php echo $title;?></a>
							
						 </div>
						 <div class="dropdown-hover card__menu">
						 <span><i class="icon-options-vertical"></i></span>
						 <div class="dropdown-content" style="right:0; text-align:left">
							<?php
							for($ccc =0; $ccc<$dt_category->getRowCount(); $ccc++)
							{
								$__id = $dt_category->getString($ccc, $table_name."__id");
								if($__id != $id)
								{
									if(count($columns)>0)
									{
							?>
							<a href="javascript:moveCategory('<?php echo $_id;?>', '<?php echo $__id;?>')"><?php echo  $dt_category->getString($ccc, $columns[0][1]);?></a>
							<?php
									}
								}
							}
							?>
						 </div>
						 </div>
						<?php printCard($appSession, $child_columns, $row, $layouts, $find_layout_id);?>
					</div>
					
					<?php
					}
					?>
				</div>
				<?php
				}
				?>
			</div>
			<?php
				
			}else{
				echo $child_view_type;
			}
			?>
			</div>
		</div>
		<?php
	
		}
		?>
		<script>
		var coll = document.getElementsByClassName("collapsible");
		var i;

		for (i = 0; i < coll.length; i++) {
		  coll[i].addEventListener("click", function() {
			this.classList.toggle("collapsible-active");
			var content = this.nextElementSibling;
			if (content.style.maxHeight){
			  content.style.maxHeight = null;
			} else {
			  content.style.maxHeight = content.scrollHeight + "px";
			} 
		  });
		}
		if(coll.length>0)
		{
			 coll[0].click();
		}

		function moveCategory(id, category_id)
		{
			<?php if($table_parent_id != "")
			{
			?>
			saveLineValue('<?php echo $child_table_name;?>', '<?php echo $table_parent_id;?>', '', id, category_id, function(status, message){
				if(status == 0)
				{
					if(message == "OK")
					{
						loadTable<?php echo $pageIndex;?>(<?php echo $p;?>, '<?php echo $sort;?>', '<?php echo $filters;?>');
					}
					
				}
			}
			);
			
			<?php
			}else
			{
				echo 'alert("Category Id is not support");';
			}
			?>
		}
		
		</script>
		</div>
	</div>
			
		
<script>
	
	
</script>
