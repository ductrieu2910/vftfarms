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
	
	
	if($group_by != "")
	{
		$sql= $sql. " GROUP BY ".$group_by;
	}
	if($sort != "")
	{
		$sql = $sql." ORDER BY ".$sort;
	}
	
	
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getTable($msg);
	$numrows = $result->getRowCount();	
	
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
	
	$split_cols = 12/$card_columns;
	
	?>

			
<div class="row">
	<?php
	$items = intval($numrows/$card_columns);
	if($items<($numrows * $card_columns))
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
			$_id = $row->getString($table_name."__id");
			if($count>=$items)
			{
				break;
			}
			$j = $j + 1;
			$count = $count + 1;
			echo "&nbsp;";
			
			$title = $table_caption;
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
		
		<div class=" card shadow--1dp" style="width:100%">
			<div class="card-title card--border">
				<a href="javascript:gt('<?php echo $cview;?>', '<?php echo $module_id;?>', 'form', '<?php echo $_id;?>', '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>')" ><i class="icon-arrow-right"></i><?php echo $title;?></a>
			</div>
			<?php printCard($appSession, $columns, $row, $layouts, $layout_id);?>
		</div>
		
		<?php
		}
		?>
	</div>
	<?php
	}
	?>
</div>
			
		

