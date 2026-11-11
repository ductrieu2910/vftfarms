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
	$sql = "SELECT d1.company_id";
    $sql= $sql. " FROM res_user_company d1";
    $sql = $sql. " WHERE d1.status =0 AND d1.user_id ='".$appSession->getConfig()->getProperty("user_id")."' AND d1.company_id != ''";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	$ids = "";
	for($i =0; $i<count($values); $i++)
	{
		if($ids != ""){
          $ids = $ids." OR ";
        }
        $ids = $ids." id ='".$values[$i][0]."' OR parent_id='".$values[$i][0]."'";
	}
	$sql = "SELECT id, code, name, parent_id FROM res_company WHERE status =0";
      if($ids != ""){
        $sql = $sql." AND (".$ids.")";
      }else{
        $sql = $sql." AND 1=0";
      }
	 $msg->add("query", $sql);
	 $values = $appSession->getTier()->getArray($msg);
	  
	?>
	<table class="table" width="100%">
	<?php
	for($i =0; $i<count($values); $i++)
	{
	?>
	<tr>
		<td><?php echo $values[$i][1];?>. <?php echo $values[$i][2];?></td>
		<td width="30" style="padding:10"><a href="javascript:changeCompany('<?php echo $values[$i][0];?>', '<?php echo $values[$i][3];?>')"><i class="icon-arrow-right"></i></a></td>
	</tr>
	<?php
	}
	?>
	</table>
	<script>
	function changeCompany(id, parent_id)
	{
		var _url = '<?php echo URL;?>admin/company/?ac=change&id=' + id + '&parent_id=' + parent_id;
		
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				
				closePopup();
				
			}
			
		}, true);
	}
	</script>
	<?php
}else if($ac == "change")
{
	$company_id = '';
	if(isset($_REQUEST['id']))
	{
		$company_id = $_REQUEST['id'];
	}
	$parent_id = '';
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}

	$appSession->getConfig()->setProperty("company_id", $company_id);
	$root_company_id = $company_id;
	
	$p_company_ids = $company_id;
	$pid = $company_id;
	$level = 0;
	while($level<10)
	{
		$sql = "SELECT parent_id FROM res_company WHERE status =0 AND id='".$pid."'";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		
		$numrows1 = count($result1);	
		if($numrows1>0)
		{
			$row1 = $result1[0];
			$pid = $row1[0];
		
			if($pid == "")
			{
				break;
			}
			if(strpos($p_company_ids, $pid) !== false)
			{
				break;
			}
			
			$root_company_id = $pid;
			
			$p_company_ids = $p_company_ids.",".$pid;
		
		}else
		{
			break;
		}
		
		$level = $level + 1;
		
	}
	
	
	if($root_company_id == "")
	{
		$root_company_id = $company_id;
	}
	
	
	
	
	
	$company_ids = $p_company_ids;
	$c_company_ids = $company_id;
	if($company_id != "")
	{
		$sql = "SELECT id FROM res_company WHERE status =0 AND parent_id='".$company_id."'";
		$msg->add("query", $sql);
		$result1 = $appSession->getTier()->getArray($msg);
		
		for($j =0; $j<count($result1); $j++)
		{
			if($c_company_ids != "")
			{
				$c_company_ids = $c_company_ids.",";
			}
			$c_company_ids = $c_company_ids.$result1[$j][0];
		}
		if($c_company_ids != "")
		{
			if($company_ids != "")
			{
				$company_ids = $company_ids.",";
			}
			$company_ids = $company_ids.$c_company_ids;
		}
	}
	
	
	$appSession->getConfig()->setProperty("company_ids", $company_ids);
	$appSession->getConfig()->setProperty("parent_company", $p_company_ids);
	$appSession->getConfig()->setProperty("child_company", $c_company_ids);
	
	$appSession->getConfig()->save();

	echo "OK";
}
?>
