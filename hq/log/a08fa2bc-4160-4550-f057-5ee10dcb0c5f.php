<?php
$config = new AppConfig("");
$config->parseWith($data, '&');
$id = $config->getProperty("id");
$sql = "SELECT d1.receipt_no, d1.receipt_date, d1.description, d2.name AS company_name, d1.rel_id, d3.code AS classification_code, d3.name AS classification_name, d4.name AS location_name, d1.res_id, d5.id AS warehouse_id, d6.id AS stock_product_id, d5.name AS warehouse_name, d7.code AS product_code, d7.name AS product_name, d3.version, d3.issue_date, d3.verify_date, d9.code AS employee_code, d9.first_name, d9.last_name, d9.middle_name, d3.frequency, d10.name AS unit_name, d11.code AS asset_code, d11.name AS asset_name, d12.name AS asset_location FROM res_receipt d1 LEFT OUTER JOIN res_company d2 ON(d1.company_id = d2.id)  LEFT OUTER JOIN product_classification d3 ON(d1.res_id = d3.id) LEFT OUTER JOIN mrp_workcenter_line_flow d4 ON(d1.location_id = d4.id) LEFT OUTER JOIN stock_warehouse d5 ON(d1.rel_id = d5.id) LEFT OUTER JOIN stock_product d6 ON(d1.rel_id = d6.id) LEFT OUTER JOIN product d7 ON(d6.product_id = d7.id) LEFT OUTER JOIN res_user_company d8 ON(d1.create_uid = d8.user_id AND d8.status =0) LEFT OUTER JOIN hr_employee d9 ON(d8.employee_id = d9.id) LEFT OUTER JOIN product_unit d10 ON(d3.unit_id =d10.id) LEFT OUTER JOIN asset d11 ON(d1.rel_id = d11.id) LEFT OUTER JOIN asset_location d12 ON(d11.location_id = d12.id) WHERE d1.id='".$id."'";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);


function printAttribute($appSession, $dt, $dt_result, $parent_id, $space, $columns, $dt_meta, $dt_document, $url, $versionList, $dt_attribute_line, $dt_notification)
{
	for($i = 0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "parent_id") == $parent_id && $dt->getString($i, "category_id") == "")
		{
			$name = $dt->getString($i, "code")." ".  $dt->getString($i, "name");
			$description = $dt->getString($i, "description");
			
			$id = $dt->getString($i, "id");
			
			
		?>
		<tr>
			<td style="padding-left: <?php echo $space;?>"><?php echo $name;?></td>
			<?php
			for($m =0; $m<$versionList->getRowCount(); $m++)
			{
				$receipt_id = $versionList->getString($m, "rel_id");
				$input_value = "";
				$more_description = "";
				$condition = "";
				$result = "";
				$resultList =[];
				
				
				for($n =0; $n<$dt_attribute_line->getRowCount(); $n++)
				{
					if($dt_attribute_line->getString($n, "id") == $id && $dt_attribute_line->getString($n, "rel_id") == $receipt_id){
						$input_value = $dt_attribute_line->getString($n, "description");
						for($j =0; $j<$dt_result->getRowCount(); $j++)
						{
							if($dt_result->getString($j, "attribute_id") == $id && $dt_result->getString($j, "receipt_id") == $receipt_id && $dt_attribute_line->getString($n, "result_id") == $dt_result->getString($j, "line_id"))
							{
								$condition = $dt_result->getString($j, "condition");
								$result = $dt_result->getString($j, "result");
								$resultList[count($resultList)] = [$condition, $result];
							}
						}
					}
					if($dt_attribute_line->getString($n, "parent_id") == $id && $dt_attribute_line->getString($n, "rel_id") == $receipt_id){
						$ss = $dt_attribute_line->getString($n, "description");
						if($ss != "" && $appSession->getTool()->indexOf($ss, "=") == -1)
						{
							if($more_description !="")
							{
								$more_description = $more_description."; ";
							}
							$more_description = $more_description.$ss;
						}
						
						
					}
				}
				
				
				$solution = "";
				for($j =0; $j<$dt_meta->getRowCount(); $j++)
				{
					if($dt_meta->getString($j, "attribute_id") == $id && $dt_meta->getString($j, "create_uid") == $result && $dt_meta->getString($j, "receipt_id") == $receipt_id)
					{
						$solution = $dt_meta->getString($j, "description");
					}
				}
	
				if($result == "1"){
					for($j =0; $j<count($resultList); $j++)
					{
						 if($resultList[$j][1] == "0")
						 {
						 	$result == "0" ;
						 }
					}
				}
				if($result == "0"){
					
					
					$result = "Không đạt";
					for($j =0; $j<$dt_meta->getRowCount(); $j++)
					{

						if($dt_meta->getString($j, "attribute_id") == $id)
						{
							$solution = $dt_meta->getString($j, "description");
						}
					}
				}
				if($result == "1"){
					$result = "Đạt";
					
				}
				$arr = $appSession->getTool()->split($condition, "\t");
				if(count($arr)>1){
					$description = "";
				}
				$ext_descripiton = "";
				for($n = 0; $n<$dt->getRowCount(); $n++)
				{
					if($dt->getString($n, "parent_id") == $id && $dt->getString($n, "category_id") == "Value" )
					{
						$check_result_description = $dt->getString($i, "description");
						$index = $appSession->getTool()->indexOf($check_result_description, '=');
						if($index != -1)
						{
							$check_result_description = $appSession->getTool()->subString($check_result_description, 0, $index);
						}
						
						if($dt->getString($n, "value") == $check_result_description)
						{
							
							for($k =0; $k<$dt_notification->getRowCount(); $k++)
							{
							
								if($dt_notification->getString($k, "rel_id") == $dt->getString($n, "id"))
								{
									
									if($ext_descripiton != "")
									{
										$ext_descripiton = $ext_descripiton."</br>";
									}
									$ext_descripiton = $ext_descripiton." ".$dt_notification->getString($k, "description");
								}
							}
							
						}
						
					}
				}
				$documents = "";
				for($j =0; $j<$dt_document->getRowCount(); $j++)
				{
					if($dt_document->getString($j, "attribute_id") == $id && $dt_document->getString($j, "receipt_id") == $receipt_id)
					{
						if($documents != "")
						{
							$documents = $documents."; ";
						}
						$document_id = $dt_document->getString($j, "id");
						$path = $dt_document->getString($j, "path");
						$ext = $dt_document->getString($j, "ext");
						$documents = $documents.'<a href="'.$url.'document/view/?id='.$document_id.'&path='.$path.'&ext='.$ext.'" target="_blank">'.$dt_document->getString($j, "name").'.'.$ext.'</a>';
						
					}
				}
				$arr_input = $appSession->getTool()->split($input_value, ";");
				$input_value = "";
				for($k =0; $k<count($arr_input); $k++)
				{
					if($arr_input[$k] == "")
					{
						continue;
					}
					$index = $appSession->getTool()->indexOf($arr_input[$k], "&");
					if($index != -1)
					{
						continue;
					}
					$index = $appSession->getTool()->indexOf($arr_input[$k], "=");
					if($index != -1)
					{
						if($input_value != "")
						{
							$input_value = $input_value."; ";
						}
						$input_value = $input_value.$appSession->getTool()->subString($arr_input[$k], $index + 1);
					}else{
						$input_value = $input_value.$arr_input[$k];
					}
				}
				
				
			?>


			<td style="padding-left: <?php echo $space;?>"><?php echo $input_value;?></td>
			<?php
			$c = 0;
			for($j =0; $j<$columns; $j++)
			{
				$v1 = "";
				$v2 = "";
				$v3 = "";
				$logic = "";
				if(count($arr)>$c)
				{
					$v1 = $arr[$c];
					$c = $c + 1;
				}
				
				if(count($arr)>$c)
				{
					$v2 = $arr[$c];
					$c = $c + 1;
				}
				
				if(count($arr)>$c)
				{
					$v3 = $arr[$c];
					$c = $c + 1;
				}
				
				if(count($arr)>$c)
				{
					$logic = $arr[$c];
					$c = $c + 1;
				}
				if($v1 != "" && is_numeric($v1))
				{
					$v1 = $appSession->getFormats()->getDOUBLE()->format($appSession->getTool()->toDouble($v1));
				}
				
				
			?>
			<td style="text-align:center"><?php echo $v2;?> <?php echo $v3;?></td>
			<td style="text-align:center" ><?php echo $v1;?></td>
			<?php
			if($j<($columns -1))
			{
			?>
			<td  style="text-align:center"><?php echo $logic;?></td>
			<?php
			}
			}
			?>
			
			<td  style="text-align:center"><?php echo $result;?></td>
			<td  style="text-align:left"><?php echo $solution;?><?php if($solution != "" && $ext_descripiton != ""){ echo "</br>"; }?><?php echo $ext_descripiton;?></td>
			<td  style="text-align:center"><?php echo $more_description;?></td>
			<td  style="text-align:center"><?php echo $documents;?></td>
			<?php
			
			}
			?>
		</tr>
		<?php
		 printAttribute($appSession, $dt, $dt_result, $id, $space + 20, $columns, $dt_meta, $dt_document, $url, $versionList, $dt_attribute_line, $dt_notification);
		}
	}
}

if($dt->getRowCount()>0)
{
	$receipt_no = $dt->getString(0, "receipt_no");
	$receipt_date = $dt->getString(0, "receipt_date");
	$classification_name = $dt->getString(0, "classification_name");
	$classification_code = $dt->getString(0, "classification_code");
	
	$company_name = $dt->getString(0, "company_name");
	$location_name = $dt->getString(0, "location_name");
	$warehouse_name = $dt->getString(0, "warehouse_name");
	$product_code = $dt->getString(0, "product_code");
	$product_name = $dt->getString(0, "product_name");
	$employee_code = $dt->getString(0, "employee_code");
	$employee_name = $dt->getString(0, "last_name")." ".$dt->getString(0, "middle_name")." ".$dt->getString(0, "first_name");
	$frequency = $dt->getString(0, "frequency");
	$unit_name = $dt->getString(0, "unit_name");
	$asset_code = $dt->getString(0, "asset_code");
	$asset_name = $dt->getString(0, "asset_name");
	$asset_location = $dt->getString(0, "asset_location");

	$name = $product_code.". ".$product_name;
	if($warehouse_name != "")
	{
		$name = $warehouse_name;
	}
	if($asset_name != "")
	{
		$name = $asset_code.". ".$asset_name;
		if($asset_location != "")
		{
			$name = $name." (".$asset_location.")";
		}
	}
	if($location_name == "")
	{
		$location_name = $asset_location;
	}
	$version = $dt->getString(0, "version");
	$issue_date = $dt->getString(0, "issue_date");
	$verify_date = $dt->getString(0, "verify_date");
	if($issue_date != "")
	{
		$issue_date = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($issue_date), "DD/MM/YYYY");
	}
	if($verify_date != "")
	{
		$verify_date = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($verify_date), "DD/MM/YYYY");
	}
	
	if($receipt_date != "")
	{
		$receipt_date = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($receipt_date), "DD/MM/YYYY hh:mm");
	}
	$company_currency_id = $appSession->getConfig()->getProperty("currency_id");
	
	$rel_id = $dt->getString(0, "rel_id");
	$res_id = $dt->getString(0, "res_id");
	$stock_product_id = $dt->getString(0, "stock_product_id");
	$warehouse_id = $dt->getString(0, "warehouse_id");
	
	
	$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code, d.description, d1.category_id, d.rel_id";
	$sql= $sql." FROM res_attribute_line d LEFT OUTER JOIN res_attribute d1 ON(d.attribute_id = d1.id) LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
	$sql= $sql." WHERE d1.status =0 AND d.rel_id='".$id."'";
	$sql= $sql." ORDER BY d1.sequence ASC";
	
	$msg->add("query", $sql);
	$dt_attribute = $appSession->getTier()->getTable($msg);
	

	$sql = "SELECT d.id AS result_id, d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code, d.description, d1.category_id, d.rel_id";
	$sql= $sql." FROM res_attribute_line d LEFT OUTER JOIN res_attribute d1 ON(d.attribute_id = d1.id) LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
	$sql= $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
	$sql= $sql." WHERE d.status =0 AND d.rel_id='".$id."'";
	
	$msg->add("query", $sql);
	$dt_attribute_line = $appSession->getTier()->getTable($msg);

 
	
	$sql = "SELECT d2.attribute_id,  d1.condition, d1.result, '".$id."' AS receipt_id, d1.line_id";
	$sql = $sql." FROM res_attribute_line_result d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.line_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$id."'";
	$msg->add("query", $sql);
	$dt_result = $appSession->getTier()->getTable($msg);
	
	
	$sql = "SELECT d2.attribute_id,  d1.description, '".$id."' AS receipt_id";
	$sql = $sql." FROM res_meta d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$id."'";
	$msg->add("query", $sql);
	
	$dt_meta = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT d1.id,d1.path, d1.ext, d1.name, d2.attribute_id, '".$id."' AS receipt_id";
	$sql = $sql." FROM document d1 LEFt OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$id."'";
	$msg->add("query", $sql);
	$dt_document = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT d1.id, d1.rel_id, d1.type, d1.name, d1.description FROM res_status_notification d1 LEFT OUTER JOIN hr_employee d2 ON(d1.create_uid = d2.id) LEFT OUTER JOIN hr_line_department d3 ON(d1.status_id = d3.id) LEFT OUTER JOIN res_attribute d4 ON(d1.rel_id = d4.id) WHERE d4.rel_id='".$res_id."' AND d1.status =0";
	$sql = $sql." ORDER BY d1.create_date ASC";
	
	$msg->add("query", $sql);
	$dt_notification = $appSession->getTier()->getTable($msg);
	

	$columns = 0;
	for($j =0; $j<$dt_result->getRowCount(); $j++)
	{
		if($dt_result->getString($j, "condition") != "")
		{
			continue;
		}
		$arr = $appSession->getTool()->split($dt_result->getString($j, "condition"), "\t");
		if(count($arr)>1 && count($arr)>$columns)
		{
			$columns =  count($arr);
		}
		
	}
	
	
	$columns = $columns/4;
	if($columns<1)
	{
		$columns = 1;
	}
	$versionList =[$id];
	$version_id = $id;
	$running =true;
	while($running)
	{
		if($version_id == "")
		{
			break;
		}
		
		$sql = "SELECT create_uid FROM res_version WHERE rel_id='".$version_id."'";
		$msg->add("query", $sql);
		$version_id = $appSession->getTier()->getValue($msg);
		if($version_id != "")
		{
			for($i = 0; $i<count($versionList); $i++)
			{
				if($versionList[$i] == $version_id)
				{
					$running = false;
					break;
				}
			}
			$versionList[count($versionList)] = $version_id;
		}
		
		
	}
	
	$ids = "";
	for($i =0; $i<count($versionList); $i++)
	{
		if($ids != "")
		{
			$ids = $ids." OR ";
		}
		$ids = $ids." create_uid='".$versionList[$i]."' OR rel_id='".$versionList[$i]."'";
	}
	$sql = "SELECT rel_id, version FROM res_version WHERE status =0";
	if($ids != "")
	{
		$sql = $sql." AND (".$ids.")";
	}else{
		$sql = $sql." AND 1=0";
	}
	$sql = $sql." ORDER BY version DESC";
	$msg->add("query", $sql);

	$dt_version = $appSession->getTier()->getTable($msg);
	
	for($i =0; $i<count($versionList); $i++)
	{
		$hasItem = false;
		for($j =0; $j<$dt_version->getRowCount(); $j++)
		{
			if($dt_version->getString($j, "rel_id") == $versionList[$i])
			{
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			$dt_version ->addArray([$versionList[$i], "0"]);
		}
		
	}
	
	
	for($i =0; $i<$dt_version->getRowCount(); $i++)
	{
		if($dt_version->getString($i, "rel_id") == $id)
		{
			continue;
		}
		$sql = "SELECT d2.attribute_id,  d1.condition, d1.result, '".$dt_version->getString($i, "rel_id")."' AS receipt_id, d1.line_id";
		$sql = $sql." FROM res_attribute_line_result d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.line_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$dt_version->getString($i, "rel_id")."'";
		$msg->add("query", $sql);

		$dt = $appSession->getTier()->getTable($msg);
		for($j = 0; $j<$dt->getRowCount(); $j++)
		{
			$dt_result->addArray($dt->getDataRow($j));
		}
		
		
		$sql = "SELECT d2.attribute_id,  d1.description, '".$dt_version->getString($i, "rel_id")."' AS receipt_id";
		$sql = $sql." FROM res_meta d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$dt_version->getString($i, "rel_id")."'";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($j = 0; $j<$dt->getRowCount(); $j++)
		{
			$dt_meta->addArray($dt->getDataRow($j));
		}
		
		$sql = "SELECT d1.id,d1.path, d1.ext, d1.name, d2.attribute_id, '".$dt_version->getString($i, "rel_id")."' AS receipt_id";
		$sql = $sql." FROM document d1 LEFt OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d2.rel_id='".$dt_version->getString($i, "rel_id")."'";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($j = 0; $j<$dt->getRowCount(); $j++)
		{
			$dt_document->addArray($dt->getDataRow($j));
		}
		
		$sql = "SELECT d.id AS result_id, d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code, d.description, d1.category_id, d.rel_id";
		$sql= $sql." FROM res_attribute_line d LEFT OUTER JOIN res_attribute d1 ON(d.attribute_id = d1.id) LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id)";
		$sql= $sql." LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id)";
		$sql= $sql." LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0)";
		$sql= $sql." WHERE d1.status =0 AND d.rel_id='".$dt_version->getString($i, "rel_id")."'";
		$sql= $sql." ORDER BY d1.sequence ASC";
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($j = 0; $j<$dt->getRowCount(); $j++)
		{
			$dt_attribute_line->addArray($dt->getDataRow($j));
		}
		
	
	}
	
	
	$packageList = [];
	

	$description = "";
	if($stock_product_id != "")
	{
		$sql = "SELECT d1.quantity, d1.barcode AS barcode1, d2.code AS product_code, d2.name AS product_name, d4.name AS unit_name, d14.barcode AS barcode2 , d15.barcode AS barcode3, d16.barcode AS barcode4, d17.barcode AS barcode5, d18.barcode AS barcode6 , d19.barcode AS barcode7, d20.barcode AS barcode8, d21.barcode AS barcode9, d22.barcode AS barcode10, d23.barcode AS barcode11, d24.barcode AS barcode12, d25.barcode AS barcode13, d26.barcode AS barcode14 , d27.barcode AS barcode15, d28.barcode AS barcode16, d29.barcode AS barcode17, d1.stock_id AS stock_id1, d14.stock_id AS stock_id2, d15.stock_id AS stock_id3, d16.stock_id AS stock_id4, d17.stock_id AS stock_id5, d18.stock_id AS stock_id6, d19.stock_id AS stock_id7, d20.stock_id AS stock_id8, d21.stock_id AS stock_id9,d22.stock_id AS stock_id10 ,d23.stock_id AS stock_id11, d24.stock_id AS stock_id12 FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d14 ON(d1.rel_id = d14.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d15 ON(d14.rel_id = d15.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d16 ON(d15.rel_id = d16.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d17 ON(d16.rel_id = d17.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d18 ON(d17.rel_id = d18.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d19 ON(d18.rel_id = d19.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d20 ON(d19.rel_id = d20.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d21 ON(d20.rel_id = d21.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d22 ON(d21.rel_id = d22.id)";
		$sql = $sql." LEFT OUTER JOIN stock_product d23 ON(d22.rel_id = d23.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d24 ON(d23.rel_id = d24.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d25 ON(d24.rel_id = d25.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d26 ON(d25.rel_id = d26.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d27 ON(d26.rel_id = d27.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d28 ON(d27.rel_id = d28.id)";
		$sql = $sql."LEFT OUTER JOIN stock_product d29 ON(d28.rel_id = d29.id)";
		$sql = $sql." WHERE d1.id ='".$stock_product_id."'";
		$msg->add("query", $sql);
		
		$dt_stock = $appSession->getTier()->getTable($msg);
		if($dt_stock->getRowCount()>0)
		{
			
			$description = $dt_stock->getString(0, "barcode1");
			if($dt_stock->getString(0, "barcode2") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode2");
			}
			if($dt_stock->getString(0, "barcode3") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode3");
			}
			if($dt_stock->getString(0, "barcode4") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode4");
			}
			if($dt_stock->getString(0, "barcode5") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode5");
			}
			if($dt_stock->getString(0, "barcode6") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode6");
			}
			if($dt_stock->getString(0, "barcode7") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode7");
			}
			if($dt_stock->getString(0, "barcode8") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}


				$description = $description.$dt_stock->getString(0, "barcode8");
			}
			if($dt_stock->getString(0, "barcode9") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode9");
			}
			if($dt_stock->getString(0, "barcode10") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode10");
			}
			if($dt_stock->getString(0, "barcode11") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode11");
			}
			if($dt_stock->getString(0, "barcode12") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode12");
			}
			if($dt_stock->getString(0, "barcode13") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					

				}
				$description = $description.$dt_stock->getString(0, "barcode13");
			}
			if($dt_stock->getString(0, "barcode14") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode14");
			}
			if($dt_stock->getString(0, "barcode15") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode15");
			}

			if($dt_stock->getString(0, "barcode16") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode16");
			}
			if($dt_stock->getString(0, "barcode17") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_stock->getString(0, "barcode17");
			}
			$description = 'Stock: <a href="'.URL.'track/?id='.$stock_product_id.'" target="_blank">'.$description.'</a>';
			$stock_ids = "";
			if($dt_stock->getString(0, "stock_id1") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id1")."'";
			}
			if($dt_stock->getString(0, "stock_id2") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id2")."'";
			}
			if($dt_stock->getString(0, "stock_id3") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id3")."'";
			}
			if($dt_stock->getString(0, "stock_id4") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id4")."'";
			}
			if($dt_stock->getString(0, "stock_id5") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id5")."'";
			}
			if($dt_stock->getString(0, "stock_id5") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id5")."'";
			}
			if($dt_stock->getString(0, "stock_id6") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id6")."'";
			}
			if($dt_stock->getString(0, "stock_id7") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id7")."'";
			}
			if($dt_stock->getString(0, "stock_id8") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id8")."'";
			}
			if($dt_stock->getString(0, "stock_id8") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id8")."'";
			}
			if($dt_stock->getString(0, "stock_id10") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id10")."'";
			}
			if($dt_stock->getString(0, "stock_id1") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id1")."'";
			}
			if($dt_stock->getString(0, "stock_id12") != "")
			{
				if($stock_ids != "")
				{
					$stock_ids = $stock_ids." OR ";
					
				}
				$stock_ids = $stock_ids." d2.rel_id ='".$dt_stock->getString(0, "stock_id12")."'";
			}
			
			if($stock_ids != "")
			{
				$sql = "SELECT d1.order_no,  d1.manufacture_date, d1.packing_date, d1.expiration_date, d1.lot_number, d1.lot_code FROM stock_package_product d1 LEFT OUTER JOIN stock_package d2 ON(d1.package_id = d2.id) WHERE d1.status =0 AND (".$stock_ids.")";
				$msg->add("query", $sql);
				$packageList = $appSession->getTier()->getArray($msg);
				
			}
		}
	}
	if($warehouse_id != "")
	{
		$sql = "SELECT d1.name AS name1, d2.name AS name2, d3.name AS name3, d4.name AS name4 FROM stock_warehouse d1 LEFT OUTER JOIN stock_warehouse d2 ON(d1.parent_id = d2.id) LEFT OUTER JOIN stock_warehouse d3 ON(d2.parent_id = d3.id) LEFT OUTER JOIN stock_warehouse d4 ON(d3.parent_id = d4.id) WHERE d1.id ='".$warehouse_id."'";
		$msg->add("query", $sql);
		$dt_warehouse = $appSession->getTier()->getTable($msg);
		if($dt_warehouse->getRowCount()>0)
		{
			$description = $dt_warehouse->getString(0, "name1");
			if($dt_warehouse->getString(0, "name2") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_warehouse->getString(0, "name2");
			}
			if($dt_warehouse->getString(0, "name3") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_warehouse->getString(0, "name3");
			}
			if($dt_warehouse->getString(0, "name4") != "")
			{
				if($description != "")
				{
					$description = $description."/";
					
				}
				$description = $description.$dt_warehouse->getString(0, "name4");
			}
			$description = "Warehouse: ".$description;
		}
	}
	

	
?>

<table  border="0" width="100%">
        <tr >
          <td height="21" align="center"><table width="100%" border="0">
            <tr>
              <td width="17%" align="center" valign="top"><img src="<?php echo URL;?>assets/img/logo.png"/><br>
              <b><?php echo $company_name;?></b></td>
              <td width="29%" valign="top"><?php echo $classification_name;?><br><?php echo $name;?></td>
              <td width="41%" valign="top">
	
		  #: <b><?php echo $receipt_no;?></b><br>
		   Ngày: <b><?php echo $receipt_date;?></b>  <br>

			 Vị trí: <b><?php echo $location_name;?></b><br> 
			  Nhân viên: <b><?php echo $employee_code;?>. <?php echo $employee_name;?></b><br> 
			 <?php echo $description;?>
			 <?php
			 if(count($packageList)>0)
			 {
			 	$manufacture_date =   $packageList[0][1];
				$packing_date =   $packageList[0][2];
				$expiration_date =   $packageList[0][3];
				
				
			 ?>
			 <br>
			 Thông tin Packing<br>
			 Order No: <?php echo $packageList[0][0];?><br>
			 Packing Date: <?php echo $packing_date;?><br>
			 Manufacture Date: <?php echo $manufacture_date;?><br>
			 Expiration Date: <?php echo $expiration_date;?><br>
			 Lot Number: <?php echo $packageList[0][4];?><br>
			 Lot Code: <?php echo $packageList[0][5];?><br>
			 <?php
			 }
			 ?>
			 </td>
              <td width="13%" valign="top">Mã số: <?php echo $classification_code;?><br>
			  Phiên bản: <?php echo $version;?><br>
			  Ngày ban hành: <?php echo $issue_date;?><br>
			  Ngày cập nhật: <?php echo $verify_date;?>
			  <?php
			  if($frequency != "")
			  {
			  ?>
			  <br>
			  Tần suất kiểm tra: <?php echo $frequency;?> lần/<?php echo $unit_name;?>
			  <?php
			  }
			  ?>			  </td>
            </tr>
            
          </table>
             

		  
	      </td>
        </tr>
        
		
</table> 
<br>
  <table width="100%" border="0">
    
    <tr>
      <td>
	  <table border="1" cellpadding="0" cellspacing="0">
	  	<tr>	
			<td width="250" align="center" rowspan="2"><strong>Chỉ tiêu </strong></td>
			<?php
			for($m = 0; $m<$dt_version->getRowCount(); $m++)
			{
				$colspan = 4;
			?>
				<td width="80" align="center"><strong>Số liệu kiểm tra </strong></td>
				<?php
				for($j =0; $j<$columns; $j++)
				{
					$colspan = $colspan + 2;
				?>
					<td width="80" align="center"><strong>Điều kiện</strong></td>
					<td width="80" align="center"><strong>Tỷ lệ đánh giá </strong></td>
					<?php 
					if($columns>1 && $j<($columns-1))
					{
						$colspan = $colspan + 1;
					?>
					<td width="80" align="center"><strong>Kết hợp</strong></td>
					<?php
					}
					?>
				<?php
				}
				?>
				<td width="100" align="center"><strong>Kết quả</strong></td>
				<td width="200" align="center"><strong>Giải pháp</strong></td>
				<td width="200" align="center"><strong>Ghi chú </strong></td>
				<td width="100" align="center"><strong>Đính kèm</strong></td>
			<?php
			}
			?>
		</tr>
		<tr>	
			<?php
			
			for($m = 0; $m<$dt_version->getRowCount(); $m++)
			{
				$colspan = 4;
				for($j =0; $j<$columns; $j++)
				{
					$colspan = $colspan + 2;
					if($columns>1 && $j<($columns-1))
					{
						$colspan = $colspan + 1;
					}
				}
			?>
			<td colspan="<?php echo $colspan; ?>" align="center" style="text-align:center"><strong><?php echo $dt_version->getString($m, "version");?></strong></td>
			<?php
			}
			?>
		</tr>
		 <?php
	  printAttribute($appSession, $dt_attribute, $dt_result, "", 0, $columns, $dt_meta, $dt_document, URL, $dt_version, $dt_attribute_line, $dt_notification);
	  ?>
	  </table>
	 
	  </td>
    </tr>
  </table>
	
  <?php
  $sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d2.code, d2.name FROM mrp_workorder d1 LEFT OUTER JOIN mrp_production d2 ON(d1.production_id=d2.id) WHERE d1.status =0 AND d1.rel_id ='".$id."' ORDER BY d1.receipt_date ASC";
 $msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0)
	{
	
	?>
	Quy trình thực hiện tiếp theo:
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$workorder_id = $dt->getString($i, "id");
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		$production_code = $dt->getString($i, "code");
		$production_name = $dt->getString($i, "name");
		
	?>
	<br><a href="<?php echo URL;?>track/wo/?id=<?php echo $workorder_id;?>" target="blank">#<?php echo $receipt_no;?> Ngày: <?php echo $receipt_date;?> Quy trình: <?php echo $production_code;?>. <?php echo $production_name;?><a>
	<?php
	}
	?>
	<?php
	}
}
?>
