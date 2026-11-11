<?php
require_once(ABSPATH.'api/Sale.php' );
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}

if($ac == "view")
{
	$appSession->getLang()->load($appSession->getTier(), "PAYMENT", $appSession->getConfig()->getProperty("lang_id"));
	
	$sale = new Sale($appSession);
	$amount = $sale->totalSaleLocal($rel_id);
	$sql = "SELECT d1.id, d1.name FROM account_payment d1 WHERE d1.status =0 AND (d1.company_id ='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	$sql = "SELECT d2.id, d2.code FROM res_currency_rel d1 LEFT OUTER JOIN res_currency d2 ON(d1.currency_id = d2.id) WHERE d1.status =0 AND (d1.rel_id ='".$appSession->getConfig()->getProperty("company_id")."' OR d1.rel_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";
	$msg->add("query", $sql);
	$dt_currency = $appSession->getTier()->getTable($msg);
	
	
?>
<div class="row">
	<div class="col m5 padding">
		<?php echo $appSession->getLang()->find("Payment");?>:
		<select class="select" id="editpayment_payment_id">
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$name = $dt->getString($i, "name");
		?>
		<option value="<?php echo $id;?>"><?php echo $name;?></option>
		<?php
		}
		?>
		</select>
	</div>
	<div class="col m3 padding">
		<?php echo $appSession->getLang()->find("Currency");?>:
		<select class="select" id="editpayment_currency_id">
		<?php
		for($i =0; $i<$dt_currency->getRowCount(); $i++)
		{
			$id = $dt_currency->getString($i, "id");
			$code = $dt_currency->getString($i, "code");
		?>
		<option value="<?php echo $id;?>"><?php echo $code;?></option>
		<?php
		}
		?>
		</select>
	</div>
	
	<div class="col m4 padding">
		<?php echo $appSession->getLang()->find("Amount");?>:
		<input id="editpayment_amount" type="number" class = "input" value="<?php echo $amount;?>">
	</div>
</div>
<div class="row">
	<div class="col m12 padding">
	<textarea class="textarea" id="editpayment_description"></textarea>
	</div>
</div>
<div class="row">
	<div class="col m12 padding">
		<input type="button" class="btn btn-primary" onclick="doAddPayment()" value="<?php echo $appSession->getLang()->find("Add");?>">
	</div>
</div>
<script>

</script>

<?php
}
?>
		