<?php

$msg = $appSession->getTier()->createMessage();

$arr_params = explode('&', $params);
$company_id = "";
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "id")
		{
			$company_id = $arrItem[1];
		}
	}
}

$appSession->getLang()->load($appSession->getTier(), "COMPANY_SETTING", $appSession->getConfig()->getProperty("lang_id"));
$sql= "SELECT id, description FROM res_meta WHERE rel_id='".$company_id."' AND status =0 AND type='EHD'";



$config = new AppConfig();
$msg->add("query", $sql);
$arr = $appSession->getTier()->getArray($msg);
$EHD = "";
$user = "";
$password = "";
$meta_id = "";
for($i=0; $i<count($arr); $i++)
{
	$meta_id =$arr[$i][0];
	$config->parseWith($arr[$i][1], '&');
}

$user = $config->getProperty("UserName");
$Token = "";
$reset_sale_receipt_type = "Ymd";
$password = $config->getProperty("Password");
$KHHDon = $config->getProperty("KHHDon");

$Token = $config->getProperty("Token");
$Print_Bill_With_Tax = $config->getProperty("Print_Bill_With_Tax");
$Print_Bill_With_Invoice = $config->getProperty("Print_Bill_With_Invoice");
$Reset_Sale_Receipt_No = $config->getProperty("Reset_Sale_Receipt_No");
$Not_Issue_Invoice_Previous_Day = $config->getProperty("Not_Issue_Invoice_Previous_Day");
$Print_Bill_With_Discount_Combine = $config->getProperty("Print_Bill_With_Discount_Combine");

if($Reset_Sale_Receipt_No == "")
{
	$Reset_Sale_Receipt_No = "Ymd";
}

if($user != "")
{

$request_code= "EHD01";
$symbol ="";
$sql = "SELECT d2.id, d2.name, d3.name AS third_name, d3.description";
$sql = $sql.", d2.description AS request_description, d1.description AS rel_description";
$sql = $sql.", d3.id AS third_party_id";
$sql = $sql." FROM third_party_request_rel d1";
$sql = $sql." LEFT OUTER JOIN third_party_request d2 ON(d1.request_id = d2.id)";
$sql = $sql." LEFT OUTER JOIN third_party d3 ON(d2.third_party_id = d3.id)";
$sql = $sql." WHERE d1.status =0 AND d2.status =0 AND d2.code ='".$request_code."' AND d3.name='EHD'";
$sql = $sql." ORDER BY d2.sequence ASC";
$msg->add("query", $sql);
$dt_request = $appSession->getTier()->getTable($msg);
if($dt_request->getRowCount()>0)
{
	$request_id = $dt_request->getString(0, "id");
	$sql = "SELECT id, key, value FROM third_party_response WHERE rel_id='".$request_id."'";
	$msg->add("query", $sql);
	
	$dt_response = $appSession->getTier()->getTable($msg);
	$third_party_id = $dt_request->getString(0, "third_party_id");
	
	$config->parse($dt_request->getString(0, "description"), '&');
	$config->parse($dt_request->getString(0, "request_description"), '&');
	$config->parse($dt_request->getString(0, "rel_description"), '&');

	
	$sql = "SELECT d2.id, d2.name, d3.name AS third_name, d3.description";
	$sql = $sql.", d2.description AS request_description";
	$sql = $sql.", d3.id AS third_party_id";
	$sql = $sql." FROM third_party_request d2";
	$sql = $sql." LEFT OUTER JOIN third_party d3 ON(d2.third_party_id = d3.id)";
	$sql = $sql." WHERE d2.status =0 AND d3.id='".$third_party_id."' AND d2.name='Login'";
	$sql = $sql." ORDER BY d2.sequence ASC";
	
	$msg->add("query", $sql);
	$dt_login = $appSession->getTier()->getTable($msg);
	if ($dt_login->getRowCount() > 0)
	{
		$config->parse($dt_login->getString(0, "description"), '&');
		$config->parse($dt_login->getString(0, "request_description"), '&');
		$url = $config->getProperty("url");
		$page = $config->getProperty("page");
		$method = $config->getProperty("method");
		$accept = $config->getProperty("accept");
		$content_type = $config->getProperty("content_type");
		$sql = "SELECT resource, type, name FROM res_resource WHERE rel_id='".$dt_login->getString(0, "id")."' AND status =0";
		$msg->add("query", $sql);
		$resourceList = $appSession->getTier()->getArray($msg);
		$headers =  [];
		
		for ($i = 0; $i < count($resourceList); $i++)
		{
			if ($resourceList[$i][1] == "content")
			{
				$resource = $resourceList[$i][0];
				
				for ($j = 0; $j <$config->getCount(); $j++)
				{
					$key = $config->getKey($j);
					$val = $config->getValue($j);
					$resource = $appSession->getTool()->replace($resource, "{".$key."}", $val);
				}
			}
			if ($resourceList[$i][1] == "header")
			{
				$header_value = $resourceList[$i][0];
				for ($j = 0; $j <$config->getCount(); $j++)
				{
					$key = $config->getKey($j);
					$val = $config->getValue($j);
					$header_value = $appSession->getTool()->replace($header_value, "{".$key."}", $val);
				}
				$headers[count($headers)] = $resourceList[$i][2].": ".$header_value;
			}

		}
	
		if($content_type != "")
		{
			$headers[count($headers)] = "Content-Type: ".$content_type;
		}
		if($accept != "")
		{
			$headers[count($headers)] = "Accept: ".$accept;
		}

		$method = 'POST';
		$content_type = 'application/json';
		$accept = "*/*";
		$resource = '{ "UserName": "'.$user.'", "Password": "'.$password.'" }';
		$headers =["Content-Type: ".$content_type, "Accept: ".$accept];
		
		$resource = $appSession->getTool()->postService($url."api/Authenticate/SignIn", $method, $content_type , $accept, $headers, $resource);
		
		$jsonList = $appSession->getTool()->toJson($resource);	
		$json = $appSession->getTool()->jsonToList($jsonList, "");
		$NoticesList = [];
		if(isset($jsonList["Notices"]))
		{
			$NoticesList = $jsonList["Notices"];
		}
		for($i =0; $i<count($NoticesList); $i++)
		{
			$line = $NoticesList[$i];
			if(isset($line["CMTMTTien"]) && $line["CMTMTTien"] == 1)
			{
				if(isset($line["Symbol"])&& isset($line["Symbol"]))
				{
					if($symbol != "")
					{
						$symbol = $symbol.";";
					}
					$symbol = $symbol.$line["TemptCode"]."".$line["Symbol"];
				}
				
			}
		}
	}
	
}

?>

<div id="pnSetting" style="padding-top:5px">
<b>Mẫu hóa đơn</b>:<br>
<select class="select" id="editKHHDon" onchange="saveSetting()">
<option value=""></option>
<?php
$arr = $appSession->getTool()->split($symbol, ";");

for($i =0; $i<count($arr); $i++)
{
	
	$checked = "";
	if($appSession->getTool()->indexOf($KHHDon, $arr[$i]) != -1)
	{
		$checked = " selected ";
	}
?>
<option value="<?php echo $arr[$i];?>" <?php echo $checked;?>><?php echo $arr[$i];?></option>

<?php
}
?>
</select>
<br>
<b>Tiền thuế in hóa đơn</b>: <input type ="checkbox" id="Print_Bill_With_Tax" onchange="saveSetting()" <?php if($Print_Bill_With_Tax == "1"){ echo " checked ";} ?> class = "checkbox">
<br>
<b>Tiền thuế phát hành hóa đơn</b>: <input type ="checkbox" id="Print_Bill_With_Invoice" onchange="saveSetting()" <?php if($Print_Bill_With_Invoice == "1"){ echo " checked ";} ?> class = "checkbox">
<br>
<b>Gộp giảm giá</b>: <input type ="checkbox" id="Print_Bill_With_Discount_Combine" onchange="saveSetting()" <?php if($Print_Bill_With_Discount_Combine == "1"){ echo " checked ";} ?> class = "checkbox">

</div>
<?php
}
?>
<?php
$sql = "SELECT d1.id, d1.name, d1.code, dl.description AS name_lg FROM sale_category d1 LEFT OUTER JOIN res_lang_line dl ON(d1.id = dl.rel_id AND dl.status =0 AND dl.name='sale_category_name') WHERE d1.status =0";
$sql = $sql." AND (d1.company_id='".$company_id."' OR d1.company_id='".$company_id."' OR d1.company_id='ROOT')";
$sql = $sql." ORDER BY d1.sequence ASC, d1.name DESC";


$msg = $appSession->getTier()->createMessage();
$msg->add("query", $sql);
$dt_category = $appSession->getTier()->getTable($msg);

$sql = "SELECT category_id FROM sale_category_rel WHERE status =0 AND rel_id='".$company_id."'";
$msg = $appSession->getTier()->createMessage();
$msg->add("query", $sql);
$dt_category_rel = $appSession->getTier()->getTable($msg);

?>
<b>Loại chứng từ:</b>
<?php
for($i = 0; $i<$dt_category->getRowCount(); $i++)
{
	$name = $dt_category->getString($i, "name_lg");
	if($name == "")
	{
		$name = $dt_category->getString($i, "name");
	}
	$checked = "";
	for($j=0; $j<$dt_category_rel->getRowCount(); $j++)
	{
		if($dt_category->getString($i, "id") == $dt_category_rel->getString($j, "category_id"))
		{
			$checked = " checked ";
		}
	}
?>
<br><?php echo $name;?>: <input type ="checkbox" onchange="saveSaleCategoryRel(this, '<?php echo $dt_category->getString($i, "id");?>')" <?php echo $checked; ?> class = "checkbox">
<?php
}
?>
<br>
<b>Nhảy số bill về 0</b>: <select id="Reset_Sale_Receipt_No" onchange="saveSetting()">
<option value="Ymd" <?php if($Reset_Sale_Receipt_No == "Ymd"){ echo " selected "; }?>>Ngày</option>
<option value="Ym" <?php if($Reset_Sale_Receipt_No == "Ym"){ echo " selected "; }?>>Tháng</option>
<option value="YW" <?php if($Reset_Sale_Receipt_No == "YW"){ echo " selected "; }?>>Tuần</option>
<option value="YQ" <?php if($Reset_Sale_Receipt_No == "YQ"){ echo " selected "; }?>>Quý</option>
<option value="Y" <?php if($Reset_Sale_Receipt_No == "Y"){ echo " selected "; }?>>Năm</option>

</select>
<br>
<b>Không cho phép phát hành hóa đơn ngày trước</b>: <input type ="checkbox" id="Not_Issue_Invoice_Previous_Day" onchange="saveSetting()" <?php if($Not_Issue_Invoice_Previous_Day == "1"){ echo " checked ";} ?> class = "checkbox">

<script>
function saveSetting()
{
	var _url = '<?php echo URL;?>addons/company_setting/action/?ac=saveSetting&id=<?php echo $meta_id;?>';
	_url = _url + "&UserName=" + encodeURIComponent('<?php echo $user;?>');
	_url = _url + "&Password=" + encodeURIComponent('<?php echo $password;?>');
	_url = _url + "&Token=" + "<?php echo $Token;?>";
	var KHHDon = "";
	if(document.getElementById('editKHHDon') != null)
	{
		KHHDon = document.getElementById('editKHHDon').value;
	}
	_url = _url + "&KHHDon=" + encodeURIComponent(KHHDon);
	
	var Print_Bill_With_Tax = 0;
	if(document.getElementById('Print_Bill_With_Tax') != null && document.getElementById('Print_Bill_With_Tax').checked)
	{
		Print_Bill_With_Tax = 1;
	}
	var Print_Bill_With_Discount_Combine =0;
	if(document.getElementById('Print_Bill_With_Discount_Combine') != null && document.getElementById('Print_Bill_With_Discount_Combine').checked)
	{
		Print_Bill_With_Discount_Combine = 1;
	}
	
	var Print_Bill_With_Invoice = 0;
	if(document.getElementById('Print_Bill_With_Invoice') != null && document.getElementById('Print_Bill_With_Invoice').checked)
	{
		Print_Bill_With_Invoice = 1;
	}
	var Not_Issue_Invoice_Previous_Day = 0;
	if(document.getElementById('Not_Issue_Invoice_Previous_Day') != null && document.getElementById('Not_Issue_Invoice_Previous_Day').checked)
	{
		Not_Issue_Invoice_Previous_Day = 1;
	}
	
	var Reset_Sale_Receipt_No = document.getElementById('Reset_Sale_Receipt_No').value;
	
	_url = _url + "&Print_Bill_With_Tax=" + encodeURIComponent(Print_Bill_With_Tax);
	_url = _url + "&Print_Bill_With_Invoice=" + encodeURIComponent(Print_Bill_With_Invoice);
	_url = _url + "&Reset_Sale_Receipt_No=" + encodeURIComponent(Reset_Sale_Receipt_No);
	_url = _url + "&Not_Issue_Invoice_Previous_Day=" + encodeURIComponent(Not_Issue_Invoice_Previous_Day);
	_url = _url + "&Print_Bill_With_Discount_Combine=" + encodeURIComponent(Print_Bill_With_Discount_Combine);
	loadPage('pnTask', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, true);
}
function saveSaleCategoryRel(theInput, category_id)
{
	var _url = '<?php echo URL;?>addons/company_setting/action/?ac=saveSaleCategoryRel&rel_id=<?php echo $company_id;?>';
	
	_url = _url + "&category_id=" + category_id;
	if(theInput.checked)
	{
		_url = _url + "&status=0";
	}else{
		_url = _url + "&status=1";
	}
	
	
	loadPage('pnTask', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, true);
}

</script>
