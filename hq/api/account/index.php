<?php
require_once(ABSPATH.'app/barcode/autoload.php' );
require_once(ABSPATH.'app/phpqrcode/qrlib.php' );
require_once(ABSPATH.'api/Customer.php' );
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
$msg = $appSession->getTier()->createMessage();
if($ac == "user")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}

	$sql = "SELECT d1.code, d1.user_name, d1.name, p.document_id, doc.path, doc.ext FROM res_user d1 LEFT OUTER JOIN poster p ON(d1.id = p.rel_id AND p.status =0 AND p.publish =1) LEFT OUTER JOIN document doc ON(p.document_id = doc.id) WHERE d1.id='".$user_id."'";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
}else if($ac == "update_user_name")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}

	$sql = "UPDATE res_user SET name='".$appSession->getTool()->replace($name, "'", "''")."' WHERE id='".$user_id."'";
	
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}else if($ac == "change_password")
{
	$password = "";
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	$new_password = "";
	if(isset($_REQUEST['new_password']))
	{
		$new_password = $_REQUEST['new_password'];
	}
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$password);
	$len = $appSession->getTool()->lenght($password);
	for($i = 0; $i<$len; $i++)
	{
		$s = $s.chr($i + 48);
	}
	$password = $appSession->getTool()->toHash("md5", $s);
	
	$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$new_password);
	$len = $appSession->getTool()->lenght($new_password);
	for($i = 0; $i<$len; $i++)
	{
		$s = $s.chr($i + 48);
	}
	$new_password = $appSession->getTool()->toHash("md5", $s);
			
	$sql = "SELECT id FROM res_user WHERE password='".$password."' AND id='".$user_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user SET write_date=".$appSession->getTier()->getDateString();
		$sql = $sql.", password='".$new_password."'";
		$sql = $sql." WHERE id='".$user_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		echo $user_id;
		
	}else{
		echo "INVALID_PASSWORD";
	}
}else if($ac == "change_new_password")
{
	
	$new_password = "";
	if(isset($_REQUEST['new_password']))
	{
		$new_password = $_REQUEST['new_password'];
	}
	$user_id = "";
	if(isset($_REQUEST['id']))
	{
		$user_id = $_REQUEST['id'];
	}
	
	$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$new_password);
	$len = $appSession->getTool()->lenght($new_password);
	for($i = 0; $i<$len; $i++)
	{
		$s = $s.chr($i + 48);
	}
	$new_password = $appSession->getTool()->toHash("md5", $s);
			
	$sql = "SELECT id FROM res_user WHERE id='".$user_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user SET write_date=".$appSession->getTier()->getDateString();
		$sql = $sql.", password='".$new_password."'";
		$sql = $sql." WHERE id='".$user_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		echo $user_id;
		
	}else{
		echo "INVALID_PASSWORD";
	}
}
else if($ac == "barcode"){
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$type = "";
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}

	$msg = $appSession->getTier()->createMessage();
	$sql = "SELECT code FROM res_user WHERE id='".$user_id."'";
	
	$msg->add("query", $sql);
	
	$bc = $appSession->getTier()->getValue($msg);
	if($bc == "")
	{
		$bc = $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "res_user");
		$code = $appSession->getTool()->paddingLeft($bc, "0", 6);
		$sql = "UPDATE res_user set code ='".$code."', write_date=NOW() WHERE id='".$user_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	if($bc != "")
	{
		if($type == "qrcode"){
			$file_name = ABSPATH.'disk/'.$user_id.'qr.png';
			QRcode::png($bc, $file_name, 'L', 4, 2);
		
			header('Content-Type: image/png');
			header('Content-Disposition: attachment; filename=barcode.png');
			header('Content-Length: ' . filesize($file_name));
			flush();
			$download_rate = 20.5;
			$file = fopen($file_name, "r");
			while(!feof($file))
			{
				print fread($file, round($download_rate * 1024));
				
				flush();
				
			}
			fclose($file);
			exit;
			
		}else{
			$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
			$img = $generator->getBarcode($bc, $generator::TYPE_CODE_128, 3, 80);
			$file_name = ABSPATH.'disk/'.$user_id.'.png';
			
			file_put_contents($file_name, $img);
			header('Content-Type: image/png');
			header('Content-Disposition: attachment; filename=barcode.png');
			header('Content-Length: ' . filesize($file_name));
			flush();
			$download_rate = 20.5;
			$file = fopen($file_name, "r");
			while(!feof($file))
			{
				print fread($file, round($download_rate * 1024));
				
				flush();
				
			}
			fclose($file);
			exit;
			
		}
		
	}
}else if($ac == "forget_password")
{
	$email = "";
	if(isset($_REQUEST['email']))
	{
		$email = $_REQUEST['email'];
	}
	
	$phone = "";
	if(isset($_REQUEST['phone']))
	{
		$phone = $_REQUEST['phone'];
	}
	if($phone == "" && $email == "")
	{
		echo "INVALID";
		exit();
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	
	$sql = "SELECT id, email, phone FROM res_user WHERE status =0";
	if($email != "")
	{
		$sql = $sql." AND email='".str_replace("'", "''", $email)."'";
	}
	if($phone != "")
	{
		$sql = $sql." AND phone='".str_replace("'", "''", $phone)."'";
	}
	
	$msg->add("query", $sql);

	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$user_id = $values[0][0];
		$code = rand(1000, 9999);
		$sql = "INSERT INTO res_user_verification(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", create_uid";
		$sql = $sql.", code";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$user_id."'";
		$sql = $sql.", '".str_replace("'", "''", $code)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		if($phone != "")
		{
			$r = $appSession->getTool()->send_sms($phone, "Quy khach dang thuc hien thiet lap mat khau VfSCFood. Khong gui mat ma nay cho bat ky ai de tranh rui ro. MA OTP ".$code);
		}
		if($email != "")
		{
			$r = $appSession->getTool()->send_mail(SUPPORT_EMAIL, "Fosacha", $email, "Quên mật khẩu", "Quên mật khẩu", "Mã OPT:".$code);
		}
		
		echo "OK:".$user_id;
	
	}else{
		echo "INVALID";
	}
}else if($ac == "verify_user_code")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$code = "";
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$sql = "SELECT id FROM res_user_verification WHERE (create_uid='".$user_id."' AND code='".str_replace("'", "''", $code)."') AND status =0";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user_verification SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$values[0][0]."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		echo "OK";
	}else{
		echo "INVALID";
	}
}else if($ac == "change_forgot_password")
{
	$password = "";
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	$new_password = "";
	if(isset($_REQUEST['new_password']))
	{
		$new_password = $_REQUEST['new_password'];
	}
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	
	
	$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$new_password);
	$len = $appSession->getTool()->lenght($new_password);
	for($i = 0; $i<$len; $i++)
	{
		$s = $s.chr($i + 48);
	}
	$new_password = $appSession->getTool()->toHash("md5", $s);
			
	$sql = "SELECT id FROM res_user WHERE id='".$user_id."'";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user SET write_date=".$appSession->getTier()->getDateString();
		$sql = $sql.", password='".$new_password."'";
		$sql = $sql." WHERE id='".$user_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
		echo $user_id;
		
	}else{
		echo "INVALID_PASSWORD";
	}
}else if($ac == "check_user")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$sql = "SELECT d1.user_id FROM res_user_company d1 LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id) WHERE d1.status =0 AND d1.user_id='".$user_id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0){
		echo $dt->getString(0, "user_id");
	}else{
		echo "ERROR";
	}
}else if($ac == "partner_bank")
{
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$sql = "SELECT id, code, name FROM account_bank WHERE status =0";
	$sql = $sql." ORDER BY name ASC";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
	
}else if($ac == "partner_bank_line")
{
	$customer_id = "";
	if(isset($_REQUEST['customer_id']))
	{
		$customer_id = $_REQUEST['customer_id'];
	}
	$sql = "SELECT d1.bank_id, d1.code, d1.name FROM account_bank_line d1 LEFT OUTER JOIN res_partner d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN customer d3 ON(d2.id = d3.partner_id) WHERE d3.id='".$customer_id."' AND d1.status =0 AND d2.status =0";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	echo $appSession->getTool()->respTable($dt);
	
}else if($ac == "partner_bank_line_update")
{
	$user_id = "";
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$bank_id = "";
	if(isset($_REQUEST['bank_id']))
	{
		$bank_id = $_REQUEST['bank_id'];
	}
	$code = "";
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$company_id = "";
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	$customer_id = "";
	if(isset($_REQUEST['customer_id']))
	{
		$customer_id = $_REQUEST['customer_id'];
	}
	
	
	
	$sql = "SELECT id FROM account_bank_line WHERE rel_id='".$customer_id."' AND status =0";
	$msg->add("query", $sql);
	$line_id = $appSession->getTier()->getValue($msg);
	if($line_id == "")
	{
		$line_id = $appSession->getTool()->getId();
		$sql = "INSERT INTO account_bank_line(";
		$sql = $sql."id";
		$sql = $sql.", create_uid";
		$sql = $sql.", write_uid";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", company_id";
		$sql = $sql.", status";
		$sql = $sql.", rel_id";
		$sql = $sql.", code";
		$sql = $sql.", name";
		$sql = $sql.", bank_id";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$line_id."'";
		$sql = $sql.", '".$user_id."'";
		$sql = $sql.", '".$user_id."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$company_id."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$customer_id."'";
		$sql = $sql.", '".$appSession->getTool()->replace($code, "'", "''")."'";
		$sql = $sql.", '".$appSession->getTool()->replace($name, "'", "''")."'";
		$sql = $sql.", '".$appSession->getTool()->replace($bank_id, "'", "''")."'";
		$sql = $sql.")";
		
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
	}else{
		$sql = "UPDATE account_bank_line SET bank_id='".$bank_id."'";
		$sql = $sql.", write_uid='".$user_id."'";
		$sql = $sql.", write_date=".$appSession->getTier()->getDateString();
		$sql = $sql.", code='".$appSession->getTool()->replace($code, "'", "''")."'";
		$sql = $sql.", name='".$appSession->getTool()->replace($name, "'", "''")."'";
		
		$sql = $sql." WHERE id='".$line_id."'";
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	
	echo "OK";
	
}else if($ac == "res_user_add")
{
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$phone = "";
	if(isset($_REQUEST['phone']))
	{
		$phone = $_REQUEST['phone'];
	}
	$email = "";
	if(isset($_REQUEST['email']))
	{
		$email = $_REQUEST['email'];
	}
	$address = "";
	if(isset($_REQUEST['address']))
	{
		$address = $_REQUEST['address'];
	}
	$address_id = "";
	if(isset($_REQUEST['address_id']))
	{
		$address_id = $_REQUEST['address_id'];
	}
	$user_name = "";
	if(isset($_REQUEST['user']))
	{
		$user_name = $_REQUEST['user'];
	}
	if(isset($_REQUEST['user_name']))
	{
		$user_name = $_REQUEST['user_name'];
	}
	$password = "";
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	if($lang_id == "")
	{
		$lang_id = "vi";
	}
	$sale_code = "";
	if(isset($_REQUEST['sale_code']))
	{
		$sale_code = $_REQUEST['sale_code'];
	}
	$source = "";
	if(isset($_REQUEST['source']))
	{
		$source = $_REQUEST['source'];
	}
	$gender = "";
	if(isset($_REQUEST['gender']))
	{
		$gender = $_REQUEST['gender'];
	}
	$birthday = "";
	if(isset($_REQUEST['birthday']))
	{
		$birthday = $_REQUEST['birthday'];
	}
	
	$parent_partner_id = "";
	
	$customer = new Customer();
	$user_id = $customer->createWithPartner($appSession, $name, $phone, $email , $user_name, $password, $lang_id, $address, $address_id, $parent_partner_id);
	$company_id = $appSession->getConfig()->getProperty("company_id");
	if(strlen($user_id) == 36)
	{
		
	}
	$code = rand(1000, 9999);
	$sql = "INSERT INTO res_user_verification(";
	$sql = $sql."id";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", status";
	$sql = $sql.", create_uid";
	$sql = $sql.", code";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$appSession->getTool()->getId()."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$user_id."'";
	$sql = $sql.", '".str_replace("'", "''", $code)."'";
	$sql = $sql.")";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	if($email != "")
	{
		$r = $appSession->getTool()->send_mail("", "VIDU", $email, "Register", "Active account", "Code: ".$code);
	}
	echo $user_id;
}else if($ac == "verify_reset_password")
{
	$user_id = "";
	if(isset($_REQUEST['id']))
	{
		$user_id = $_REQUEST['id'];
	}
	$code = "";
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$sql = "SELECT id FROM res_user_verification WHERE (create_uid='".$user_id."' AND code='".str_replace("'", "''", $code)."') AND status =0";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user_verification SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$values[0][0]."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		echo "OK";
	}else{
		echo "INVALID";
	}
}else if($ac == "reset_password")
{
	$email = "";
	if(isset($_REQUEST['email']))
	{
		$email = $_REQUEST['email'];
	}
	
	$phone = "";
	if(isset($_REQUEST['phone']))
	{
		$phone = $_REQUEST['phone'];
	}
	if($phone == "" && $email == "")
	{
		echo "INVALID";
		exit();
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	
	$sql = "SELECT id, email, phone FROM res_user WHERE status =0";
	if($email != "")
	{
		$sql = $sql." AND email='".str_replace("'", "''", $email)."'";
	}
	if($phone != "")
	{
		$sql = $sql." AND phone='".str_replace("'", "''", $phone)."'";
	}
	
	$msg->add("query", $sql);

	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$user_id = $values[0][0];
		$code = rand(1000, 9999);
		$sql = "INSERT INTO res_user_verification(";
		$sql = $sql."id";
		$sql = $sql.", create_date";
		$sql = $sql.", write_date";
		$sql = $sql.", status";
		$sql = $sql.", create_uid";
		$sql = $sql.", code";
		$sql = $sql." )VALUES(";
		$sql = $sql."'".$appSession->getTool()->getId()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$user_id."'";
		$sql = $sql.", '".str_replace("'", "''", $code)."'";
		$sql = $sql.")";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		if($email != "")
		{
			$r = $appSession->getTool()->send_mail("", "VIDU", $email, "Forgot password", "Forget password", "Code:".$code);
		}
		echo "OK:".$user_id;
	
	}else{
		echo "INVALID";
	}
}else if($ac == "res_user_active")
{
	$user_id = "";
	if(isset($_REQUEST['id']))
	{
		$user_id = $_REQUEST['id'];
	}
	$code = "";
	if(isset($_REQUEST['code']))
	{
		$code = $_REQUEST['code'];
	}
	$sql = "SELECT id, create_uid FROM res_user_verification WHERE code='".str_replace("'", "''", $code)."' AND status =0";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values) > 0)
	{
		$sql = "UPDATE res_user_verification SET status =1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$values[0][0]."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		$user_id = $values[0][1];
		$msg = $appSession->getTier()->createMessage();
		$sql = "UPDATE res_user SET status =0, write_date=NOW() WHERE id='".$user_id."'";
		$msg->add("query", $sql);
		$result = $appSession->getTier()->exec($msg);
		echo "OK";
	}else{
		echo "INVALID";
	}
}
?>
