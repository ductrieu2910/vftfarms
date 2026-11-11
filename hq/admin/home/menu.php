<?php
require_once('../../config.php' );

if($appSession->getUserInfo()->getId() == "")
{
	echo "Access deny";
	exit;
}


$sql = "SELECT module_name, ac_name, ac_value FROM res_user_group_per WHERE status =0 AND group_id='".$appSession->getUserInfo()->getGroupId()."'";
$msg = $appSession->getTier()->createMessage();
$msg->add("query", $sql);
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());


$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);
$perList = array();
for($i = 0; $i < $numrows; $i++) 
{
	$row = $result[$i];
	$arr = array();
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$perList[$i] = $arr;
}

function per($appSession, $perList, $module_name, $ac_name)
{
	if($appSession->getUserInfo()->getGroupId() == "ADMIN")
	{
		return true;
	}
	
	for($i=0; $i<count($perList); $i++)
	{
		
		if($perList[$i][0] == $module_name && $perList[$i][1] == $ac_name && $perList[$i][2] == "1")
		{
			return true;
		}
	}
	return false;
}		
	
function buildMenu($url, $appSession, $arr, $parent_id, $perList)
{
	$s = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($arr[$i][1] == $parent_id)
		{
			//if(per($appSession, $perList, $arr[$i][0], "view") == true)
			//{
				$hasSub = false;
				if($arr[$i][0] != 'report')
				{
					for($j =0; $j<count($arr); $j++)
					{
						if($arr[$j][1] == $arr[$i][0])
						{
							$hasSub = true;
							break;
						}
					}
					
				}
				
				if($hasSub == true)
				{
					$s = $s.'<button class="dropdown-btn" style="background-image: url(\''.$url.$appSession->getConfig()->getProperty("addons").'/assets/images/mright.png\'); background-repeat: no-repeat; background-position: right; margin-right: 30px;">'.$appSession->getLang()->find($arr[$i][2]).' <a href="javascript:module(\''.$arr[$i][0].'\')" class="dropbtn" style="float:right;">...   </a></button>';
				   $s = $s.'<div class="dropdown-container">';
				   $s = $s.buildMenu($url, $appSession, $arr, $arr[$i][0], $perList);
				   $s = $s.'</div>';
				}else{
					$s = $s.'<span><a href="javascript:gt(\'\', \''.$arr[$i][0].'\', \'\', \'\', \'\');">'.$appSession->getLang()->find($arr[$i][2]).'</a><a href="javascript:module(\''.$arr[$i][0].'\')" class="dropbtn" style="float:right; padding-right:10px">...</a></span>';
				}
					
			//}
			
		}
	}
	
	return $s;
}

$appSession->getLang()->load($appSession->getTier(), 'ROOT', $appSession->getConfig()->getProperty("lang_id"));
$company_ids = "rel_id='".$appSession->getUserInfo()->getCompanyId()."'";
$pid = $appSession->getUserInfo()->getCompanyId();
while(true)
{
	$sql = "SELECT parent_id FROM res_company WHERE status =0 AND id='".$pid."'";
	$msg->add("query", $sql);

	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	if($numrows>0)
	{
		$pid = $result[0][0];
		if($pid == "")
		{
			break;
		}
		$company_ids = $company_ids." OR rel_id='".$pid."'";
	}else
	{
		break;
	}
}
$sql = "SELECT module_id FROM ir_module_rel WHERE status =0 AND (".$company_ids.")";
$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$rels =[];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$module_id = $row[0];
	$rels[count($rels)] = $module_id;
	
}
	
	
$sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 AND publish=1 AND (grouped=0 OR grouped IS NULL) AND type='' ORDER BY sequence ASC";
$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$menus = [];
for($j =0; $j<$numrows; $j++)
{
	$row = $result[$j];
	if($row[1] == "")
	{
		$hasItem = false;
		for($n =0; $n<count($rels); $n++)
		{
			if($row[1] == "" && $rels[$n] == $row[0])
			{
				$hasItem = true;
				break;
			}
		}
		if($hasItem == true)
		{
			$menus[count($menus)] = [$row[0], $row[1], $row[2]];
		}
	}else{
		$menus[count($menus)] = [$row[0], $row[1], $row[2]];
	}
	
	
}

if($appSession->getUserInfo()->getId() != "")
{
 echo buildMenu(URL, $appSession, $menus, "", $perList);
}
?>

	  
<script>
var dropdown = document.getElementsByClassName("dropdown-btn");
var i;

for (i = 0; i < dropdown.length; i++) {
  dropdown[i].addEventListener("click", function() {
  this.classList.toggle("active");
  var dropdownContent = this.nextElementSibling;
  if (dropdownContent.style.display === "block") {
	dropdownContent.style.display = "none";
	this.style.backgroundImage = "url('<?php echo URL;?>assets/img/mright.png')";
	
  } else {
	dropdownContent.style.display = "block";
	
	this.style.backgroundImage = "url('<?php echo URL;?>assets/img/mdown.png')";
	
	
  }
  });
}
</script>
	