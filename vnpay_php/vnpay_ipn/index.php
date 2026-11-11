<?php
require_once(ABSPATH."vnpay_php/config.php");  
require_once(ABSPATH.'api/Wallet.php' );
require_once(ABSPATH.'api/Sale.php' );

	
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
$returnData = array();
try {
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

	$vnp_Amount = $inputData['vnp_Amount']/100;

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
		$cache_file = ABSPATH."log/vnpay/".$vnp_TxnRef.".txt";
		$encodedString = json_encode($inputData);
		file_put_contents($cache_file, $encodedString);
		
		$arr = $appSession->getTool()->split($vnp_TxnRef, '_');
			
		$index = strpos($vnp_TxnRef, "APP");
		$holder_id = "";
		if($index== 0 && count($arr)>2)
		{
			
			if($arr[1] == "WALLET")
			{	
				$customer_id = $arr[3];
				$wallet = new Wallet($appSession);
				
				if(count($arr)>3)
				{
					$holder_id = $arr[3];
				}
				$wallet->add($appSession->getTool()->getId(), $customer_id, $holder_id, $vnp_Amount, $vnp_TransactionNo , $vnp_OrderInfo." - ".$vnp_BankCode);
				$returnData['RspCode'] = '00';
				$returnData['Message'] = 'Confirm Success';
					
				
			}else if($arr[1] == "ORDER")
			{
				
				
				$msg = $appSession->getTier()->createMessage();
				$sale_id = $arr[2];
				
				$sql = "SELECT id, amount, status, holder_id,customer_id FROM wallet WHERE id='".$sale_id."'";
				$msg->add("query", $sql);
				$values = $appSession->getTier()->getArray($msg);
				if(count($values)>0 && $values[0][0] != "")
				{
					$saleAmount = $values[0][1];
					if($saleAmount == $vnp_Amount)
					{
						if($values[0][2] == "-1")
						{
							if($holder_id == "")
							{
								$holder_id = $values[0][3];
								$customer_id = $values[0][4];
								if($holder_id == "")
								{
									$sql = "SELECT id FROM wallet_holder WHERE rel_id='".$customer_id."' AND status=0";
									$msg->add("query", $sql);
									$arr = $appSession->getTier()->getArray($msg);
									if(count($arr)>0)
									{
										$holder_id = $arr[0][0];
									}
								}
							}
							
							if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00')
							{
								
								$sql = "SELECT d1.amount + ( d1.amount * d6.percent) AS amount FROM wallet d1  LEFT OUTER JOIN wallet_holder d2 ON(d1.holder_id = d2.id) LEFT OUTER JOIN wallet_category d3 ON(d2.category_id = d3.id) LEFT OUTER JOIN wallet_category_rel d4 ON(d3.id = d4.category_id AND d4.status =0) LEFT OUTER JOIN account_service_setting d5 ON(d4.rel_id = d5.id AND d5.status=0 AND d5.type='WALLET') LEFT OUTER JOIN account_service d6 ON(d5.service_id = d6.id AND d6.status =0) WHERE d6.id IS NOT NULL AND d1.id='".$values[0][0]."'";
								$msg->add("query", $sql);
								$arr = $appSession->getTier()->getArray($msg);
								if(count($arr)>0 && $arr[0][0] != "")
								{
									$sql = "UPDATE  wallet SET status=0, amount=". $arr[0][0] .", write_date=NOW() WHERE id='".$values[0][0]."'";
									$msg->add("query", $sql);
									$appSession->getTier()->exec($msg);
									
								}else{
									$sql = "UPDATE  wallet SET status=0, write_date=NOW() WHERE id='".$values[0][0]."'";
									$msg->add("query", $sql);
									$appSession->getTier()->exec($msg);
								}
								$sql = "UPDATE  wallet SET holder_id='".$holder_id."', write_date=NOW() WHERE id='".$values[0][0]."' AND (holder_id='' OR holder_id IS NULL)";
								$msg->add("query", $sql);
								$appSession->getTier()->exec($msg);
								
							}
							$returnData['RspCode'] = '00';
							$returnData['Message'] = 'Confirm Success';
						}else{
							$returnData['RspCode'] = '02';
							$returnData['Message'] = 'Order already confirmed';
						}
					}else{
						$returnData['RspCode'] = '04';
						$returnData['Message'] = 'invalid amount';
					}
				}else{
					
					
					$user_id = $appSession->getConfig()->getProperty("user_id");
					$user_id = "";
					$sale = new Sale($appSession);
					
					$saleAmount = $sale->totalSaleLocal($sale_id);
					if(abs($saleAmount - $vnp_Amount)<=1)
					{
						$sql = "SELECT id FROM account_payment_line_local WHERE status=0 AND rel_id='".$sale_id."' AND amount='".$vnp_Amount."' AND payment_id='e182748a-ba3c-4c24-afc2-853c9a6a0451'";
						$msg->add("query", $sql);
						
						$payment_id = $appSession->getTier()->getValue($msg);
						if($payment_id == "")
						{
							if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00')
							{
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
								$builder->add("amount", $vnp_Amount);
								$builder->add("status", 0);
								$builder->add("description", $vnp_TxnRef);
								$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
								
								$sql = $appSession->getTier()->getInsert($builder);
								$msg->add("query", $sql);
								$appSession->getTier()->exec($msg);
								
								$sql = "SELECT id FROM sale_shipping WHERE sale_id='".$sale_id."'";
								$msg->add("query", $sql);
								$check_id = $appSession->getTier()->getValue($msg);
								if($check_id != "")
								{
									$sql = "UPDATE sale_local SET status =3, write_date=NOW() WHERE id='".$sale_id."'";
									$msg->add("query", $sql);
									$appSession->getTier()->exec($msg);
								}
									
								$returnData['RspCode'] = '00';
								$returnData['Message'] = 'Confirm Success';
							}else{
								$returnData['RspCode'] = '00';
								$returnData['Message'] = 'Confirm Success';
							}
						
						}else{
							if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00')
							{
								$sql = "SELECT id FROM account_payment_line_local WHERE rel_id='".$sale_id."' AND amount=".$vnp_Amount;
								$msg->add("query", $sql);
								
								$payment_id = $appSession->getTier()->getValue($msg);
								if($payment_id != "")
								{
									$sql = "UPDATE account_payment_line_local SET status =0, line_id='".$sale_id."', write_date=NOW() WHERE id='".$payment_id."'";
									$msg->add("query", $sql);
									$appSession->getTier()->exec($msg);
									$sql = "SELECT id FROM sale_shipping WHERE sale_id='".$sale_id."'";
									$msg->add("query", $sql);
									$check_id = $appSession->getTier()->getValue($msg);
									if($check_id != "")
									{
										$sql = "UPDATE sale_local SET status =3, write_date=NOW() WHERE id='".$sale_id."'";
										$msg->add("query", $sql);
										$appSession->getTier()->exec($msg);
									}
									
								}
							}
							$returnData['RspCode'] = '02';
							$returnData['Message'] = 'Order already confirmed';
						}
						
						
					}else{
						$returnData['RspCode'] = '04';
						$returnData['Message'] = 'invalid amount';
					}
				}
				
			}else{
				$returnData['RspCode'] = '01';
				$returnData['Message'] = 'Order not found';
			}
		}else{
			$returnData['RspCode'] = '01';
			$returnData['Message'] = 'Order not found';
		}
		
		
	}else 
	{
		$returnData['RspCode'] = '97';
		$returnData['Message'] = 'Invalid Checksum';
	}
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error';
}
echo json_encode($returnData);
?>