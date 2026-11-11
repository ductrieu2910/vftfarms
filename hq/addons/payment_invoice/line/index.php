<?php
require_once(ABSPATH . 'api/Sale.php');
validUser($appSession);

$appSession->getLang()->load($appSession->getTier(), "PAYMENT", $appSession->getConfig()->getProperty("lang_id"));

$msg = $appSession->getTier()->createMessage();


$line_id = '';
if(isset($_REQUEST['line_id']))
{
	$line_id = $_REQUEST['line_id'];
}

$sql = "SELECT d1.id, d2.name, d1.currency_id, d1.receipt_no, d1.receipt_date, d1.amount, d1.description";
$sql = $sql." FROM account_payment_line d1";
$sql = $sql." LEFT OUTER JOIN account_payment d2 ON(d1.payment_id = d2.id)";
$sql = $sql." WHERE d1.status =0 AND d1.line_id='".$line_id."'";
$sql = $sql." ORDER BY d1.receipt_date ASC";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);



?>
<div class="row">
	<div class="col m12 responsive" >
<table class="table">
	<tr>
		<td align="center" nowrap style="width:20px"><?php echo $appSession->getLang()->find("No");?><td>
		<td align="center" nowrap style="width:40px"><?php echo $appSession->getLang()->find("Receipt No");?><td>
		<td align="center" nowrap style="width:100px"><?php echo $appSession->getLang()->find("Receipt Date");?><td>
		<td><?php echo $appSession->getLang()->find("Name");?><td>
		<td align="center" nowrap style="width:100px"><?php echo $appSession->getLang()->find("Amount");?><td>
		<td align="center" nowrap style="width:200px"><?php echo $appSession->getLang()->find("Description");?><td>
		<td></td>
	</tr>
	<?php
	$no = 0;
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		$no = $no + 1;
		$id = $dt->getString($i, "id");
		$receipt_no = $dt->getString($i, "receipt_no");
		$receipt_date = $dt->getString($i, "receipt_date");
		$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
		$name = $dt->getString($i, "name");
		$currency_id = $dt->getString($i, "currency_id");
		$amount = $dt->getFloat($i, "amount");
		$description = $dt->getString($i, "description");
	?>
	<tr>
		<td style="vertical-align:middle" align="center"><?php echo $no + 1;?><td>
		<td style="vertical-align:middle"><?php echo $receipt_no;?><td>
		<td style="vertical-align:middle" align="center"><?php echo $receipt_date;?><td>
		<td style="vertical-align:middle"><?php echo $name;?><td>
		<td style="vertical-align:middle" align="amount"><?php echo $appSession->getCurrency()->format($currency_id, $amount);?><td>
		<td style="vertical-align:middle"><?php echo $description;?><td>
		<td></td>
	</tr>
	<?php
	}
	?>
</table>
	</div>
</div>
	


    
