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
	if($appSession->getTool()->indexOf($filters, "assigned") != -1)
	{
		$conditions = explode(',', $filters);
		$condition = "";
		$values = "";
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
			{
				
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
						$values = $values.",";
					}
					$values = $values."'".$arr[$j]."'";
				}
				
			}
		}
		
		$sql = str_replace("{user_id}", $values, $sql);
	}else{
		$sql = str_replace("{user_id}", "'".$appSession->getUserInfo()->getId()."'", $sql);
	}
	$is_filter_assign = false;
	if($appSession->getTool()->indexOf($filters, "assigned") != -1)
	{
		$conditions = explode(',', $filters);
		$condition = "";
		for($i =0; $i<count($conditions); $i++)
		{
			if($conditions[$i] != "" && $appSession->getTool()->indexOf($conditions[$i], "assigned") != -1)
			{
				$is_filter_assign = true;
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
					$values = $values." resource_id='".$arr[$j]."'";
				}
				
				$sql = str_replace("{assigned}", "(select rel_id from res_assign where status =0 AND ((".$values.") OR resource_id IN(select d3.user_id from hr_department_employee d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id) LEFT OUTER JOIN res_user_company d3 ON(d2.id = d3.employee_id) where  d1.status =0 AND d2.status =0 AND d3.status=0 AND (".$appSession->getTool()->replace($values, "resource_id", "d1.department_id").")) ) AND type='".$table_name."')", $sql);
			}
		}
	}else{
		$sql = str_replace("{assigned}", "select d1.rel_id from res_assign d1 left outer join res_assign_status d2 ON(d1.status_id = d2.id) where d1.status =0 AND (d1.resource_id='".$appSession->getUserInfo()->getId()."' or d1.resource_id='".$appSession->getUserInfo()->getCompanyId()."') AND d1.type='".$table_name."' AND (d1.status_id = '' OR d1.status_id IS NULL OR d2.hidden=0)", $sql);
		
		
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
	
	$item_count = 0;
	$page_count = 0;
	$start = 0;
	$end = 0;
	if($sql == "")
	{
		echo "Query is empty";
		exit();
	}
	
	$arr = $appSession->getTier()->paging($sql, $p, $ps, $sort);
	if(count($arr) <=1)
	{
		exit();
	}
	
	$sql = $arr[1];
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	$numrows = count($result);	
	if($numrows>0)
	{
		$row = $result[0];
		$item_count = $row[0];
	}
	
	$page_count = (int)($item_count / $ps);
	if ($item_count - ($page_count * $ps) > 0)
	{
		$page_count = $page_count + 1;
	}
	if($item_count>0)
	{
		$start = ($p * $ps) + 1;
	}
	$end = $p + 1;
	if((($p + 1) * $ps)<$item_count)
	{
		$end = ($p + 1) * $ps;
	}else
	{
		$end = $item_count;
	}
	$sql = $arr[0];
	
	
	$msg->add("query", $sql);
	
	$result = $appSession->getTier()->getTable($msg);
	$numrows = $result->getRowCount();	
	
	$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));
	
	$appSession->getLang()->load($appSession->getTier(), 'table', $appSession->getConfig()->getProperty("lang_id"));
	
	$attributes = [];
	
	for($c =0; $c<count($columns); $c++)
	{
		$type = $columns[$c][2];
		if($type == "attribute")
		{
			$ids = "";
			for($j=0; $j<$numrows; $j++)
			{
				if($ids != "")
				{
					$ids = $ids." OR ";
				}
				$ids = $ids." d1.rel_id='".$result->getString($j, $table_name."__id")."'";
			}
			$sql = "SELECT d1.rel_id, d3.code AS type_code, d2.code, d2.value, d1.description, d1.id FROM res_attribute_line d1 LEFT OUTER JOIN res_attribute d2 ON(d1.attribute_id= d2.id) LEFT OUTER JOIN res_attribute_type d3 ON(d2.type_id = d3.id) WHERE d1.status =0";
			if($ids != "")
			{
				$sql = $sql." AND (".$ids.")";
				$msg->add("query", $sql);
				
				$attributes = $appSession->getTier()->getArray($msg);
			}
			break;
		
		}
	}
	
	?>

			
	<div class="row  padding-small">
		<div class="col m12 responsive" >
			
			<table class="table">
				<thead>
					<tr class="text-left">
						<th  style="width: 20px">#
						</th>
						<th  style="width:20px">
							
							<input type="checkbox" class="check" value="1" name="[]<?php echo $pageIndex;?>" onclick="checkAll<?php echo $pageIndex;?>(this)" />
								
						</th>
						<?php
						if($selFunc != "")
						{
						?>
						<th  style="width:20px">
						<?php
						}
						?>
						<th  style="text-align:center;width: 80px"><?php echo $appSession->getLang()->find("action");?></th>
						<?php
						for($c =0; $c<count($columns); $c++)
						{
							$catpion = $columns[$c][0];
							$name = $columns[$c][1];
							$width = $columns[$c][3];
							$type = $columns[$c][2];
							if($name != "")
							{
								
								$column_type = "ASC";
								$column_sort = $appSession->getTool()->replace($columns[$c][9], "__", ".");
								if($appSession->getTool()->indexOf($sort, $column_sort." ASC") !=-1)
								{ 
								
									$column_type = "DESC";
								}
								
						?>
						<th style="text-align:center; <?php if($width != ""){?> width: <?php echo $width;?>px <?php }?>"><a href="javascript:loadSort<?php echo $pageIndex;?>('<?php echo $appSession->getTool()->replace($name, "__", ".");?> <?php echo $column_type;?>')"><?php echo $appSession->getLang()->find($catpion);?><?php
						if($appSession->getTool()->indexOf($sort, $column_sort." ASC") !=-1)
						{ 
						?>
						<i class="icon-arrow-down"></i>
						<?php
						}else if($appSession->getTool()->indexOf($sort, $column_sort." DESC") !=-1)
						{
						?>
						<i class="icon-arrow-up"></i>
						<?php
						}
						?></a></th>
						<?php
							}else
							{
								if($type == "status")
								{
								?>
								<th style="text-align:center;  width: 20px"><?php echo $appSession->getLang()->find("Status");?></th>
								<?php
								}
							}
						}
						?>
						<?php
						if($view_type == "document")
						{
						?>
						<th  style="width: 24px"></th>
						
						<?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = ($p * $ps) + 1;
					$hasTotal = 0;
					for($j =0; $j<$numrows; $j++)
					{
						
						$row = $result->getRow($j);
						$id = $row->getString($table_name."__id");
					?>
					<tr>
						<td style="width:20px">
						<?php echo ($no + $j);?>.
						</td>
						
						<td style="width:20px">
							<input type="checkbox" class="check" name="[]<?php echo $pageIndex;?>" value="<?php echo $id;?>" />
						</td>
						<?php
						if($selFunc != "")
						{
						?>
						<td>
						<a class="button" href="javascript:<?php echo $selFunc;?>('<?php echo $id;?>')">
								<i class="icon-plus"></i>
							</a></td>
						<?php
						}
						?>
						<td nowrap="nowrap">
							
							<?php if(canPer($perList, "view")){ ?>
							<a class="button" href="javascript:goto<?php echo $pageIndex;?>('<?php echo $id;?>')">
								<i class="icon-pencil"></i>
							</a>
							<?php
							}
							?>
							<?php if(canPer($perList, "delete")){ ?>
							<a class="button" href="javascript:del<?php echo $pageIndex;?>('<?php echo $id;?>')">
								<i class="icon-trash"></i>
							</a>
							<?php
							}
							?>
						</td>
						<?php
						
						for($c =0; $c<count($columns); $c++)
						{
							
							$catpion = $columns[$c][0];
							$name = $columns[$c][1];
							
							$type = $columns[$c][2];
							$width = $columns[$c][3];
							
							$align = $columns[$c][4];
							$forecolor = $columns[$c][5];
							$backcolor = $columns[$c][6];
							$format = $columns[$c][7];
							$default = $columns[$c][11];
							if($columns[$c][8] != "")
							{
								$hasTotal = 1;
							}
							if($name != "")
							{
								
							
							
						?>
						<td  style="<?php if($align != ""){?> ;text-align: <?php echo $align;?> <?php } ?> <?php if($forecolor != ""){?> ;color: <?php echo $forecolor;?> <?php } ?><?php if($forecolor != ""){?> ;background-color: <?php echo $backcolor;?> <?php } ?>;max-width:600px" >
							<?php 
							$name = $row->getString($name);
								if($type == "date" && $name != "")
								{
									
									
									
									if($format != "")
									{
										$name = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($name), $format);
									}else{
										$name = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($name));
									}
									
								}else if($type == "float" && $name != "")
								{
									$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name));
									if($align == "")
									{
										$align = "right";
									}
								}
								else if($type == "int" && $name != "")
								{
									if($align == "")
									{
										$align = "right";
									}
									$name = $appSession->getFormats()->getINT()->format($appSession->getTool()->toInt($name));
								}else if($type == "currency")
								{
									if($name == "")
									{
										$name = 0;
									}
									if($align == "")
									{
										$align = "right";
									}
									$currency_id = $appSession->getConfig()->getProperty("currency_id");
									for($c1 =0; $c1<count($columns); $c1++)
									{
										$name1 = $columns[$c1][1];
										
										if($name1 == "currency_id")
										{
											$currency_id = $row->getString($name1);
											break;
										}
									}
									$name = $appSession->getCurrency()->format($currency_id, $appSession->getTool()->toDouble($name));
								}else if($type == "percent" && $name != "")
								{
									$name = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($name)) ."%";
									if($align == "")
									{
										$align = "right";
									}
								}else if($type == "assigned")
								{
									$create_uid = $row->getString($table_name."__create_uid");
									if($create_uid == "" || $is_filter_assign == false)
									{
										$create_uid = $appSession->getUserInfo()->getId();
									}
									$sql = "SELECT d2.name, d3.name, d4.name, d1.status_date, d1.id FROM res_assign d1 LEFT OUTER JOIN res_user d2 ON(d1.resource_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d1.resource_id = d3.id) LEFT OUTER JOIN res_assign_status d4 ON(d1.status_id = d4.id) WHERE d1.rel_id='".$name."' AND d1.status =0 AND(d1.create_uid='".$create_uid."' OR d1.resource_id='".$create_uid."' OR d1.resource_id='".$appSession->getUserInfo()->getCompanyId()."') ORDER BY d1.create_date ASC";
									
									$name = "";
									$msg->add("query", $sql);
									$assignedList = $appSession->getTier()->getArray($msg);
									for($n =0; $n<count($assignedList); $n++)
									{
										$sname = $assignedList[$n][0];
										if($sname == "")
										{
											$sname = $assignedList[$n][1];
										}
										
										
										$status = $assignedList[$n][2];
		
										if($status != "")
										{
											$status_date = $assignedList[$n][3];
											if($status_date != "")
											{
												$status_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($status_date));
											}
											
											$sname = $sname." (".$status."-".$status_date.")";
										}
		
										if($sname != "")
										{
											$sname = '<a href="javascript:viewAssigned(\''.$assignedList[$n][4].'\')">'.$sname.'</a>';
											
											if($name != "")
											{
												$name = $name.", ";
											}
											$name = $name.$sname;
										}
									}
								}else if($type == "image")
								{
									$name = '<img src="'.URL.'document/?id='.$name.'" height="120">';
								}else if($type == "attribute")
								{
									
									for($a =0; $a<count($attributes); $a++)
									{
										if($attributes[$a][0] == $name && $attributes[$a][2] == $default)
										{
											
											if(strtolower($attributes[$a][1]) == "page")
											{
												$value = $attributes[$a][3];
												if($appSession->getTool()->indexOf($value, 'api/action') != -1)
												{
													$value = $value."&data=".$appSession->getTool()->urlEncode("rel_id=".$name."&line_id=".$attributes[$a][5]."&ac=value");
													$url = URL.$value;
													
													$appSession->getTool()->httpGet($url);
													$name = "";
													
												}else{
													$value = $appSession->getTool()->replace($value, "{rel_id}", $name);
													$url = URL.$value."/?ac=value&line_id=".$attributes[$a][5];
													if($appSession->getTool()->indexOf($value, '?') != -1)
													{
														$url = URL.$value."&ac=value&line_id=".$attributes[$a][5];
													}
													
													$appSession->getTool()->httpGet($url);
													$name = "";
												}
												
											}else{
												$name = $attributes[$a][4];
											}
											
										}
									}
								}
								echo $name;
							?>
						</td>
						<?php
							}
							else{
								if($type == "status")
								{
									$sql = "SELECT d2.name FROM ir_module_status_log d1 LEFT OUTER JOIN ir_module_status d2 ON(d1.status_id = d2.id) WHERE d1.status =0 AND d1.rel_id='".$id."'";
									$status_name = $appSession->getTier()->getValue($sql);
									
								?>
								<td  style="text-align:left">
									<?php echo $status_name;?>
								</td>
								<?php
								}
							}
						}
						?>
						<?php
						if($view_type == "document")
						{
						?>
						<td><a href="<?php echo URL;?>document/?id=<?php echo $id;?>" target="_blank"><i class="icon-cloud-download"></i></a></td>
						
						<?php
						}
						?>
						
					</tr>
					<?php
					}
					?>
					<?php
					if($hasTotal == 1)
					{
					?>
					<tr>
						<td colspan="3"><b>Total</b></td>
						<?php
						
						for($c =0; $c<count($columns); $c++)
						{
							
							
							$name = $columns[$c][1];
							$type = $columns[$c][2];
							$width = $columns[$c][3];
							
							$align = $columns[$c][4];
							$format = $columns[$c][7];
							$format_value = "";
							if($name != "")
							{
								$total = $columns[$c][8];
								$total_amount = 0;
								if($total != "")
								{
									for($j =0; $j<$numrows; $j++)
									{
										
										$row = $result->getRow($j);
										$total_amount = $total_amount + $row->getFloat($name);
									}
								}
								if($type == "float" || $type == "currency" || $type == "integer" || $type == "percent")
								{
									if($align == "")
									{
										$align = "right";
									}
								}
								if($total == "AVG")
								{
									if($numrows>0)
									{
										$total_amount = $total_amount/$numrows;
									}
								}
								if($type == "float" || $type == "integer")
								{
									$format_value = $appSession->getFormats()->getDOUBLE()->format($total_amount);
								}else if($type == "currency")
								{
									$format_value = $appSession->getCurrency()->format($appSession->getConfig()->getProperty("currency_id"), $total_amount);
								}else if($type == "percent")
								{
									$format_value = $appSession->getFormats()->getDOUBLE()->format($total_amount)."%";
								}
							?>
							<td style="<?php if($align != ""){?> ;text-align: <?php echo $align;?> <?php } ?> <?php if($forecolor != ""){?> ;color: <?php echo $forecolor;?> <?php } ?><?php if($forecolor != ""){?> ;background-color: <?php echo $backcolor;?> <?php } ?>"><?php if($total != ""){ echo $format_value; }?></td>
							<?php
							}
						}
						?>
						<?php
						if($view_type == "document")
						{
						?>
						<td></td>
						<?php
						}
						?>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		
	</div>
</div>
			
<div class="row padding-small">
	<div class="col m2">
		<select id="editps" onchange="loadTable<?php echo $pageIndex;?>(0,'<?php echo $sort;?>', '<?php echo $filters;?>')" class="select" style="width: 75px;">
			<option value="10" <?php if($ps == 10){ echo " selected "; }?>>10</option>
			<option value="20" <?php if($ps == 20){ echo " selected "; }?>>20</option>
			<option value="30" <?php if($ps == 30){ echo " selected "; }?>>30</option>
			<option value="50" <?php if($ps == 50){ echo " selected "; }?>>50</option>
			<option value="100" <?php if($ps == 100){ echo " selected "; }?>>100</option>
		</select>
	</div>
	<div class="col m6">
		<div class="pagination">
		<?php
		if($p>5)
		{
		?>
		<a href="javascript:loadTable<?php echo $pageIndex;?>(0 ,'<?php echo $sort;?>', '<?php echo $filters;?>')">
			<i class="icon-control-start"></i>
		</a>
		<?php
		}
		?>
		<?php
		if($p>2)
		{
		?>
		<a href="javascript:loadTable<?php echo $pageIndex;?>(<?php echo ($p-1);?>, '<?php echo $sort;?>', '<?php echo $filters;?>')">
			<i class="icon-arrow-left"></i>
		</a>
		<?php
		}
		?>
		<?php 			
			if($page_count == 0)
			{
				$page_count = 1;
			}
			$start = $p - 3;
			if($start<0)
			{
				$start = 0;
			}
			$end = $start;
			$len = 0;
			for($i =$start; $i<$page_count; $i++)
			{
				$end = $end + 1;
				$len = $len + 1;
				if($len>10)
				{
					break;
					
				}
			?>
			<a href="javascript:loadTable<?php echo $pageIndex;?>(<?php echo $i;?>, '<?php echo $sort;?>', '<?php echo $filters;?>')" class="<?php if($i == $p){ ?> active <?php } ?> "><?php echo ($i + 1);?></a>
			<?php 
			} 
			?>
		<?php
		if($end<$page_count)
		{
		?>
		<a href="javascript:loadTable<?php echo $pageIndex;?>(<?php echo $p + 1;?>,'<?php echo $sort;?>', '<?php echo $filters;?>')">
			<i class="icon-arrow-right"></i>
		</a>
		<?php
		}
		?>
		<?php
		if(($end + 1)<$page_count)
		{
		?>
		<a href="javascript:loadTable<?php echo $pageIndex;?>(<?php echo $page_count - 1;?>, '<?php echo $sort;?>', '<?php echo $filters	;?>')">
			<i class="icon-control-end"></i>
		</a>
		<?php
		}
		?>
		</div>
	</div>
		
	<div class="col m4" style="text-align:right">
		<?php echo $appSession->getLang()->find("Displaying");?> <?php echo ($p * $ps) + $numrows ;?> <?php echo $appSession->getLang()->find("of");?> <?php echo ($item_count);?> <?php echo $appSession->getLang()->find("records");?>
	</div>
</div>
			
		
<script>

	
	
	function checkAll<?php echo $pageIndex;?>(theInput)
	{
		var checkboxes = document.getElementsByName(theInput.name);
		for (var i= 0; i<checkboxes.length; i++) {
			if(checkboxes[i].type == 'checkbox')
			{
				checkboxes[i].checked = theInput.checked;
			}
		}
	}
	function delAll<?php echo $pageIndex;?>()
	{
		var count = 0;
		var ids = "";
		var checkboxes = document.getElementsByName("[]<?php echo $pageIndex;?>");
		for (var i= 0; i<checkboxes.length; i++) {
			if(checkboxes[i].type == "checkbox" && checkboxes[i].checked && checkboxes[i].value != "")
			{
				if(ids != "")
				{
					ids += ",";
				}
				ids += checkboxes[i].value;
				count = count + 1;
			}
		}
		if(ids == "")
		{
			alert("<?php echo $appSession->getLang()->find('Please check to delete');?>");
			return;
		}
		var result = confirm(count + ". Want to delete?");
		if (!result) {
			return;
		}
		delLine<?php echo $pageIndex;?>(ids);
	}
	function selAll<?php echo $pageIndex;?>()
	{
		var count = 0;
		var ids = "";
		var checkboxes = document.getElementsByName("[]<?php echo $pageIndex;?>");
		for (var i= 0; i<checkboxes.length; i++) {
			if(checkboxes[i].type == "checkbox" && checkboxes[i].checked && checkboxes[i].value != "")
			{
				if(ids != "")
				{
					ids += ",";
				}
				ids += checkboxes[i].value;
				count = count + 1;
			}
		}
		if(ids == "")
		{
			alert("<?php echo $appSession->getLang()->find('Please check to select');?>");
			return;
		}
		
		<?php echo $selFunc;?>(ids);
	}
</script>
