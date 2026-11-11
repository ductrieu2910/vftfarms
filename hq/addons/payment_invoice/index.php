<?php
$msg = $appSession->getTier()->createMessage();
$arr_params = explode('&', $params);
$sale_id = "";
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$sale_id = $arrItem[1];
		}
	}
}
$appSession->getLang()->load($appSession->getTier(), "PAYMENT", $appSession->getConfig()->getProperty("lang_id"));

?>
<a href="javascript:addPayment();"><i class="icon-plus"> <?php echo $appSession->getLang()->find("New");?></i></a>
<br>
<div id="pnPayment" style="padding-top:5px"></div>
<script>
function loadPayment()
{
	var _url = '<?php echo URL;?>addons/payment_invoice/line/?line_id=<?php echo $sale_id;?>';
	loadPage('pnPayment', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
loadPayment();
function addPayment()
{
	var _url = '<?php echo URL;?>addons/payment_invoice/payment/?rel_id=<?php echo $sale_id;?>';
	openPopup(_url);
}

function doAddPayment()
{
	var ctr = document.getElementById('editpayment_payment_id');
	if(ctr.value == "")
	{
		alert("Please select payment");
		ctr.focus();
		return;
	}
	var payment_id = ctr.value;
	ctr = document.getElementById('editpayment_currency_id');
	if(ctr.value == "")
	{
		alert("Please select currency");
		ctr.focus();
		return;
	}
	var currency_id = ctr.value;
	ctr = document.getElementById('editpayment_amount');
	var amount =0;
	if(numeric(ctr.value) == true)
	{
		amount = ctr.value;
	}
	if(amount == 0)
	{
		alert('Please enter amount');
		ctr.focus();
		return;
	}
	ctr = document.getElementById('editpayment_description');
	var description = ctr.value;
	closePopup();
	var _url = '<?php echo URL;?>addons/payment_invoice/action/?ac=addPayment&rel_id=<?php echo $sale_id;?>';
	_url = _url + "&payment_id=" + payment_id;
	_url = _url + "&currency_id=" + currency_id;
	_url = _url + "&amount=" + encodeURIComponent(amount);
	_url = _url + "&description=" + encodeURIComponent(description);
	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			
			if(message.length == 36)
			{
				
				loadPayment();
				
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}

function delPayment(id)
{
	var result = confirm("Want to delete?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>addons/payment_invoice/action/?ac=delPayment&id=' + id;

	loadPage('pnSale', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadPayment();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}

</script>