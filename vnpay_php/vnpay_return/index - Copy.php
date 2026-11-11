<?php
require_once(ABSPATH."vnpay_php/config.php");  
require_once(ABSPATH.'api/Wallet.php' );
require_once(ABSPATH.'api/Sale.php' );

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
$returnData = array();
foreach ($_GET as $key => $value) {
	if (substr($key, 0, 4) == "vnp_") {
		$inputData[$key] = $value;
	}
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
	if ($i == 1) {
		$hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
	} else {
		$hashData = $hashData . urlencode($key) . "=" . urlencode($value);
		$i = 1;
	}
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

$vnp_TxnRef = "";
if(isset($_GET['vnp_TxnRef']))
{
	$vnp_TxnRef = $_GET['vnp_TxnRef'];
	
}

$vnp_Amount = 0;
if(isset($_GET['vnp_Amount']))
{
	$vnp_Amount = $_GET['vnp_Amount'];
	
}
$vnp_OrderInfo = "";
if(isset($_GET['vnp_OrderInfo']))
{
	$vnp_OrderInfo = $_GET['vnp_OrderInfo'];
	
}

$vnp_ResponseCode = "";
if(isset($_GET['vnp_ResponseCode']))
{
	$vnp_ResponseCode = $_GET['vnp_ResponseCode'];
	
}
$vnp_TransactionNo = "";
if(isset($_GET['vnp_TransactionNo']))
{
	$vnp_TransactionNo = $_GET['vnp_TransactionNo'];
	
}
$vnp_BankCode = "";
if(isset($_GET['vnp_BankCode']))
{
	$vnp_BankCode = $_GET['vnp_BankCode'];
	
}
$vnp_PayDate = "";
if(isset($_GET['vnp_PayDate']))
{
	$vnp_PayDate = $_GET['vnp_PayDate'];
	
}
if ($secureHash == $vnp_SecureHash) 
{
	
	if ($_GET['vnp_ResponseCode'] == '00' || $_GET['vnp_ResponseCode'] == '99') 
	{
		$arr = $appSession->getTool()->split($vnp_TxnRef, '_');
		
		if(count($arr)>2)
		{
			
			if($arr[1] == "WALLET")
			{	
				$customer_id = $arr[2];
				$wallet = new Wallet($appSession);
				$holder_id = "";
				if(count($arr)>3)
				{
					$holder_id = $arr[3];
				}
				$wallet->add($appSession->getTool()->getId(), $customer_id, $holder_id, $vnp_Amount/100, $vnp_TransactionNo , $vnp_OrderInfo." - ".$vnp_BankCode);
				$returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
					
				
			}else if($arr[1] == "ORDER")
			{
				$msg = $appSession->getTier()->createMessage();
				$sale_id = $arr[2];
				$user_id = $appSession->getConfig()->getProperty("user_id");
				$user_id = "";
				$sale = new Sale($appSession);
				
				$builder = $appSession->getTier()->createBuilder("account_payment_line_local");
				$builder->add("id", $appSession->getTool()->getId());
				$builder->add("create_uid", $user_id);
				$builder->add("write_uid", $user_id);
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("line_id", $sale_id);
				$builder->add("payment_id", 'e182748a-ba3c-4c24-afc2-853c9a6a0451');
				$builder->add("currency_id", "23");
				$builder->add("rel_id", $sale_id);
				$builder->add("receipt_no", $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_payment_line_local"));
				$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("amount", $vnp_Amount/100);
				$builder->add("status", 0);
				$builder->add("description", $vnp_TransactionNo);
				$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
				
				$sql = $appSession->getTier()->getInsert($builder);
				$msg->add("query", $sql);
				
				$appSession->getTier()->exec($msg);
				$returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
				
			}else{
				$returnData['RspCode'] = '01';
				$returnData['Message'] = 'Confirm Success';
			}
		}else{
			$returnData['RspCode'] = '01';
			$returnData['Message'] = 'Order not found';
		}
	} else {
		$returnData['RspCode'] = '01';
        $returnData['Message'] = 'Order not found';
	}
}else 
{
	$returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error';
}
echo json_encode($returnData);
?>