<?php
$values = $appSession->getTool()->split($rel_ids, ',');
$ids = "";
for($i =0; $i<count($values); $i++)
{
	if($ids != "")
	{
		$ids = $ids." OR ";
	}
	$ids = $ids." d1.rel_id ='".$values[$i]."' OR d4.rel_id='".$values[$i]."'";
}
$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name, d1.value, d1.readonly, d2.code AS type_code FROM res_attribute d1 LEFT OUTER JOIN res_attribute_type d2 ON(d1.type_id = d2.id) LEFT OUTER JOIN res_attribute_category d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN res_attribute_category_rel d4 ON(d3.id = d4.category_id AND d4.status=0) WHERE d1.status =0";
if($ids != "")
{
	$sql = $sql." AND (".$ids.")";
}else{
	$sql = $sql." AND 1=0";
}

$sql = $sql." ORDER BY d1.sequence ASC";

$msg->add("query", $sql);
$dt_attribute = $appSession->getTier()->getTable($msg);
if($dt_attribute->getRowCount()>0)
{
	$sql = "SELECT id, attribute_id, description FROM res_attribute_line WHERE status =0 AND rel_id='".$id."'"; 
	$msg->add("query", $sql);
	$dt_attribute_line = $appSession->getTier()->getTable($msg);
	$sql = "";
	for($i=0; $i<$dt_attribute->getRowCount(); $i++)
	{
		$hasItem = false;
		$attribute_id = $dt_attribute->getString($i, "id");
		for($j=0; $j<$dt_attribute_line->getRowCount(); $j++)
		{
			
			if($attribute_id == $dt_attribute_line->getString($j, "attribute_id")){
				$hasItem = true;
				break;
			}
		}
		if($hasItem == false)
		{
			$builder = $appSession->getTier()->createBuilder("res_attribute_line");
			$builder->add("id", $appSession->getTool()->getId());
			$builder->add("rel_id", $id);
			$builder->add("attribute_id", $attribute_id);
			$builder->add("description", "");
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status", 0);
			$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
			if($sql != "")
			{
				$sql = $sql."; ";
			}
			$sql = $sql.$appSession->getTier()->getInsert($builder);
		}
	}
	if($sql != "")
	{
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		$sql = "SELECT id, attribute_id, description FROM res_attribute_line WHERE status =0 AND rel_id='".$id."'"; 
		$msg->add("query", $sql);
		$dt_attribute_line = $appSession->getTier()->getTable($msg);
		
	}
	function printResAttribue($appSession, $dt, $dt_attribute_line, $parent_id, $space, $pageIndex, $rel_id)
	{
		
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "parent_id") != $parent_id)
			{
				continue;
			}
			$pageIndex = $pageIndex + 1;	
			$id = $dt->getString($i, "id");
			$code = $dt->getString($i, "code");
			$name = $dt->getString($i, "name");
			$value = $dt->getString($i, "value");
			$att_parent_id = $dt->getString($i, "parent_id");
			$type_code = $dt->getString($i, "type_code");
			$line_id = "";
			$description = "";
			for($j =0; $j<$dt_attribute_line->getRowCount(); $j++)
			{
				if($id == $dt_attribute_line->getString($j, "attribute_id"))
				{
					$line_id = $dt_attribute_line->getString($j, "id");
					$description = $dt_attribute_line->getString($j, "description");
					break;
				}
			}
			
			
			?>
			<div class="row">
				<div class="col m12 padding" style="padding-left:<?php echo $space;?>px">
					<label class="label" style="padding-left:<?php echo $space;?>px"><?php echo $name;?>:</label>
				</div>
			</div>
			<?php 
			
			if($att_parent_id != "")
			{
				$type_code = strtolower($type_code);
			?>
			<div class="row">
				<div class="col m12 padding" style="padding-left:<?php echo $space;?>px">
				<?php 
				if($type_code == "document")
				{
					if($line_id == "")
					{
						$line_id = $appSession->getTool()->getId();
						$sql = "INSERT INTO res_attribute_line(id,create_uid, create_uid, create_date, write_date, status, company_id)VALUES('".$line_id."', '".$appSession->getConfig()->getProperty("user_id")."', '".$appSession->getConfig()->getProperty("user_id")."', '".$appSession->getTier()->getDateString().", ".$appSession->getTier()->getDateString().", 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$id."')";
						$msg->add("query", $sql);
						$appSession->getTier()->exec($msg);
					}
					
					echo $appSession->getTool()->httpGet(URL."admin/document/?rel_id=".$line_id."&pageIndex=".$pageIndex);
				}else if($type_code == "date")
				{
				?>
				<input type="date" value="<?php echo $description;?>" class="input" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})">
				<?php
				}else if($type_code == "datetime")
				{
				?>
				<input type="datetime-local" value="<?php echo $description;?>" class="input" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})">
				<?php
				}else if($type_code == "number")
				{
				?>
				<input type="number" value="<?php echo $description;?>" class="input" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})">
				<?php
				}else if($type_code == "html")
				{
				?>
				<textarea class="textarea" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})"><?php echo $description;?></textarea>
				<?php
				}else if($type_code == "textarea")
				{
				?>
				<textarea class="textarea" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})"><?php echo $description;?></textarea>
				<?php
				}else if($type_code == "option")
				{
					$items = $appSession->getTool()->split($value, ";");
					for($j=0; $j<count($items); $j++)
					{
						$index = $appSession->getTool()->indexOf($items[$j], '=');
						if($index != -1)
						{
							$k = substr($items[$j], 0, $index);
							$v = substr($items[$j],  $index + 1);
					?>
						<input type="radio" onchange="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})" <?php if($description == $k){ echo " checked "; }?> id="a<?php echo $line_id;?><?php echo $j;?>" name="a<?php echo $line_id;?>" value="<?php echo $k;?>">
						<label for="a<?php echo $line_id;?><?php echo $j;?>"><?php echo $v;?></label>
					<?php
						}
					}
				}
				else if($type_code == "page")
				{
					if($value =="")
					{
						echo "Empty Page";
					}else{
						if($line_id == "")
						{
							$line_id = $appSession->getTool()->getId();
							$sql = "INSERT INTO res_attribute_line(id,create_uid, create_uid, create_date, write_date, status, company_id)VALUES('".$line_id."', '".$appSession->getConfig()->getProperty("user_id")."', '".$appSession->getConfig()->getProperty("user_id")."', '".$appSession->getTier()->getDateString().", ".$appSession->getTier()->getDateString().", 0, '".$appSession->getConfig()->getProperty("company_id")."', '".$id."')";
							$msg->add("query", $sql);
							$appSession->getTier()->exec($msg);
						}
						if($appSession->getTool()->indexOf($value, 'api/action') != -1)
						{
							$value = $value."&data=".$appSession->getTool()->urlEncode("rel_id=".$rel_id."&line_id=".$line_id);
							$url = URL.$value;
							$appSession->getTool()->httpGet($url);
													
						}else{
							$value = $appSession->getTool()->replace($value, "{rel_id}", $rel_id);
							$url = URL.$value."/?line_id=".$line_id."&pageIndex=".$pageIndex;
							if($appSession->getTool()->indexOf($value, '?') != -1)
							{
								$url = URL.$value."&line_id=".$line_id."&pageIndex=".$pageIndex;
							}
							$appSession->getTool()->httpGet($url);
							
						}
						
					}
					
				}
				else
				{
				?>
				<input type="text" value="<?php echo $description;?>" class="input" onblur="saveLine('res_attribute_line', 'description', '', '<?php echo $line_id;?>', this, function(status, message){})">
				<?php
				}
				?>
				</div>
			</div>
			<?php
			}
			?>
			
		<?php
			printResAttribue($appSession, $dt, $dt_attribute_line , $id, $space + 10, $pageIndex, $rel_id);
		}	
	}
	printResAttribue($appSession, $dt_attribute, $dt_attribute_line , "", 0, 0, $id);
}
?>

