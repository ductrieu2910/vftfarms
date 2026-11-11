<?php
class Customer
{

	public function create($appSession, $name, $phone, $email , $user_name, $password, $lang_id, $address, $address_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$message = "";
		
		$sql = "SELECT id, phone, email, user_name FROM res_user WHERE status =0 AND (user_name='".str_replace("'", "''", $user_name)."'";
		if($email != "")
		{
			$sql = $sql. " OR email = '".str_replace("'", "''", $email)."'";
		}
		if($phone != "")
		{
			$sql = $sql. " OR phone = '".str_replace("'", "''", $phone)."'";
		}
		$sql = $sql.")";
		$msg->add("query", $sql);
		
		$result = $appSession->getTier()->getTable($msg);
		$numrows = $result->getRowCount();
		
		if($numrows>0)
		{
			$row = $result->getRow(0);
			if($row->getString("user_name") == $user_name)
			{
				$message = "USER_AVAIBLE";
			}else if($row->getString("email") != "" && $row->getString("email") == $email)
			{
				$message = "EMAIL_AVAIBLE";
			}
			else if($row->getString("phone") != "" && $row->getString("phone") == $phone)
			{
				$message = "PHONE_AVAIBLE";
			}
		}else{
			$user_id = $appSession->getTool()->getId();
			$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$password);
			$len = $appSession->getTool()->lenght($password);
			for($i = 0; $i<$len; $i++)
			{
				$s = $s.chr($i + 48);
			}
			$password = $appSession->getTool()->toHash("md5", $s);
			$company_id = $appSession->getConfig()->getProperty("company_id");
			
			$sql = "INSERT INTO res_user(";
			$sql = $sql."id";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", name";
			$sql = $sql.", user_name";
			$sql = $sql.", password";
			$sql = $sql.", email";
			$sql = $sql.", phone";
			$sql = $sql.", thousands_sep";
			$sql = $sql.", decimal_point";
			$sql = $sql.", date_format";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", lang_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$user_id."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", '".str_replace("'", "''", $user_name)."'";
			$sql = $sql.", '".str_replace("'", "''", $password)."'";
			$sql = $sql.", '".str_replace("'", "''", $email)."'";
			$sql = $sql.", '".str_replace("'", "''", $phone)."'";
			$sql = $sql.", ','";
			$sql = $sql.", '.'";
			$sql = $sql.", 'YYYY-MM-DD'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$lang_id."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			
			
			$customer_id = $user_id;
			
			$code = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "customer.code");
			$code = $appSession->getTool()->paddingLeft($code, "0", 4);
			
			$barcode = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "customer.barcode");
			$barcode = $appSession->getTool()->paddingLeft($barcode, "0", 8);
			
			$sql = "INSERT INTO customer(";
			$sql = $sql."id";
			$sql = $sql.", code";
			$sql = $sql.", barcode";
			$sql = $sql.", join_date";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", name";
			$sql = $sql.", email";
			$sql = $sql.", phone";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", address";
			$sql = $sql.", address_id";
			$sql = $sql.", category_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$customer_id."'";
			$sql = $sql.", '".$code."'";
			$sql = $sql.", '".$barcode."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", '".str_replace("'", "''", $email)."'";
			$sql = $sql.", '".str_replace("'", "''", $phone)."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '". $appSession->getTool()->replace($address, "'", "''")."'";
			$sql = $sql.", '".$address_id."'";
			$sql = $sql.", 'ffe706f4-f7b5-400c-eb0f-f4b821389544'";
			$sql = $sql.")";
			
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			
			
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_user_company(";
			$sql = $sql."id";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", user_id";
			$sql = $sql.", rel_id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", group_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$user_id."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$customer_id."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", 'CUSTOMER'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			$message = $user_id;
		}
		return $message;
	}
	public function createWithPartner($appSession, $name, $phone, $email , $user_name, $password, $lang_id, $address, $address_id, $parent_partner_id)
	{
		$msg = $appSession->getTier()->createMessage();
		$message = "";
		
		$sql = "SELECT id, phone, email, user_name, status FROM res_user WHERE status =0 AND (user_name='".str_replace("'", "''", $user_name)."'";
		if($email != "")
		{
			$sql = $sql. " OR email = '".str_replace("'", "''", $email)."'";
		}
		if($phone != "")
		{
			$sql = $sql. " OR phone = '".str_replace("'", "''", $phone)."'";
		}
		$sql = $sql.")";
		$msg->add("query", $sql);
		
		$result = $appSession->getTier()->getTable($msg);
		$numrows = $result->getRowCount();
		
		if($numrows>0)
		{
			$row = $result->getRow(0);
			if($row->getString("status") == "2")
			{
				return $row->getString("id");
			}
			if($row->getString("user_name") == $user_name)
			{
				$message = "USER_AVAIBLE";
			}else if($row->getString("email") != "" && $row->getString("email") == $email)
			{
				$message = "EMAIL_AVAIBLE";
			}
			else if($row->getString("phone") != "" && $row->getString("phone") == $phone)
			{
				$message = "PHONE_AVAIBLE";
			}
		}else{
			$user_id = $appSession->getTool()->getId();
			$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$password);
			$len = $appSession->getTool()->lenght($password);
			for($i = 0; $i<$len; $i++)
			{
				$s = $s.chr($i + 48);
			}
			$password = $appSession->getTool()->toHash("md5", $s);
			$company_id = $appSession->getConfig()->getProperty("company_id");
			
			$sql = "INSERT INTO res_user(";
			$sql = $sql."id";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", name";
			$sql = $sql.", user_name";
			$sql = $sql.", password";
			$sql = $sql.", email";
			$sql = $sql.", phone";
			$sql = $sql.", thousands_sep";
			$sql = $sql.", decimal_point";
			$sql = $sql.", date_format";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", lang_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$user_id."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 2";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", '".str_replace("'", "''", $user_name)."'";
			$sql = $sql.", '".str_replace("'", "''", $password)."'";
			$sql = $sql.", '".str_replace("'", "''", $email)."'";
			$sql = $sql.", '".str_replace("'", "''", $phone)."'";
			$sql = $sql.", ','";
			$sql = $sql.", '.'";
			$sql = $sql.", 'YYYY-MM-DD'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$lang_id."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			
			
			$customer_id = $user_id;
			
			$code = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "customer.code");
			$code = "F".$appSession->getTool()->paddingLeft($code, "0", 6);
			
			$barcode = $appSession->getTool()->findReceiptNo($appSession->getTier(), $company_id, "customer.barcode");
			$barcode = $appSession->getTool()->paddingLeft($barcode, "0", 8);
			
			$sql = "INSERT INTO customer(";
			$sql = $sql."id";
			$sql = $sql.", code";
			$sql = $sql.", barcode";
			$sql = $sql.", join_date";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", name";
			$sql = $sql.", email";
			$sql = $sql.", phone";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", address";
			$sql = $sql.", address_id";
			$sql = $sql.", partner_id";
			$sql = $sql.", category_id";
			$sql = $sql.", type_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$customer_id."'";
			$sql = $sql.", '".$code."'";
			$sql = $sql.", '".$barcode."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 2";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", '".str_replace("'", "''", $email)."'";
			$sql = $sql.", '".str_replace("'", "''", $phone)."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '". $appSession->getTool()->replace($address, "'", "''")."'";
			$sql = $sql.", '".$address_id."'";
			$sql = $sql.", '".$customer_id."'";
			$sql = $sql.", 'ffe706f4-f7b5-400c-eb0f-f4b821389544'";
			$sql = $sql.", '8ab43a44-fa00-4225-a0f5-f13d20c283cd'";
			$sql = $sql.")";
			
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			
			
			$sql = "INSERT INTO res_partner(";
			$sql = $sql."id";
			$sql = $sql.", code";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", name";
			$sql = $sql.", email";
			$sql = $sql.", phone";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", address";
			$sql = $sql.", parent_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$customer_id."'";
			$sql = $sql.", '".$code."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".str_replace("'", "''", $name)."'";
			$sql = $sql.", '".str_replace("'", "''", $email)."'";
			$sql = $sql.", '".str_replace("'", "''", $phone)."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '". $appSession->getTool()->replace($address, "'", "''")."'";
			$sql = $sql.", '".$parent_partner_id."'";
			$sql = $sql.")";
			
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			
			
			
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_user_company(";
			$sql = $sql."id";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", user_id";
			$sql = $sql.", rel_id";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", create_uid";
			$sql = $sql.", write_uid";
			$sql = $sql.", group_id";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$user_id."'";
			$sql = $sql.", '".$company_id."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$customer_id."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$appSession->getTier()->getDateString()."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", '".$user_id."'";
			$sql = $sql.", 'CUSTOMER'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$result = $appSession->getTier()->exec($msg);
			$message = $user_id;
		}
		return $message;
	}
	public function login($appSession, $user, $pass)
	{
		$message = "";
		$msg = $appSession->getTier()->createMessage();
		
		$sql = "SELECT d3.id AS customer_id, d2.id, d2.password, d2.user_name, d2.name, d2.company_id, d2.date_format, d2.thousands_sep, d2.time_format, d2.decimal_point, d2.avatar, d2.lang_id, d3.code AS customer_code, d3.barcode AS customer_barcode, d4.id AS employee_id, d3.category_id AS customer_category_id, d1.employee_id AS employee_id1, d2.phone FROM res_user_company d1 LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id) LEFT OUTER JOIN customer d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN hr_employee d4 ON(d1.rel_id = d4.id) WHERE d1.status =0 AND d2.status =0 AND (d2.user_name='".$user."' OR d2.email='".$user."' OR d2.phone='".$user."')";
	
	
		$msg->add("query", $sql);
		$result = $appSession->getTier()->getTable($msg);
		$numrows = $result->getRowCount();
		
		if($numrows>0)
		{
			$row = $result->getRow(0);
			$user_id = $row->getString("id");
			$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$pass);
			$len = strlen($pass);
			for($i = 0; $i<$len; $i++)
			{
				$s = $s.chr($i + 48);
			}
			$pass = $appSession->getTool()->toHash("md5", $s);
			
			
			if($pass == $row->getString("password"))
			{
				
				$company_id = $row->getString("company_id");
				$employee_id = $row->getString("employee_id1");
				
				if($employee_id == "")
				{
					$employee_id = $row->getString("employee_id");
				}
				$message = $message."user_id=".$user_id.";";
				$message = $message."name=".$row->getString("name").";";
				$message = $message."user_name=".$row->getString("user_name").";";
				$message = $message."company_id=".$company_id.";";
				$message = $message."date_format=".$row->getString("date_format").";";
				$message = $message."thousands_sep=".$row->getString("thousands_sep").";";
				$message = $message."time_format=".$row->getString("time_format").";";
				$message = $message."decimal_point=".$row->getString("decimal_point").";";
				$message = $message."avatar=".$row->getString("avatar").";";
				$message = $message."customer_code=".$row->getString("customer_code").";";
				$message = $message."customer_barcode=".$row->getString("customer_barcode").";";
				$message = $message."phone=".$row->getString("phone").";";
				$message = $message."employee_id=".$employee_id.";";
				$message = $message."customer_id=".$row->getString("customer_id").";";
				$message = $message."customer_category_id=".$row->getString("customer_category_id").";";
				$lang_id = $row->getString("lang_id");
				if($lang_id == "")
				{
					$lang_id = "vi";
				}
				$message = $message."lang_id=".$lang_id.";";
				
				
			}else{
				$message = 'INCORRECT';
			}
			
		}else{
			$message = 'INVALID';
		}
		return $message;
	}
	
}

?>