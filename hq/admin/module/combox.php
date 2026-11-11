<?php

require_once(ABSPATH.'config.php' );


$sql = "SELECT d1.id, d1.parent_id, d2.name, d1.model_id FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) WHERE d1.status =0 AND d1.module_id='".$module_id."' AND d1.parent_id='' AND d2.name='".$model_name."'";



$result = $appSession->getTier()->getArray( $sql);
$numrows = count($result);	
$columns = [];
$table_name = "customer";


for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	
	$sql = "SELECT d1.id, d1.name, d2.name AS category_name, d2.id AS model_id FROM ir_model_attribute d1 LEFT OUTER JOIN ir_model d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND d1.model_id='".$row[3]."' AND d1.name='columns' ORDER BY d1.create_date ASC";
	
	
	$result_attr = $appSession->getTier()->getArray($sql);
	$numrows_attr = count($result_attr);	
	for($j=0; $j<$numrows_attr; $j++)
	{
		$row1 = $result_attr[$j];
		$sql = "SELECT d1.id, d1.parent_id, d2.name, d1.model_id FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) WHERE d1.status =0 AND d1.parent_id='".$row1[0]."' ORDER BY d1.create_date ASC";
		
		$result_sub = $appSession->getTier()->getArray($sql);
		$numrows_sub = count($result_sub);	
		for($k=0; $k<$numrows_sub; $k++)
		{
			$rowk = $result_sub[$k];
			$arr = [];
			$arr[0] = $rowk[0];
			$arr[1] = $rowk[1];
			$arr[2] = $rowk[2];
			$arr[3] = $rowk[3];
			$arr[4] = "";
			$arr[5] = "";
			$arr[6] = "";
			$columns[$k] = $arr;
		}
			
			
	}
}
	
$sql = "SELECT d3.name AS rename, d1.module_model_id, d4.name, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.model_attribute_id = d3.id) LEFT OUTER JOIN ir_model d4 ON(d3.category_id = d4.id) WHERE d1.status =0 AND d1.module_id='".$module_id."'";



$result_attribute_value = $appSession->getTier()->getArray($sql);
$numrows_attribute_value = count($result_attribute_value);	


$column = "";
$data_id = "";
$name = "";

for($i = 0; $i<$numrows_attribute_value; $i++)
{
	$row = $result_attribute_value[$i];
	$name = $row["name"];
	if($name == "data")
	{
		$data_id = $row["data"];
	}
}
if($data_id == "")
{
	echo "Data is not support";
}else
{
	$sql = "SELECT id, parent_id, name, data, relationship FROM ir_data_line WHERE data_id ='".$data_id."' AND status =0";
	

	$lines = [];
	$result_data_sql = $appSession->getTier()->getArray($sql);
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
	}
	$sql = $appSession->getTier()->buildSQL($lines, "");
	$sql = str_replace("{company}", "'".$appSession->getUserInfo()->getCompanyId()."'", $sql);
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
	

	if($id == "")
	{
		$sql = $sql." WHERE 1=0";
	}else
	{
		$sql = $sql." WHERE ".$table_name.".id='".$id."'";
	}
	
	$result = $appSession->getTier()->getArray($sql);
	$numrows = count($result);	
	
	for($c=0; $c<count($columns); $c++)
	{
		$column_id = $columns[$c][0];
		
		$ccaption = "";
		$name = "";
		for($a =0; $a<$numrows_attribute_value; $a++)
		{
			$row_a =  $result_attribute_value[$a];
			if($row_a[1] == $column_id)
			{
				if($row_a[0] == "caption")
				{
					$ccaption = $row_a[3];
				}else if($row_a[0] == "name")
				{
					$name = $row_a[3];
				}
				
			}
		}
		$columns[$c][4]= $ccaption;
		$columns[$c][5]= $name;
	}
		
	if($numrows>0)
	{
		$row = $result[0];
		for($c=0; $c<count($columns); $c++)
		{
			$column_id = $columns[$c][0];
			$cname = "";
			
			for($a =0; $a<$numrows_attribute_value; $a++)
			{
				$row_a =  pg_fetch_array($result_attribute_value, $a);
				if($row_a[1] == $column_id)
				{
					if($row_a[0] == "name")
					{
						$cname = $row_a[3];
					}
					
				}
			}
			$columns[$c][6]= $row[$cname];
		}
	}
	?>
	
	
						<div class="d-flex flex-column-fluid">
							<!--begin::Container-->
							<div class="container">
								<div class="row">
									<div class="col-md-12">
										<!--begin::Card-->
										<div class="card card-custom gutter-b example example-compact">
											<div class="card-header">
												<h3 class="card-title">Base Controls</h3>
												<div class="card-toolbar">
													<div class="example-tools justify-content-center">
														<span class="example-toggle" data-toggle="tooltip" title="View code"></span>
														<span class="example-copy" data-toggle="tooltip" title="Copy code"></span>
													</div>
												</div>
											</div>
											<!--begin::Form-->
											<form>
												<div class="card-body">
												
													<?php
													for($c =0; $c<count($columns); $c++)
													{
													?>
													<div class="form-group">
														<label><?php echo $columns[$c][4];?>
														<span class="text-danger">*</span></label>
														<input id="<?php echo $columns[$c][5];?>" type="text" class="form-control" value="<?php echo $columns[$c][6];?>"  />
														
													</div>
													<?php
													}
													
													?>
										
												<div class="card-footer">
													<button type="button" onclick="saveLine()" class="btn btn-primary mr-2">Save</button>
													<button type="button" class="btn btn-secondary" onclick="back()">Cancel</button>
												</div>
											</form>
											<!--end::Form-->
										</div>
										<!--end::Card-->
										
									</div>
		
								</div>
							</div>
							<!--end::Container-->
						</div>
						<!--end::Entry-->

	<script>
		function saveLine()
		{
			var arr =[
			<?php
			for($c =0; $c<count($columns); $c++)
			{
				if($c>0)
				{
					echo ", ";
				}
				echo "'".$columns[$c][5]."'";
			}
			?>
			];
			var params = "";
			
			for(var i=0; i<arr.length; i++)
			{
				var name = arr[i];
				var index = name.indexOf('__');
				
				if(index !=-1)
				{
					name = name.substring(index + 2);
					if(params != "")
					{
						params = params + "`";
					}
				
					params = params + name + "=" + document.getElementById(arr[i]).value;
				}
				
			}
			var _url = '<?php echo URL?>addons/module/action.php?ac=save&table_name=<?php echo $table_name;?>&id=<?php echo $id;?>';
			if(params != "")
			{
				_url = _url + "&params=" + encodeURIComponent(params);
			}
	
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					back();
				}
				
			}, true);
		}
		function back()
		{
			gt('<?php echo $module_id;?>', '', '');
		}
		
	</script>
	</div>
</div>
		
	</div>
</div>


	<?php
}

?>