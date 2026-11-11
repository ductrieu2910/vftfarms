<?php
$msg = $appSession->getTier()->createMessage();
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}

$line_id = "";
if(isset($_REQUEST['line_id']))
{
	$line_id = $_REQUEST['line_id'];
}
$readonly = "";
if(isset($_REQUEST['readonly']))
{
	$readonly = $_REQUEST['readonly'];
}
if($ac == "view")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT id, name, type FROM res_meta WHERE rel_id='".$rel_id."' AND status =0";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$so_thua = "";
	$so_to = "";
	$phuong = "";
	$quan = "";
	
	$so_thua_id = "";
	$so_to_id = "";
	$phuong_id = "";
	$quan_id = "";
	for($i=0; $i<count($values); $i++)
	{
		if($values[$i][2] == "SO_THUA")
		{
			$so_thua = $values[$i][1];
			$so_thua_id = $values[$i][0];
		}else if($values[$i][2] == "SO_TO")
		{
			$so_to = $values[$i][1];
			$so_to_id = $values[$i][0];
		}else if($values[$i][2] == "PHUONG")
		{
			$phuong = $values[$i][1];
			$phuong_id = $values[$i][0];
		}else if($values[$i][2] == "QUAN")
		{
			$quan = $values[$i][1];
			$quan_id = $values[$i][0];
		}
	}
	$sql= "";
	if($so_thua_id == "")
	{
		$so_thua_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_meta");
		$builder->add("id", $so_thua_id);
		$builder->add("rel_id", $rel_id);
		$builder->add("name", "");
		$builder->add("type", "SO_THUA");
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
	if($so_to_id == "")
	{
		$so_to_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_meta");
		$builder->add("id", $so_to_id);
		$builder->add("rel_id", $rel_id);
		$builder->add("name", "");
		$builder->add("type", "SO_TO");
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
	if($phuong_id == "")
	{
		$phuong_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_meta");
		$builder->add("id", $phuong_id);
		$builder->add("rel_id", $rel_id);
		$builder->add("name", "");
		$builder->add("type", "PHUONG");
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
	if($quan_id == "")
	{
		$quan_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_meta");
		$builder->add("id", $quan_id);
		$builder->add("rel_id", $rel_id);
		$builder->add("name", "");
		$builder->add("type", "QUAN");
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
	if($sql != "")
	{
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
	}
?>
	<?php if($readonly == "1"){ ?>
	<b>Thửa đất số</b>:&nbsp;<?php echo $so_thua;?> &nbsp;&nbsp;&nbsp;<b>Tờ bản đồ số</b>:&nbsp;<?php echo $so_to;?>&nbsp;&nbsp;&nbsp;<b>Phường</b>:&nbsp;<?php echo $phuong;?>&nbsp;&nbsp;&nbsp;<b>Quận</b>: <?php echo $quan;?>
	<?php }else{ ?>
	<div class="row">
		<div class="col m5 padding"><b>Thửa đất số</b>: <input type="text" value="<?php echo $so_thua;?>" class="input" onblur="saveLine('res_meta', 'name', '', '<?php echo $so_thua_id;?>', this, function(status, message){ loadDouble();})"></div>
		
		<div class="col m3 padding"><b> Tờ bản đồ số</b>:<input type="number" value="<?php echo $so_to;?>" class="input" onblur="saveLine('res_meta', 'name', '', '<?php echo $so_to_id;?>', this, function(status, message){ loadDouble();})"></div>
	
		<div class="col m2 padding"><b> Phường</b>: <input type="text" class="input" value="<?php echo $phuong;?>" onblur="saveLine('res_meta', 'name', '', '<?php echo $phuong_id;?>', this, function(status, message){ loadDouble();})"></div>
		
		<div class="col m2 padding"><b> Quận</b>: <input type="text" class="input" value="<?php echo $quan;?>" onblur="saveLine('res_meta', 'name', '', '<?php echo $quan_id;?>', this, function(status, message){ loadDouble();})"></div>
	
	</div>
	<?php
	}
	?>
	<div class="row">
		<div class="col m12 padding" id="pnSoToThua"></div>
	</div>
	<script>
		function loadDouble()
		{
			
			/*var _url = '<?php echo URL;?>addons/so_to_so_thua/?ac=double&line_id=<?php echo $line_id;?>&rel_id=<?php echo $rel_id;?>&readonly=<?php echo $readonly;?>';
			loadPage('pnSoToThua', _url, function(status, message)
			{
				if(status== 0)
				{
				
				}
				
			}, false);*/
		}
		//loadDouble();
	</script>
<?php
}else if($ac == "double")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$sql = "SELECT rel_id FROM sale_local WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$project_id = $appSession->getTier()->getValue($msg);
	$sql = "SELECT d1.id, d1.name, d1.type FROM res_meta d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN sale_local d3 ON(d2.rel_id = d3.id) WHERE d3.rel_id='".$project_id."' AND d1.status =0 AND d2.status =0 AND d3.status =0 AND d1.rel_id ='".$line_id."'";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$so_thua = "";
	$so_to = "";
	$phuong = "";
	

	for($i=0; $i<count($values); $i++)
	{
		if($values[$i][2] == "SO_THUA")
		{
			$so_thua = $values[$i][1];
		}else if($values[$i][2] == "SO_TO")
		{
			$so_to = $values[$i][1];
			
		}else if($values[$i][2] == "PHUONG")
		{
			$phuong = $values[$i][1];
			
		}
	}
	$s = "";
	if($so_thua != "" && $so_to != "" && $phuong != "")
	{
		$sql = "SELECT d1.rel_id, d1.name, d1.type FROM res_meta d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN sale_local d3 ON(d2.rel_id = d3.id) WHERE d3.rel_id='".$project_id."' AND d1.status =0 AND d3.status =0 AND d2.status =0 AND d1.rel_id !='".$line_id."'";
		$sql = $sql." AND ((d1.name='".$so_thua."' AND d1.type='SO_THUA') OR (d1.name='".$so_to."' AND d1.type='SO_TO') OR (d1.name='".$phuong."' AND d1.type='PHUONG'))";
		
		$msg->add("query", $sql);
		$values = $appSession->getTier()->getArray($msg);
		$types = $appSession->getTool()->selectDistinct($values, [0]);
		for($i =0; $i<count($types); $i++)
		{
			$so_thua1 = "";
			$so_to1 = "";
			$phuong1 = "";
			for($j = 0; $j<count($values); $j++)
			{
				if($types[$i][0] == $values[$j][0])
				{
					if($values[$j][2] == "SO_THUA")
					{
						$so_thua1 = $values[$j][1];
					}else if($values[$j][2] == "SO_TO")
					{
						$so_to1 = $values[$j][1];
						
					}else if($values[$j][2] == "PHUONG")
					{
						$phuong1 = $values[$j][1];
						
					}
				}
			}
			if($so_thua1 == $so_thua && $so_to == $so_to1 && $phuong1== $phuong)
			{
				$sql = "SELECT DISTINCT d4.id, d4.code, d4.name, d4.address, d3.receipt_no FROM res_meta d1 LEFT OUTER JOIN res_attribute_line d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN sale_local d3 ON(d2.rel_id = d3.id) LEFT OUTER JOIN customer d4 ON(d3.customer_id = d4.id) WHERE d3.rel_id='".$project_id."' AND d1.status =0 AND d3.status =0 AND d2.status =0 AND d1.rel_id ='".$types[$i][0]."'";
				$msg->add("query", $sql);
				$dt = $appSession->getTier()->getTable($msg);
				if($dt->getRowCount()>0)
				{
					if($s != "")
					{
						$s = $s ."<br>";
					}
					$s = $s."#: ".$dt->getString(0, "receipt_no")."; ".$dt->getString(0, "name")."; ".$dt->getString(0, "address");
				}
				
			}
		}
	}
	echo "<span style='color:red'>".$s."</span>";
}else if($ac == "value")
{
	$sql = "SELECT d1.rel_id, d1.name, d1.type FROM res_meta d1 WHERE d1.status =0 AND d1.rel_id ='".$line_id."'";
	$msg->add("query", $sql);
	
	$values = $appSession->getTier()->getArray($msg);
	$so_thua = "";
	$so_to = "";
	$phuong = "";
	$quan = "";
	for($i=0; $i<count($values); $i++)
	{
		if($values[$i][2] == "SO_THUA")
		{
			$so_thua = $values[$i][1];
		}else if($values[$i][2] == "SO_TO")
		{
			$so_to = $values[$i][1];
			
		}else if($values[$i][2] == "PHUONG")
		{
			$phuong = $values[$i][1];
			
		}else if($values[$i][2] == "QUAN")
		{
			$quan = $values[$i][1];
			
		}
	}
	echo "Số thửa: ".$so_thua."; Số tờ: ".$so_to."; Phường: ".$phuong."; quận/huyện: ".$quan;
}

?>

